<?php

namespace App\Services;

use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Indicadores de «Estado de la plataforma» (dashboard de administración).
 *
 * Todo es agregado: ninguna cifra identifica a una persona. La actividad se
 * mide por el registro diario (mood_logs) y el semáforo por la última
 * clasificación del motor en los 30 días previos a cada corte.
 */
class PlataformaService
{
    /** Filtro de periodo. «Ciclo» es el ciclo de 14 días del WHO-5. */
    public const PERIODOS = [
        '7' => ['dias' => 7, 'etiqueta' => '7 días'],
        '30' => ['dias' => 30, 'etiqueta' => '30 días'],
        '90' => ['dias' => 90, 'etiqueta' => '90 días'],
        'ciclo' => ['dias' => 14, 'etiqueta' => 'Ciclo'],
    ];

    public const NIVELES = ['VERDE', 'AMARILLO', 'NARANJA', 'ROJO', 'ROJO_AGUDO'];

    /** Silencio R4 del tablero: sin registro diario en 14 días o más. */
    public const DIAS_SILENCIO = 14;

    /** WHO-5 vencido: más de 7 días de atraso sobre el ciclo de 14. */
    public const DIAS_WHO5_VENCIDO = 21;

    /** Caída de uso: 3 semanas seguidas a la baja y al menos este % acumulado. */
    public const CAIDA_USO_PCT = 20;

    /** «Silencio alto»: este % del padrón o más en silencio. */
    public const SILENCIO_ALTO_PCT = 5;

    public const NDA_AVISO_DIAS = 30;

    public const SECTOR_ESCOLAR = 'Educación y Colegios';

    public const ESTADOS = [
        'activa' => 'Activa',
        'onboarding' => 'Onboarding',
        'por_renovar' => 'Por renovar',
        'suspendida' => 'Suspendida',
        'baja' => 'Baja',
    ];

    private Carbon $hoy;

    /** Los 12 cortes semanales del semáforo: [inicio, fin] en Y-m-d; el último termina hoy. */
    private array $cortes = [];

    /**
     * @return array{clave:string, dias:int, etiqueta:string, desde:Carbon, hasta:Carbon, anterior_desde:Carbon, anterior_hasta:Carbon}
     */
    public static function periodo(?string $clave): array
    {
        $clave = array_key_exists((string) $clave, self::PERIODOS) ? (string) $clave : '7';
        $dias = self::PERIODOS[$clave]['dias'];
        $hoy = Carbon::today();

        return [
            'clave' => $clave,
            'dias' => $dias,
            'etiqueta' => self::PERIODOS[$clave]['etiqueta'],
            'desde' => $hoy->copy()->subDays($dias - 1),
            'hasta' => $hoy,
            'anterior_desde' => $hoy->copy()->subDays(2 * $dias - 1),
            'anterior_hasta' => $hoy->copy()->subDays($dias),
        ];
    }

    public function resumen(?string $clave = '7'): array
    {
        $this->hoy = Carbon::today();
        $this->cortes = [];
        for ($k = 11; $k >= 0; $k--) {
            $fin = $this->hoy->copy()->subWeeks($k);
            $this->cortes[] = [$fin->copy()->subDays(29)->toDateString(), $fin->toDateString()];
        }

        $periodo = self::periodo($clave);

        $instituciones = Institucion::withCount('departamentos')->orderBy('nombre_corto')->get();
        $membresias = Membresia::query()->enPadron()
            ->get(['id', 'institucion_id', 'user_id', 'estado', 'activado_en', 'created_at']);
        $porInstitucion = $membresias->groupBy('institucion_id');

        $dias = $this->diasConRegistro();
        $ultimoRegistro = $this->ultimoRegistro();
        $who5 = $this->ultimaWho5();
        [$nivelActual, $evolucion] = $this->semaforo();

        $abiertos = EventoCrisis::abiertos()->get(['id', 'institucion_id', 'nivel', 'disparado_en']);
        $contactos = EventoCrisis::query()
            ->whereNotNull('contactado_en')
            ->where('disparado_en', '>=', $this->hoy->copy()->subDays(90))
            ->get(['id', 'institucion_id', 'disparado_en', 'contactado_en']);

        $filas = $instituciones->map(fn (Institucion $i) => $this->institucion(
            $i,
            $porInstitucion->get($i->id, collect()),
            $dias,
            $ultimoRegistro,
            $who5,
            $nivelActual,
            $abiertos->where('institucion_id', $i->id),
            $contactos->where('institucion_id', $i->id),
        ));

        $sectores = collect(config('atulado.sectores', []))
            ->merge($instituciones->pluck('sector')->filter())
            ->unique()->values()->all();

        return [
            'periodo' => $periodo,
            'kpis' => $this->kpis($periodo, $instituciones, $membresias, $abiertos),
            'semaforo' => $this->consolidado($nivelActual, $evolucion),
            'alertas' => $this->alertas($periodo, $filas, $abiertos, $membresias, $ultimoRegistro, $who5),
            'instituciones' => $filas->values()->all(),
            'sectores' => $sectores,
            'estados' => self::ESTADOS,
            'actualizado' => now(),
        ];
    }

    /* ──────────────────────────── Fuentes ──────────────────────────── */

    /** Personas del padrón (subconsulta, sin límite de parámetros). */
    private function padronUsuarios()
    {
        return Membresia::query()->enPadron()->select('user_id');
    }

    /** @return array<int, array<string, true>> user_id → días con registro en los últimos 30 */
    private function diasConRegistro(): array
    {
        $dias = [];
        DB::table('mood_logs')
            ->whereIn('user_id', $this->padronUsuarios())
            ->whereDate('logged_date', '>=', $this->hoy->copy()->subDays(29)->toDateString())
            ->whereDate('logged_date', '<=', $this->hoy->toDateString())
            ->select('user_id', 'logged_date')
            ->orderBy('user_id')
            ->cursor()
            ->each(function ($f) use (&$dias) {
                $dias[(int) $f->user_id][substr((string) $f->logged_date, 0, 10)] = true;
            });

        return $dias;
    }

    /** @return array<int, string> user_id → fecha (Y-m-d) de su último registro diario */
    private function ultimoRegistro(): array
    {
        return DB::table('mood_logs')
            ->whereIn('user_id', $this->padronUsuarios())
            ->groupBy('user_id')
            ->selectRaw('user_id, max(logged_date) as ultimo')
            ->pluck('ultimo', 'user_id')
            ->mapWithKeys(fn ($fecha, $u) => [(int) $u => substr((string) $fecha, 0, 10)])
            ->all();
    }

    /** @return array<int, array{fecha:string, escala:int}> user_id → su última aplicación del WHO-5 */
    private function ultimaWho5(): array
    {
        $ultimas = [];
        DB::table('aplicaciones_who5')
            ->whereIn('user_id', $this->padronUsuarios())
            ->orderBy('fecha')->orderBy('id')
            ->select('user_id', 'fecha', 'escala')
            ->cursor()
            ->each(function ($f) use (&$ultimas) {
                $ultimas[(int) $f->user_id] = ['fecha' => substr((string) $f->fecha, 0, 10), 'escala' => (int) $f->escala];
            });

        return $ultimas;
    }

    /**
     * Nivel de cada persona del padrón en cada corte semanal: su última
     * clasificación dentro de los 30 días previos al corte.
     *
     * @return array{0: array<int, string>, 1: array<int, array<string, int>>} [nivel de hoy por persona, conteos por corte]
     */
    private function semaforo(): array
    {
        $evolucion = array_fill(0, count($this->cortes), array_fill_keys(self::NIVELES, 0));
        $actual = [];
        $ultimo = count($this->cortes) - 1;

        $procesar = function (?int $usuario, array $registros) use (&$evolucion, &$actual, $ultimo) {
            if ($usuario === null) {
                return;
            }
            foreach ($this->cortes as $k => [$inicio, $fin]) {
                $nivel = null;
                foreach ($registros as [$fecha, $n]) {
                    if ($fecha > $fin) {
                        break;
                    }
                    if ($fecha >= $inicio) {
                        $nivel = $n;
                    }
                }
                if ($nivel !== null && isset($evolucion[$k][$nivel])) {
                    $evolucion[$k][$nivel]++;
                    if ($k === $ultimo) {
                        $actual[$usuario] = $nivel;
                    }
                }
            }
        };

        $usuario = null;
        $registros = [];
        DB::table('clasificaciones')
            ->whereIn('user_id', $this->padronUsuarios())
            ->whereDate('fecha', '>=', $this->cortes[0][0])
            ->whereDate('fecha', '<=', $this->cortes[$ultimo][1])
            ->orderBy('user_id')->orderBy('fecha')->orderBy('id')
            ->select('user_id', 'fecha', 'nivel')
            ->cursor()
            ->each(function ($f) use (&$usuario, &$registros, $procesar) {
                if ((int) $f->user_id !== $usuario) {
                    $procesar($usuario, $registros);
                    $usuario = (int) $f->user_id;
                    $registros = [];
                }
                $registros[] = [substr((string) $f->fecha, 0, 10), $f->nivel];
            });
        $procesar($usuario, $registros);

        return [$actual, $evolucion];
    }

    /* ──────────────────────────── Instituciones ──────────────────────────── */

    private function institucion(
        Institucion $i,
        Collection $miembros,
        array $dias,
        array $ultimoRegistro,
        array $who5,
        array $nivelActual,
        Collection $abiertos,
        Collection $contactos,
    ): array {
        $hoy = $this->hoy;
        $usuarios = $miembros->pluck('user_id')->map(fn ($u) => (int) $u)->unique()->values();
        $activos = $miembros->where('estado', 'activo');
        $padron = $miembros->count();
        $onboarding = $i->estado === 'onboarding';
        $escolar = $i->sector === self::SECTOR_ESCOLAR;
        $umbral = max(1, (int) $i->umbral_anonimato);

        // Actividad en el registro diario (últimos 30 días)
        $atras = [];
        for ($n = 0; $n < 30; $n++) {
            $atras[$hoy->copy()->subDays($n)->toDateString()] = $n;
        }
        $personasDia = 0;
        $mau = 0;
        $semanas = [0, 0, 0, 0]; // esta semana, hace 1, hace 2, hace 3
        foreach ($usuarios as $u) {
            if (empty($dias[$u])) {
                continue;
            }
            $mau++;
            $personasDia += count($dias[$u]);
            $enSemana = [];
            foreach (array_keys($dias[$u]) as $d) {
                $s = intdiv($atras[$d] ?? 99, 7);
                if ($s < 4) {
                    $enSemana[$s] = true;
                }
            }
            foreach (array_keys($enSemana) as $s) {
                $semanas[$s]++;
            }
        }
        // Arraigo = promedio de activos por día ÷ activos del mes
        $arraigo = $mau > 0 ? round($personasDia / 30 / $mau, 2) : null;

        // Adherencia: días con registro ÷ días posibles desde que activó (tope 30)
        $posibles = 0;
        $conRegistro = 0;
        foreach ($activos as $m) {
            $desde = ($m->activado_en ?? $m->created_at)?->copy()->startOfDay();
            $n = $desde ? max(1, min(30, (int) $desde->diffInDays($hoy) + 1)) : 30;
            $posibles += $n;
            $conRegistro += min($n, count($dias[(int) $m->user_id] ?? []));
        }
        $adherencia = $posibles > 0 ? (int) round($conRegistro / $posibles * 100) : null;

        // Caída de uso: tres semanas seguidas a la baja
        [$s0, $s1, $s2, $s3] = $semanas;
        $caida = null;
        if ($s3 > 0 && $s3 > $s2 && $s2 > $s1 && $s1 > $s0) {
            $pct = (int) round(($s0 - $s3) / $s3 * 100);
            $caida = -$pct >= self::CAIDA_USO_PCT ? $pct : null;
        }

        // Silencio R4 (14 días o más sin registrar, entre quienes ya registraban)
        $limite = $hoy->copy()->subDays(self::DIAS_SILENCIO)->toDateString();
        $silencio = $activos->filter(fn ($m) => ($ultimoRegistro[(int) $m->user_id] ?? '9999') <= $limite)->count();
        $silencioPct = $padron > 0 ? $silencio / $padron * 100 : 0;

        // Semáforo actual (respeta el mínimo de personas por reporte)
        $sem = array_fill_keys(self::NIVELES, 0);
        foreach ($usuarios as $u) {
            if (isset($nivelActual[$u])) {
                $sem[$nivelActual[$u]]++;
            }
        }
        $clasificados = array_sum($sem);
        $semVisible = $clasificados > 0 && $clasificados >= $umbral;

        // WHO-5 institucional: promedio de la última aplicación de cada persona
        $escalas = $usuarios->map(fn ($u) => $who5[$u]['escala'] ?? null)->filter(fn ($v) => $v !== null);
        $who5Inst = $escalas->isNotEmpty() && $escalas->count() >= $umbral ? (int) round($escalas->avg()) : null;

        // Casos y primer contacto (mediana de los últimos 90 días)
        $casos = $abiertos->count();
        $agudos = $abiertos->where('nivel', 'ROJO_AGUDO')->count();
        $nivelCaso = $abiertos->whereIn('nivel', ['ROJO', 'ROJO_AGUDO'])->isNotEmpty() ? 'rojo' : ($casos > 0 ? 'naranja' : null);
        $contacto = $this->mediana($contactos->map(fn ($c) => (int) abs($c->disparado_en->diffInMinutes($c->contactado_en)))->all());

        // Profesional designado y su contrato de confidencialidad
        $nda = $i->profesional_nda_hasta
            ? (int) $hoy->diffInDays($i->profesional_nda_hasta->copy()->startOfDay(), false)
            : null;
        $conProfesional = filled($i->profesional_nombre);

        $adopcion = $padron > 0 ? (int) round($activos->count() / $padron * 100) : null;

        $badge = match (true) {
            $agudos > 0 => ['texto' => $agudos . ($agudos === 1 ? ' agudo' : ' agudos'), 'clase' => 'sem sem-rojo'],
            $caida !== null => ['texto' => 'Uso a la baja', 'clase' => 'sem sem-amarillo'],
            $silencio > 0 && $silencioPct >= self::SILENCIO_ALTO_PCT => ['texto' => 'Silencio alto', 'clase' => 'sem sem-naranja'],
            $onboarding => ['texto' => 'Onboarding', 'clase' => 'chip'],
            default => ['texto' => 'Estable', 'clase' => 'sem sem-verde'],
        };

        $chips = [];
        $chips[] = $casos > 0
            ? ['red', 'fa-triangle-exclamation', $casos . ($casos === 1 ? ' caso abierto' : ' casos abiertos')]
            : ['green', 'fa-check', 'Sin casos abiertos'];
        if ($escolar) {
            $chips[] = ['violet', 'fa-graduation-cap', 'Modo escolar'];
        }
        if (! $conProfesional) {
            $chips[] = ['', 'fa-list-check', 'Falta designar profesional'];
        } elseif ($nda !== null && $nda < 0) {
            $chips[] = ['red', 'fa-file-signature', 'NDA vencido'];
        } else {
            $chips[] = ['green', 'fa-user-shield', 'Prof. designado activo'];
            if ($nda !== null && $nda <= self::NDA_AVISO_DIAS) {
                $chips[] = ['red', 'fa-file-signature', $nda === 0 ? 'NDA vence hoy' : "NDA vence en {$nda} " . ($nda === 1 ? 'día' : 'días')];
            }
        }
        if ($caida !== null) {
            $chips[] = ['amber', 'fa-arrow-trend-down', '−' . abs($caida) . '% en 3 semanas'];
        }
        if ($silencio > 0) {
            $chips[] = ['amber', 'fa-volume-xmark', "{$silencio} en silencio R4"];
        }
        if ($onboarding) {
            $chips[] = ['mono', null, 'Alta ' . $i->created_at?->format('d/m/y')];
        } elseif ($i->vigencia_fin) {
            $chips[] = ['mono', null, 'Renueva ' . $i->vigencia_fin->format('d/m/y')];
        }

        $rojoNaranja = $clasificados > 0 ? ($sem['NARANJA'] + $sem['ROJO'] + $sem['ROJO_AGUDO']) / $clasificados : 0;

        return [
            'id' => $i->id,
            'url' => route('admin.instituciones.show', $i),
            'nombre' => $i->nombre_corto,
            'razon_social' => $i->razon_social,
            'busqueda' => mb_strtolower(implode(' ', array_filter([
                $i->nombre_corto, $i->razon_social, $i->rfc, $i->contacto_nombre, $i->contacto_email,
            ]))),
            'sector' => $i->sector,
            'ubicacion' => implode(' · ', array_filter([
                $i->sector ?: 'Sin sector',
                $i->ciudad,
                $i->departamentos_count . ($i->departamentos_count === 1 ? ' área' : ' áreas'),
            ])),
            'color' => $i->color ?: '#2E5D4B',
            'iniciales' => $i->iniciales,
            'estado' => $i->estado,
            'estado_legible' => self::ESTADOS[$i->estado] ?? $i->estado_legible,
            'estado_chip' => match ($i->estado) {
                'activa' => 'green',
                'por_renovar' => 'amber',
                'suspendida' => 'red',
                default => '',
            },
            'onboarding' => $onboarding,
            'escolar' => $escolar,
            'etiqueta_personas' => $escolar ? 'Estudiantes' : ($onboarding ? 'En padrón' : 'Colaboradores'),
            'padron' => $padron,
            'activas' => $activos->count(),
            'adopcion' => $adopcion,
            'meta' => (int) ($i->meta_adopcion ?: 75),
            'arraigo' => $arraigo,
            'adherencia' => $adherencia,
            'activos_semana' => $s0,
            'semaforo' => $sem,
            'clasificados' => $clasificados,
            'semaforo_visible' => $semVisible,
            'casos' => $casos,
            'agudos' => $agudos,
            'nivel_caso' => $nivelCaso,
            'primer_contacto' => $contacto,
            'who5' => $who5Inst,
            'silencio' => $silencio,
            'caida' => $caida,
            'profesional' => $i->profesional_nombre,
            'nda_dias' => $nda,
            'badge' => $badge,
            'chips' => $chips,
            'orden' => [
                'prioridad' => $agudos * 1000000 + $casos * 10000 + ($caida !== null ? 5000 : 0)
                    + (int) round($rojoNaranja * 1000) + min(999, $silencio),
                'actividad' => $padron > 0 ? round($s0 / $padron * 100, 1) : -1,
                'adopcion' => $adopcion ?? -1,
                'alta' => $i->created_at?->timestamp ?? 0,
            ],
        ];
    }

    /* ──────────────────────────── KPI ──────────────────────────── */

    private function kpis(array $p, Collection $instituciones, Collection $membresias, Collection $abiertos): array
    {
        $hoy = $this->hoy;
        $cortes = collect(range(7, 0))->map(fn ($k) => $hoy->copy()->subWeeks($k)->endOfDay());

        // Instituciones
        $vigentes = $instituciones->where('estado', '!=', 'baja');
        $panorama = [
            'activas' => $instituciones->whereIn('estado', ['activa', 'por_renovar'])->count(),
            'onboarding' => $instituciones->where('estado', 'onboarding')->count(),
            'trimestre' => $vigentes->filter(fn ($i) => $i->created_at?->gte($hoy->copy()->subDays(90)))->count(),
            'spark' => $cortes->map(fn ($c) => $vigentes->filter(fn ($i) => $i->created_at?->lte($c))->count())->all(),
        ];

        // Cuentas activadas contra el padrón y la meta de cada contrato
        $padron = $membresias->count();
        $activas = $membresias->where('estado', 'activo')->count();
        $metas = $instituciones->pluck('meta_adopcion', 'id');
        $meta = $padron > 0
            ? (int) round($membresias->sum(fn ($m) => (int) ($metas[$m->institucion_id] ?? 75)) / $padron)
            : (int) round($instituciones->avg('meta_adopcion') ?: 75);
        $cuentas = [
            'activas' => $activas,
            'padron' => $padron,
            'adopcion' => $padron > 0 ? round($activas / $padron * 100, 1) : null,
            'meta' => $meta,
            'spark' => $cortes->map(function ($c) use ($membresias) {
                $enPadron = $membresias->filter(fn ($m) => $m->created_at?->lte($c));
                $activadas = $enPadron->filter(fn ($m) => $m->estado === 'activo' && ($m->activado_en ?? $m->created_at)?->lte($c));

                return $enPadron->count() > 0 ? round($activadas->count() / $enPadron->count() * 100, 1) : 0;
            })->all(),
        ];

        // Casos abiertos de nivel rojo
        $rojos = $abiertos->whereIn('nivel', ['ROJO', 'ROJO_AGUDO']);
        $casos = [
            'total' => $rojos->count(),
            'agudos' => $rojos->where('nivel', 'ROJO_AGUDO')->count(),
            'instituciones' => $rojos->pluck('institucion_id')->filter()->unique()->count(),
            'sin_institucion' => $rojos->whereNull('institucion_id')->count(),
        ];

        // Instrumentos aplicados en el periodo y en el anterior
        $instrumentos = [];
        foreach (['actual' => [$p['desde'], $p['hasta']], 'anterior' => [$p['anterior_desde'], $p['anterior_hasta']]] as $clave => [$desde, $hasta]) {
            $instrumentos[$clave] = [
                'WHO-5' => $this->contarFechas('aplicaciones_who5', 'fecha', $desde, $hasta),
                'MDI' => $this->contarFechas('aplicaciones_mdi', 'fecha', $desde, $hasta),
                'ASQ' => $this->contarFechas('aplicaciones_asq', 'fecha', $desde, $hasta),
                'Puchol' => DB::table('aplicaciones_puchol')->where('estado', 'completado')
                    ->where('completado_en', '>=', $desde->copy()->startOfDay())
                    ->where('completado_en', '<=', $hasta->copy()->endOfDay())
                    ->count(),
            ];
        }

        // Lecturas de la revista (una por visitante, artículo y día)
        $lecturas = [
            'periodo' => $this->contarFechas('lecturas_articulos', 'fecha', $p['desde'], $p['hasta']),
            'anterior' => $this->contarFechas('lecturas_articulos', 'fecha', $p['anterior_desde'], $p['anterior_hasta']),
            'total' => DB::table('lecturas_articulos')->count(),
            'desde' => ($primera = DB::table('lecturas_articulos')->min('fecha')) ? Carbon::parse($primera) : null,
        ];

        // Personas con sesión activa en los últimos 5 minutos (sin administración)
        $enLinea = Schema::hasTable('sessions')
            ? DB::table('sessions')
                ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                ->whereNotNull('user_id')
                ->whereNotIn('user_id', User::query()->where('is_admin', true)->select('id'))
                ->distinct()
                ->count('user_id')
            : 0;

        // Psicólogos: quienes publican (publica + ambos) y quienes atienden (clínico + ambos)
        $publican = User::query()->where('is_admin', false)->where('role', 'profesional');
        $clinicos = User::query()->where('is_admin', false)->where(fn ($q) => $q
            ->where('role', 'clinico')
            ->orWhere(fn ($w) => $w->where('role', 'profesional')->where('is_clinico_atulado', true)));
        $psicologos = [
            'publican' => (clone $publican)->count(),
            'ya_publicaron' => (clone $publican)->whereHas('articles')->count(),
            'clinicos' => (clone $clinicos)->count(),
            'con_institucion' => (clone $clinicos)->whereHas('institucionesAsignadas')->count(),
        ];

        return [
            'instituciones' => $panorama,
            'cuentas' => $cuentas,
            'casos' => $casos,
            'instrumentos' => $instrumentos,
            'lecturas' => $lecturas,
            'en_linea' => $enLinea,
            'psicologos' => $psicologos,
        ];
    }

    /* ──────────────────────────── Semáforo consolidado ──────────────────────────── */

    private function consolidado(array $nivelActual, array $evolucion): array
    {
        $conteos = array_fill_keys(self::NIVELES, 0);
        foreach ($nivelActual as $nivel) {
            $conteos[$nivel]++;
        }

        $totales = array_map('array_sum', $evolucion);
        $series = array_map(fn ($nivel) => [
            'name' => ExpedienteClinicoService::ETIQUETA_NIVEL[$nivel] ?? $nivel,
            'color' => ExpedienteClinicoService::COLOR_NIVEL[$nivel] ?? '#8EADA4',
            'data' => array_map(
                fn ($k) => $totales[$k] > 0 ? round($evolucion[$k][$nivel] / $totales[$k] * 100, 1) : 0,
                array_keys($evolucion)
            ),
        ], self::NIVELES);

        return [
            'conteos' => $conteos,
            'total' => array_sum($conteos),
            'evolucion' => [
                'labels' => array_map(fn ($c) => Carbon::parse($c[1])->format('d/m'), $this->cortes),
                'series' => $series,
                'con_datos' => array_sum($totales) > 0,
            ],
        ];
    }

    /* ──────────────────────────── Alertas ──────────────────────────── */

    private function alertas(array $p, Collection $filas, Collection $abiertos, Collection $membresias, array $ultimoRegistro, array $who5): array
    {
        $alertas = [];
        $nombres = $filas->pluck('nombre', 'id');
        $vigentes = $filas->where('estado', '!=', 'baja');

        // Rojo agudo sin cerrar
        $agudos = $abiertos->where('nivel', 'ROJO_AGUDO');
        if ($agudos->isNotEmpty()) {
            $n = $agudos->count();
            $alertas[] = [
                'tono' => 'crit',
                'icono' => 'fa-heart-pulse',
                'titulo' => $n === 1 ? '1 caso ROJO AGUDO sin cerrar' : "{$n} casos ROJO AGUDO sin cerrar",
                'texto' => $agudos->groupBy(fn ($c) => $nombres[$c->institucion_id] ?? 'Sin institución')
                    ->map(fn ($casos, $nombre) => "{$nombre} (" . $casos->count() . ')')
                    ->implode(' · ') . '. Protocolo activo: verificar el contacto humano y cerrar el caso.',
                'cuando' => $this->hace($agudos->min('disparado_en')),
                'url' => route('admin.cola.index'),
            ];
        }

        // Filtro léxico en el periodo
        $lexicas = DB::table('mood_logs')
            ->where('bandera_lexica', true)
            ->whereDate('logged_date', '>=', $p['desde']->toDateString())
            ->whereDate('logged_date', '<=', $p['hasta']->toDateString())
            ->get(['user_id', 'logged_date', 'created_at']);
        if ($lexicas->isNotEmpty()) {
            $asq = DB::table('aplicaciones_asq')
                ->whereIn('user_id', $lexicas->pluck('user_id')->unique()->values()->all())
                ->groupBy('user_id')
                ->selectRaw('user_id, max(fecha) as ultima')
                ->pluck('ultima', 'user_id');
            $respondidas = $lexicas->filter(fn ($l) => isset($asq[$l->user_id])
                && substr((string) $asq[$l->user_id], 0, 10) >= substr((string) $l->logged_date, 0, 10))->count();
            $n = $lexicas->count();
            $alertas[] = [
                'tono' => 'crit',
                'icono' => 'fa-comment-dots',
                'titulo' => 'Filtro léxico: ' . ($n === 1 ? '1 detección' : "{$n} detecciones"),
                'texto' => 'En el texto libre del registro diario (' . mb_strtolower($p['etiqueta']) . '). '
                    . ($respondidas === 1 ? '1 ya respondió' : "{$respondidas} ya respondieron") . ' el ASQ después de la detección.',
                'cuando' => $this->hace($lexicas->max('created_at') ? Carbon::parse($lexicas->max('created_at')) : null),
                'url' => null,
            ];
        }

        $activas = $membresias->where('estado', 'activo')->unique('user_id');

        // Silencio R4
        $limite = $this->hoy->copy()->subDays(self::DIAS_SILENCIO)->toDateString();
        $silencio = $activas->filter(fn ($m) => ($ultimoRegistro[(int) $m->user_id] ?? '9999') <= $limite)->count();
        if ($silencio > 0) {
            $mayor = $filas->sortByDesc('silencio')->first();
            $concentra = $mayor && $mayor['silencio'] > 0 && $filas->where('silencio', '>', 0)->count() > 1
                ? " {$mayor['nombre']} concentra el " . (int) round($mayor['silencio'] / $silencio * 100) . '%.'
                : ($mayor && $mayor['silencio'] > 0 ? " Todas en {$mayor['nombre']}." : '');
            $alertas[] = [
                'tono' => 'warn',
                'icono' => 'fa-volume-xmark',
                'titulo' => 'Regla R4 — silencio prolongado',
                'texto' => ($silencio === 1 ? '1 persona' : "{$silencio} personas") . ' sin registro en ' . self::DIAS_SILENCIO . ' días o más.' . $concentra,
                'cuando' => 'hoy',
                'url' => null,
            ];
        }

        // Caída de uso sostenida
        $caidas = $vigentes->whereNotNull('caida');
        if ($caidas->isNotEmpty()) {
            $alertas[] = [
                'tono' => 'warn',
                'icono' => 'fa-chart-line',
                'titulo' => 'Caída de uso sostenida',
                'texto' => $caidas->map(fn ($f) => "{$f['nombre']} (−" . abs($f['caida']) . '%)')->implode(' · ')
                    . ($caidas->count() === 1 ? ' lleva' : ' llevan') . ' 3 semanas seguidas a la baja. Riesgo de renovación.',
                'cuando' => 'esta semana',
                'url' => null,
            ];
        }

        // Contrato de confidencialidad por vencer o vencido
        $ndas = $vigentes->filter(fn ($f) => $f['profesional'] && $f['nda_dias'] !== null && $f['nda_dias'] <= self::NDA_AVISO_DIAS);
        if ($ndas->isNotEmpty()) {
            $alertas[] = [
                'tono' => 'warn',
                'icono' => 'fa-file-signature',
                'titulo' => 'Contrato de confidencialidad por vencer',
                'texto' => $ndas->sortBy('nda_dias')->map(fn ($f) => "{$f['profesional']} ({$f['nombre']}) " . match (true) {
                    $f['nda_dias'] < 0 => 'venció hace ' . abs($f['nda_dias']) . (abs($f['nda_dias']) === 1 ? ' día' : ' días'),
                    $f['nda_dias'] === 0 => 'vence hoy',
                    default => "vence en {$f['nda_dias']} " . ($f['nda_dias'] === 1 ? 'día' : 'días'),
                })->implode(' · ') . '. Sin renovarlo no se puede enviar el resumen clínico.',
                'cuando' => 'hoy',
                'url' => null,
            ];
        }

        // Ciclos WHO-5 vencidos
        $limiteWho5 = $this->hoy->copy()->subDays(self::DIAS_WHO5_VENCIDO)->toDateString();
        $vencidos = $activas->filter(function ($m) use ($who5, $limiteWho5) {
            $ultima = $who5[(int) $m->user_id]['fecha'] ?? null;
            $referencia = $ultima ?? ($m->activado_en ?? $m->created_at)?->toDateString();

            return $referencia !== null && $referencia < $limiteWho5;
        })->count();
        if ($vencidos > 0) {
            $alertas[] = [
                'tono' => 'info',
                'icono' => 'fa-clipboard-list',
                'titulo' => 'Ciclos WHO-5 vencidos',
                'texto' => ($vencidos === 1 ? '1 persona' : "{$vencidos} personas") . ' con cuenta activa sin responder el WHO-5 en más de '
                    . self::DIAS_WHO5_VENCIDO . ' días (7 de atraso sobre el ciclo de 14).',
                'cuando' => 'hoy',
                'url' => null,
            ];
        }

        if ($alertas === []) {
            $alertas[] = [
                'tono' => 'ok',
                'icono' => 'fa-circle-check',
                'titulo' => 'Sin alertas que exijan acción',
                'texto' => 'No hay casos agudos abiertos, detecciones léxicas en el periodo, silencios prolongados, caídas de uso, contratos por vencer ni WHO-5 vencidos.',
                'cuando' => 'ahora',
                'url' => null,
            ];
        }

        return $alertas;
    }

    /* ──────────────────────────── Utilidades ──────────────────────────── */

    private function contarFechas(string $tabla, string $columna, Carbon $desde, Carbon $hasta): int
    {
        return DB::table($tabla)
            ->whereDate($columna, '>=', $desde->toDateString())
            ->whereDate($columna, '<=', $hasta->toDateString())
            ->count();
    }

    private function mediana(array $valores): ?int
    {
        if ($valores === []) {
            return null;
        }
        sort($valores);
        $n = count($valores);
        $medio = intdiv($n, 2);

        return (int) round($n % 2 ? $valores[$medio] : ($valores[$medio - 1] + $valores[$medio]) / 2);
    }

    private function hace(?Carbon $momento): string
    {
        if ($momento === null) {
            return '';
        }
        $minutos = (int) abs($momento->diffInMinutes(now()));

        return match (true) {
            $minutos < 1 => 'ahora',
            $minutos < 60 => "hace {$minutos} min",
            $minutos < 1440 => 'hace ' . intdiv($minutos, 60) . ' h',
            $minutos < 2880 => 'ayer',
            default => 'hace ' . intdiv($minutos, 1440) . ' días',
        };
    }
}
