<?php

namespace App\Services;

use App\Models\Departamento;
use App\Models\EventoCrisis;
use App\Models\Membresia;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * «Analítica e índices» (sólo administración). Todo es agregado.
 *
 * Tres índices responden tres preguntas: ¿la gente está mejor? (IBI),
 * ¿dónde se concentra el riesgo? (IRC) y ¿estamos respondiendo bien? (IRO).
 * Cada uno se compara contra el periodo anterior de la misma duración.
 */
class AnaliticaService
{
    public const PERIODOS = [
        '30' => ['dias' => 30, 'etiqueta' => '30 d'],
        '90' => ['dias' => 90, 'etiqueta' => '90 d'],
        '365' => ['dias' => 365, 'etiqueta' => '12 m'],
    ];

    /** WHO-5 bajo 50: corte asociado a riesgo de sintomatología depresiva. */
    public const CORTE_WHO5 = 50;

    /** IBI bajo este valor activa la revisión con dirección. */
    public const IBI_REVISION = 45;

    public const SLA_CONTACTO_MIN = 30;

    private const PESOS_IBI = ['who5' => 0.40, 'semaforo' => 0.30, 'adherencia' => 0.20, 'herramientas' => 0.10];

    private const PESOS_IRO = ['contacto' => 0.40, 'cierre' => 0.30, 'salidas' => 0.20, 'asq' => 0.10];

    private const HORAS_NOCHE = [22, 23, 0, 1, 2, 3, 4, 5];

    private const MODULOS = [
        'diario' => 'Registro diario',
        'herramienta' => 'Herramientas (respiración, grounding, STOP)',
        'recurso' => 'Recursos y artículos',
        'revista' => 'Revista comunitaria',
        'cuestionarios' => 'Cuestionarios',
        'plan' => 'Plan de seguridad',
        'crisis' => 'Botón de crisis',
    ];

    private Carbon $hoy;

    /** @var array<int> */
    private array $ids = [];

    /** @var Collection<int, Membresia> una membresía por persona */
    private Collection $miembros;

    /** @var array<int, string> user_id → área */
    private array $areaDe = [];

    /** @var Collection<int, EventoCrisis> */
    private Collection $casos;

    /**
     * @param  Collection  $instituciones  las instituciones del corte (una o todas las vigentes)
     */
    public function calcular(Collection $instituciones, string $clavePeriodo): array
    {
        $clave = array_key_exists($clavePeriodo, self::PERIODOS) ? $clavePeriodo : '90';
        $dias = self::PERIODOS[$clave]['dias'];

        $this->hoy = Carbon::today();
        $this->ids = $instituciones->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->cargarPoblacion($instituciones);

        $hasta = $this->hoy->copy();
        $desde = $hasta->copy()->subDays($dias - 1);
        $antesHasta = $desde->copy()->subDay();
        $antesDesde = $antesHasta->copy()->subDays($dias - 1);

        $ibi = $this->ibi($desde, $hasta);
        $ibi['anterior'] = $this->ibi($antesDesde, $antesHasta)['valor'];
        $irc = $this->irc($desde, $hasta);
        $irc['anterior'] = $this->irc($antesDesde, $antesHasta)['valor'];
        $iro = $this->iro($desde, $hasta);
        $iro['anterior'] = $this->iro($antesDesde, $antesHasta)['valor'];

        return [
            'periodo' => ['clave' => $clave, 'dias' => $dias, 'etiqueta' => self::PERIODOS[$clave]['etiqueta'], 'desde' => $desde, 'hasta' => $hasta],
            'poblacion' => [
                'padron' => $this->miembros->count(),
                'activos' => $this->miembros->where('estado', 'activo')->count(),
            ],
            'ibi' => $ibi,
            'irc' => $irc,
            'iro' => $iro,
            'evolucion' => $this->evolucion($dias),
            'transicion' => $this->transicion(),
            'embudo' => $this->embudo($desde, $hasta),
            'modulos' => $this->modulos($desde, $hasta),
            'horas' => $this->horas($desde, $hasta),
        ];
    }

    /* ──────────────────────────── Población ──────────────────────────── */

    private function cargarPoblacion(Collection $instituciones): void
    {
        $this->miembros = Membresia::query()->whereIn('institucion_id', $this->ids)->enPadron()->soloColaboradores()
            ->get(['id', 'institucion_id', 'user_id', 'estado', 'departamento_id', 'activado_en', 'created_at'])
            // Quien está en dos instituciones cuenta una vez; manda su membresía activa.
            ->sortBy(fn ($m) => $m->estado === 'activo' ? 0 : 1)
            ->unique('user_id')
            ->values();

        $varias = count($this->ids) > 1;
        $nombresInst = $instituciones->pluck('nombre_corto', 'id');
        $areas = Departamento::query()->whereIn('institucion_id', $this->ids)->get(['id', 'institucion_id', 'nombre', 'parent_id'])->keyBy('id');
        $nombreArea = function (?int $id, int $institucion) use ($areas, $varias, $nombresInst): string {
            $area = $id ? $areas->get($id) : null;
            $nombre = $area
                ? (($area->parent_id && $areas->has($area->parent_id)) ? $areas[$area->parent_id]->nombre . ' — ' : '') . $area->nombre
                : 'Sin área asignada';

            return $varias ? ($nombresInst[$institucion] ?? '') . ' · ' . $nombre : $nombre;
        };

        $this->areaDe = [];
        foreach ($this->miembros as $m) {
            $this->areaDe[(int) $m->user_id] = $nombreArea($m->departamento_id ? (int) $m->departamento_id : null, (int) $m->institucion_id);
        }

        $this->casos = EventoCrisis::query()->whereIn('institucion_id', $this->ids)
            ->get(['id', 'user_id', 'institucion_id', 'nivel', 'estado', 'disparado_en', 'contactado_en',
                'cierre_verificado_por', 'salida_sin_contacto', 'updated_at']);
    }

    /** Subconsulta con los user_id del corte (sin límite de parámetros). */
    private function usuarios()
    {
        return Membresia::query()->whereIn('institucion_id', $this->ids)->enPadron()->soloColaboradores()->select('user_id');
    }

    /** @return array<int, string> user_id → nivel vigente al corte (clasificación de 30 días o caso abierto en ese momento) */
    private function nivelesAl(Carbon $corte, bool $conCasos = true): array
    {
        $niveles = [];
        DB::table('clasificaciones')
            ->whereIn('user_id', $this->usuarios())
            ->whereDate('fecha', '>=', $corte->copy()->subDays(29)->toDateString())
            ->whereDate('fecha', '<=', $corte->toDateString())
            ->orderBy('fecha')->orderBy('id')
            ->select('user_id', 'nivel')
            ->cursor()
            ->each(function ($f) use (&$niveles) {
                if (in_array($f->nivel, PlataformaService::NIVELES, true)) {
                    $niveles[(int) $f->user_id] = $f->nivel;
                }
            });

        if ($conCasos) {
            $fin = $corte->copy()->endOfDay();
            $abiertosAlCorte = $this->casos->filter(fn ($c) => $c->disparado_en && $c->disparado_en->lte($fin)
                && ($c->estado !== 'cerrado' || $c->updated_at?->gt($fin)));
            $niveles = PlataformaService::conCasosAbiertos($niveles, $abiertosAlCorte, array_keys($this->areaDe));
        }

        return $niveles;
    }

    /* ──────────────────────────── IBI ──────────────────────────── */

    private function ibi(Carbon $desde, Carbon $hasta): array
    {
        // WHO-5: promedio de la última aplicación de cada persona en el periodo.
        $who5 = [];
        DB::table('aplicaciones_who5')->whereIn('user_id', $this->usuarios())
            ->whereDate('fecha', '>=', $desde->toDateString())->whereDate('fecha', '<=', $hasta->toDateString())
            ->orderBy('fecha')->orderBy('id')->select('user_id', 'escala')->cursor()
            ->each(function ($f) use (&$who5) {
                $who5[(int) $f->user_id] = (int) $f->escala;
            });

        // Semáforo: %verde + la mitad del %amarillo entre quienes tienen nivel.
        $niveles = $this->nivelesAl($hasta);
        $conteo = array_count_values($niveles);
        $semaforo = $niveles !== []
            ? (($conteo['VERDE'] ?? 0) + 0.5 * ($conteo['AMARILLO'] ?? 0)) / count($niveles) * 100
            : null;

        // Adherencia: días con registro ÷ días posibles desde la activación dentro del periodo.
        $activos = $this->miembros->filter(fn ($m) => $m->estado === 'activo'
            && ($m->activado_en ?? $m->created_at)?->copy()->startOfDay()->lte($hasta));
        $diasRegistro = $this->diasConRegistro($desde, $hasta);
        $posibles = 0;
        $conRegistro = 0;
        foreach ($activos as $m) {
            $inicio = ($m->activado_en ?? $m->created_at)->copy()->startOfDay()->max($desde);
            $n = (int) $inicio->diffInDays($hasta) + 1;
            $posibles += $n;
            $conRegistro += min($n, count($diasRegistro[(int) $m->user_id] ?? []));
        }
        $adherencia = $posibles > 0 ? $conRegistro / $posibles * 100 : null;

        // Uso de herramientas: % del padrón activo que usó al menos una en el periodo.
        $usaron = DB::table('interacciones')->whereIn('user_id', $this->usuarios())->where('modulo', 'herramienta')
            ->where('ocurrido_en', '>=', $desde->copy()->startOfDay())->where('ocurrido_en', '<=', $hasta->copy()->endOfDay())
            ->distinct()->pluck('user_id')->map(fn ($u) => (int) $u)->flip();
        $herramientas = $activos->isNotEmpty()
            ? $activos->filter(fn ($m) => $usaron->has((int) $m->user_id))->count() / $activos->count() * 100
            : null;

        $componentes = [
            'who5' => $who5 !== [] ? (int) round(array_sum($who5) / count($who5)) : null,
            'semaforo' => $semaforo !== null ? (int) round($semaforo) : null,
            'adherencia' => $adherencia !== null ? (int) round($adherencia) : null,
            'herramientas' => $herramientas !== null ? (int) round($herramientas) : null,
        ];

        return [
            'valor' => self::ponderar($componentes, self::PESOS_IBI),
            'componentes' => $componentes,
            'pesos' => self::PESOS_IBI,
            'personas_who5' => count($who5),
        ];
    }

    /* ──────────────────────────── IRC ──────────────────────────── */

    private function irc(Carbon $desde, Carbon $hasta): array
    {
        $padronArea = collect($this->areaDe)->countBy()->all();
        $alertasArea = array_fill_keys(array_keys($padronArea), 0);
        foreach ($this->casosEntre($desde, $hasta) as $caso) {
            $area = $this->areaDe[(int) $caso->user_id] ?? null;
            if ($area !== null) {
                $alertasArea[$area]++;
            }
        }

        $total = array_sum($alertasArea);
        if ($total === 0 || count($padronArea) < 2) {
            return ['valor' => null, 'alertas' => $total, 'reparto' => [], 'areas' => count($padronArea)];
        }

        $tasas = [];
        foreach ($padronArea as $area => $n) {
            $tasas[] = $n > 0 ? $alertasArea[$area] / $n : 0;
        }

        arsort($alertasArea);
        $reparto = [];
        $acumulado = 0;
        foreach (array_slice($alertasArea, 0, 2, true) as $area => $n) {
            if ($n === 0) {
                break;
            }
            $pct = (int) round($n / $total * 100);
            $reparto[] = ['area' => $area, 'pct' => $pct];
            $acumulado += $pct;
        }
        if ($acumulado < 100 && count($alertasArea) > count($reparto)) {
            $reparto[] = ['area' => 'Resto', 'pct' => 100 - $acumulado];
        }

        return ['valor' => (int) round(self::gini($tasas) * 100), 'alertas' => $total, 'reparto' => $reparto, 'areas' => count($padronArea)];
    }

    /* ──────────────────────────── IRO ──────────────────────────── */

    private function iro(Carbon $desde, Carbon $hasta): array
    {
        $casos = $this->casosEntre($desde, $hasta);
        $total = $casos->count();
        $aTiempo = $casos->filter(fn ($c) => $c->contactado_en
            && abs($c->disparado_en->diffInMinutes($c->contactado_en)) <= self::SLA_CONTACTO_MIN)->count();
        $cerrados = $casos->where('estado', 'cerrado');
        $verificados = $cerrados->filter(fn ($c) => $c->cierre_verificado_por !== null)->count();
        $salidas = $casos->where('salida_sin_contacto', true)->count();

        // ASQ respondidas en 24 h después de una bandera del filtro léxico.
        $banderas = DB::table('mood_logs')->whereIn('user_id', $this->usuarios())->where('bandera_lexica', true)
            ->whereDate('logged_date', '>=', $desde->toDateString())->whereDate('logged_date', '<=', $hasta->toDateString())
            ->get(['user_id', 'logged_date']);
        $asqRespondidas = null;
        if ($banderas->isNotEmpty()) {
            $asq = DB::table('aplicaciones_asq')->whereIn('user_id', $banderas->pluck('user_id')->unique()->values()->all())
                ->whereDate('fecha', '>=', $desde->toDateString())
                ->get(['user_id', 'fecha'])->groupBy('user_id');
            $respondidas = $banderas->filter(function ($b) use ($asq) {
                $dia = Carbon::parse(substr((string) $b->logged_date, 0, 10));

                return collect($asq->get($b->user_id, []))->contains(function ($a) use ($dia) {
                    $fecha = Carbon::parse(substr((string) $a->fecha, 0, 10));

                    return $fecha->gte($dia) && $fecha->lte($dia->copy()->addDay());
                });
            })->count();
            $asqRespondidas = (int) round($respondidas / $banderas->count() * 100);
        }

        $componentes = [
            'contacto' => $total > 0 ? (int) round($aTiempo / $total * 100) : null,
            'cierre' => $cerrados->isNotEmpty() ? (int) round($verificados / $cerrados->count() * 100) : null,
            'salidas' => $total > 0 ? max(0, 100 - 25 * $salidas) : null,
            'asq' => $asqRespondidas,
        ];

        return [
            'valor' => self::ponderar($componentes, self::PESOS_IRO),
            'componentes' => $componentes,
            'casos' => $total,
            'salidas' => $salidas,
            'banderas' => $banderas->count(),
        ];
    }

    /* ──────────────────────────── Evolución ──────────────────────────── */

    /** 12 cortes: semanales en 30 y 90 días, mensuales en 12 meses. */
    private function cortes(int $dias): array
    {
        $cortes = [];
        for ($k = 11; $k >= 0; $k--) {
            $cortes[] = $dias >= 365
                ? ($k === 0 ? $this->hoy->copy() : $this->hoy->copy()->subMonthsNoOverflow($k)->endOfMonth()->startOfDay())
                : $this->hoy->copy()->subWeeks($k);
        }

        return $cortes;
    }

    private function evolucion(int $dias): array
    {
        $mensual = $dias >= 365;
        $cortes = $this->cortes($dias);
        $etiquetas = array_map(fn (Carbon $c) => $mensual ? ucfirst($c->translatedFormat('M')) : $c->format('d/m'), $cortes);

        // Semáforo en cada corte
        $porCorte = [];
        foreach ($cortes as $k => $corte) {
            $porCorte[$k] = array_count_values($this->nivelesAl($corte));
        }
        $totales = array_map('array_sum', $porCorte);
        $series = array_map(fn ($nivel) => [
            'name' => ExpedienteClinicoService::ETIQUETA_NIVEL[$nivel] ?? $nivel,
            'color' => ExpedienteClinicoService::COLOR_NIVEL[$nivel] ?? '#8EADA4',
            'data' => array_map(fn ($k) => $totales[$k] > 0 ? round(($porCorte[$k][$nivel] ?? 0) / $totales[$k] * 100, 1) : 0, array_keys($cortes)),
        ], PlataformaService::NIVELES);

        $conDatos = array_keys(array_filter($totales));
        $textoSemaforo = null;
        if (count($conDatos) >= 2) {
            [$a, $b] = [reset($conDatos), end($conDatos)];
            $pct = fn ($k, array $niveles) => array_sum(array_map(fn ($n) => $porCorte[$k][$n] ?? 0, $niveles)) / $totales[$k] * 100;
            $cambio = function (string $nombre, array $niveles) use ($a, $b, $pct): string {
                $d = (int) round($pct($b, $niveles) - $pct($a, $niveles));

                return $d === 0 ? "{$nombre} se mantuvo" : "{$nombre} " . ($d > 0 ? 'subió ' : 'bajó ') . abs($d) . (abs($d) === 1 ? ' punto' : ' puntos');
            };
            $textoSemaforo = 'Del ' . $etiquetas[$a] . ' al ' . $etiquetas[$b] . ': ' . $cambio('la banda verde', ['VERDE'])
                . ', ' . $cambio('naranja', ['NARANJA']) . ' y ' . $cambio('rojo', ['ROJO', 'ROJO_AGUDO']) . '.';
        }

        return [
            'etiquetas' => $etiquetas,
            'unidad' => $mensual ? 'meses' : 'semanas',
            'semaforo' => ['series' => $series, 'con_datos' => $conDatos !== [], 'texto' => $textoSemaforo],
            'who5' => $this->who5PorCorte($cortes, $mensual),
        ];
    }

    private function who5PorCorte(array $cortes, bool $mensual): array
    {
        $n = count($cortes);
        $inicio = $mensual ? $cortes[0]->copy()->startOfMonth() : $cortes[0]->copy()->subDays(6);
        $cubeta = function (string $fecha) use ($cortes, $inicio): ?int {
            if ($fecha < $inicio->toDateString()) {
                return null;
            }
            foreach ($cortes as $k => $corte) {
                if ($fecha <= $corte->toDateString()) {
                    return $k;
                }
            }

            return null;
        };

        $inst = array_fill(0, $n, []);
        $areas = [];
        DB::table('aplicaciones_who5')->whereIn('user_id', $this->usuarios())
            ->whereDate('fecha', '>=', $inicio->toDateString())->whereDate('fecha', '<=', $this->hoy->toDateString())
            ->select('user_id', 'fecha', 'escala')->cursor()
            ->each(function ($f) use (&$inst, &$areas, $cubeta) {
                $k = $cubeta(substr((string) $f->fecha, 0, 10));
                if ($k === null) {
                    return;
                }
                $inst[$k][] = (int) $f->escala;
                $area = $this->areaDe[(int) $f->user_id] ?? null;
                if ($area !== null) {
                    $areas[$area][$k][] = (int) $f->escala;
                }
            });

        $promedios = fn (array $cubetas) => array_map(fn ($k) => !empty($cubetas[$k]) ? (int) round(array_sum($cubetas[$k]) / count($cubetas[$k])) : null, range(0, $n - 1));
        $serieInst = $promedios($inst);
        if (count(array_filter($serieInst, fn ($v) => $v !== null)) === 0) {
            return ['con_datos' => false];
        }

        // El área con el promedio más bajo del periodo (si hay más de una con datos).
        $peor = null;
        if (count($areas) > 1) {
            $medias = array_map(fn ($c) => array_sum(array_merge(...array_values($c))) / count(array_merge(...array_values($c))), $areas);
            asort($medias);
            $peor = array_key_first($medias);
        }
        $serieArea = $peor !== null ? $promedios($areas[$peor]) : null;

        $bajo = fn (?array $serie) => $serie === null ? 0 : count(array_filter($serie, fn ($v) => $v !== null && $v < self::CORTE_WHO5));
        $unidad = $mensual ? 'meses' : 'semanas';
        $texto = $peor !== null
            ? "{$peor} estuvo bajo el corte {$bajo($serieArea)} de {$n} {$unidad}; el conjunto, {$bajo($serieInst)}."
            : "El promedio estuvo bajo el corte {$bajo($serieInst)} de {$n} {$unidad}.";

        $series = [['name' => count($this->ids) > 1 ? 'Todas las instituciones' : 'Institución completa', 'color' => '#2A78D6', 'data' => self::rellenar($serieInst)]];
        if ($serieArea !== null) {
            $series[] = ['name' => $peor, 'color' => '#D9660F', 'data' => self::rellenar($serieArea)];
        }

        return ['con_datos' => true, 'series' => $series, 'texto' => $texto];
    }

    /* ──────────────────────────── Transición mensual ──────────────────────────── */

    private function transicion(): array
    {
        $agrupar = fn (?string $nivel) => $nivel === 'ROJO_AGUDO' ? 'ROJO' : $nivel;
        $antes = array_map($agrupar, $this->nivelesAl($this->hoy->copy()->subDays(30)));
        $ahora = array_map($agrupar, $this->nivelesAl($this->hoy->copy()));

        $filas = ['VERDE', 'AMARILLO', 'NARANJA', 'ROJO'];
        $columnas = ['VERDE', 'AMARILLO', 'NARANJA', 'ROJO', 'SILENCIO'];
        $conteos = array_fill_keys($filas, array_fill_keys($columnas, 0));
        foreach ($antes as $u => $de) {
            $conteos[$de][$ahora[$u] ?? 'SILENCIO']++;
        }

        $matriz = [];
        foreach ($conteos as $de => $destinos) {
            $total = array_sum($destinos);
            $matriz[$de] = [
                'total' => $total,
                'pct' => array_map(fn ($n) => $total > 0 ? (int) round($n / $total * 100) : null, $destinos),
            ];
        }

        $texto = null;
        if ($matriz['ROJO']['total'] > 0) {
            $bajaron = $conteos['ROJO']['VERDE'] + $conteos['ROJO']['AMARILLO'] + $conteos['ROJO']['NARANJA'];
            $texto = 'La diagonal es permanencia. De quienes estaban en rojo, el ' . (int) round($bajaron / $matriz['ROJO']['total'] * 100)
                . '% bajó de nivel en un mes y el ' . ($matriz['ROJO']['pct']['SILENCIO'] ?? 0) . '% cayó en silencio: ese grupo es el que hay que buscar.';
        } elseif (array_sum(array_column($matriz, 'total')) > 0) {
            $texto = 'La diagonal es permanencia. Nadie estaba en rojo hace un mes; la columna «Silencio» son quienes dejaron de tener clasificación.';
        }

        return ['matriz' => $matriz, 'con_datos' => array_sum(array_column($matriz, 'total')) > 0, 'texto' => $texto];
    }

    /* ──────────────────────────── Embudo ──────────────────────────── */

    private function embudo(Carbon $desde, Carbon $hasta): array
    {
        $dias = $this->diasConRegistro($desde, $hasta);
        $sostenido = 0;
        foreach ($dias as $fechas) {
            if (self::rachaMaxima(array_keys($fechas)) >= 7) {
                $sostenido++;
            }
        }

        $conAplicacion = fn (string $tabla) => DB::table($tabla)->whereIn('user_id', $this->usuarios())
            ->whereDate('fecha', '>=', $desde->toDateString())->whereDate('fecha', '<=', $hasta->toDateString())
            ->distinct()->count('user_id');

        $pasos = [
            ['k' => 'Invitados al padrón', 'v' => $this->miembros->count()],
            ['k' => 'Activaron cuenta', 'v' => $this->miembros->where('estado', 'activo')->count()],
            ['k' => 'Registro diario ≥ 1 vez', 'v' => count($dias)],
            ['k' => 'Registro sostenido 7 d', 'v' => $sostenido],
            ['k' => 'Respondieron WHO-5', 'v' => $conAplicacion('aplicaciones_who5')],
            ['k' => 'Escalaron a MDI', 'v' => $conAplicacion('aplicaciones_mdi')],
            ['k' => 'Escalaron a ASQ', 'v' => $conAplicacion('aplicaciones_asq')],
            ['k' => 'Atendidos por profesional', 'v' => $this->casosEntre($desde, $hasta)->whereNotNull('contactado_en')->pluck('user_id')->unique()->count()],
        ];

        // La fuga más grande entre los pasos de adopción (los de escalamiento no son fuga).
        $texto = null;
        $mayor = 0;
        for ($i = 1; $i <= 3; $i++) {
            $fuga = $pasos[$i - 1]['v'] - $pasos[$i]['v'];
            if ($fuga > $mayor) {
                $mayor = $fuga;
                $texto = "La fuga más grande está entre «{$pasos[$i - 1]['k']}» y «{$pasos[$i]['k']}»: {$fuga} " . ($fuga === 1 ? 'persona' : 'personas') . '.'
                    . ($i === 1 ? ' Es un tema de difusión interna, no del producto.' : '');
            }
        }

        return ['pasos' => $pasos, 'con_datos' => $pasos[0]['v'] > 0, 'texto' => $texto];
    }

    /* ──────────────────────────── Uso por módulo y hora ──────────────────────────── */

    private function modulos(Carbon $desde, Carbon $hasta): array
    {
        [$ini, $fin] = [$desde->toDateString(), $hasta->toDateString()];
        $conteos = array_fill_keys(array_keys(self::MODULOS), 0);

        $conteos['diario'] = DB::table('mood_logs')->whereIn('user_id', $this->usuarios())
            ->whereDate('logged_date', '>=', $ini)->whereDate('logged_date', '<=', $fin)->count();
        foreach (['aplicaciones_who5', 'aplicaciones_mdi', 'aplicaciones_asq'] as $tabla) {
            $conteos['cuestionarios'] += DB::table($tabla)->whereIn('user_id', $this->usuarios())
                ->whereDate('fecha', '>=', $ini)->whereDate('fecha', '<=', $fin)->count();
        }
        $conteos['cuestionarios'] += DB::table('aplicaciones_puchol')->whereIn('user_id', $this->usuarios())
            ->where('estado', 'completado')
            ->where('completado_en', '>=', $desde->copy()->startOfDay())->where('completado_en', '<=', $hasta->copy()->endOfDay())
            ->count();

        DB::table('interacciones')->whereIn('user_id', $this->usuarios())
            ->where('ocurrido_en', '>=', $desde->copy()->startOfDay())->where('ocurrido_en', '<=', $hasta->copy()->endOfDay())
            ->groupBy('modulo')->selectRaw('modulo, count(*) as n')->get()
            ->each(function ($f) use (&$conteos) {
                if (isset($conteos[$f->modulo])) {
                    $conteos[$f->modulo] += (int) $f->n;
                }
            });

        arsort($conteos);
        $datos = [];
        foreach ($conteos as $clave => $n) {
            $datos[] = ['k' => self::MODULOS[$clave], 'v' => $n];
        }
        $total = array_sum($conteos);

        $texto = null;
        if ($total > 0) {
            $orden = array_keys(array_filter($conteos));
            $texto = self::MODULOS[$orden[0]] . ' es el ancla de uso (' . (int) round($conteos[$orden[0]] / $total * 100) . '% de los eventos)'
                . (isset($orden[1]) ? '; le sigue ' . mb_strtolower(self::MODULOS[$orden[1]]) . '.' : '.');
        }

        $primera = DB::table('interacciones')->min('ocurrido_en');
        $desdeCuando = $primera && Carbon::parse($primera)->gt($desde)
            ? 'Herramientas, recursos, revista, plan y botón de crisis se cuentan desde el ' . Carbon::parse($primera)->format('d/m/Y') . '.'
            : null;

        return ['datos' => $datos, 'total' => $total, 'texto' => $texto, 'aviso' => $desdeCuando];
    }

    private function horas(Carbon $desde, Carbon $hasta): array
    {
        $porHora = array_fill(0, 24, 0);
        $porArea = [];
        $sumar = function ($f, string $columna) use (&$porHora, &$porArea) {
            if (!$f->{$columna}) {
                return;
            }
            $hora = (int) Carbon::parse($f->{$columna})->format('G');
            $porHora[$hora]++;
            $area = $this->areaDe[(int) $f->user_id] ?? null;
            if ($area !== null) {
                $porArea[$area]['total'] = ($porArea[$area]['total'] ?? 0) + 1;
                if (in_array($hora, self::HORAS_NOCHE, true)) {
                    $porArea[$area]['noche'] = ($porArea[$area]['noche'] ?? 0) + 1;
                }
            }
        };

        [$ini, $fin] = [$desde->toDateString(), $hasta->toDateString()];
        DB::table('mood_logs')->whereIn('user_id', $this->usuarios())
            ->whereDate('logged_date', '>=', $ini)->whereDate('logged_date', '<=', $fin)
            ->select('user_id', 'created_at')->cursor()->each(fn ($f) => $sumar($f, 'created_at'));
        foreach (['aplicaciones_who5', 'aplicaciones_mdi', 'aplicaciones_asq'] as $tabla) {
            DB::table($tabla)->whereIn('user_id', $this->usuarios())
                ->whereDate('fecha', '>=', $ini)->whereDate('fecha', '<=', $fin)
                ->select('user_id', 'created_at')->cursor()->each(fn ($f) => $sumar($f, 'created_at'));
        }
        DB::table('interacciones')->whereIn('user_id', $this->usuarios())
            ->where('ocurrido_en', '>=', $desde->copy()->startOfDay())->where('ocurrido_en', '<=', $hasta->copy()->endOfDay())
            ->select('user_id', 'ocurrido_en')->cursor()->each(fn ($f) => $sumar($f, 'ocurrido_en'));

        $total = array_sum($porHora);
        if ($total === 0) {
            return ['con_datos' => false];
        }

        $noche = array_sum(array_map(fn ($h) => $porHora[$h], self::HORAS_NOCHE));
        $pctNoche = (int) round($noche / $total * 100);

        // El área más nocturna, con al menos 20 eventos para que el % signifique algo.
        $area = null;
        $areaPct = null;
        foreach ($porArea as $nombre => $a) {
            if (($a['total'] ?? 0) < 20 || count($porArea) < 2) {
                continue;
            }
            $pct = (int) round(($a['noche'] ?? 0) / $a['total'] * 100);
            if ($pct > $pctNoche && ($areaPct === null || $pct > $areaPct)) {
                [$area, $areaPct] = [$nombre, $pct];
            }
        }

        return [
            'con_datos' => true,
            'datos' => array_map(fn ($h) => ['k' => str_pad((string) $h, 2, '0', STR_PAD_LEFT), 'v' => round($porHora[$h] / $total * 100, 1)], range(0, 23)),
            'noche_pct' => $pctNoche,
            'total' => $total,
            'area_noche' => $area,
            'area_noche_pct' => $areaPct,
        ];
    }

    /* ──────────────────────────── Utilidades ──────────────────────────── */

    private function casosEntre(Carbon $desde, Carbon $hasta): Collection
    {
        $ini = $desde->copy()->startOfDay();
        $fin = $hasta->copy()->endOfDay();

        return $this->casos->filter(fn ($c) => $c->disparado_en && $c->disparado_en->between($ini, $fin))->values();
    }

    /** @return array<int, array<string, true>> user_id → días con registro diario en el periodo */
    private function diasConRegistro(Carbon $desde, Carbon $hasta): array
    {
        $dias = [];
        DB::table('mood_logs')->whereIn('user_id', $this->usuarios())
            ->whereDate('logged_date', '>=', $desde->toDateString())->whereDate('logged_date', '<=', $hasta->toDateString())
            ->select('user_id', 'logged_date')->cursor()
            ->each(function ($f) use (&$dias) {
                $dias[(int) $f->user_id][substr((string) $f->logged_date, 0, 10)] = true;
            });

        return $dias;
    }

    /** Promedio ponderado de los componentes con dato (los pesos se reparten entre ellos). */
    private static function ponderar(array $componentes, array $pesos): ?int
    {
        $suma = 0;
        $peso = 0;
        foreach ($pesos as $clave => $w) {
            if ($componentes[$clave] !== null) {
                $suma += $w * $componentes[$clave];
                $peso += $w;
            }
        }

        return $peso > 0 ? (int) round($suma / $peso) : null;
    }

    /** Coeficiente de Gini: 0 = alertas repartidas por igual, 1 = todas en un área. */
    public static function gini(array $valores): float
    {
        $n = count($valores);
        $media = $n > 0 ? array_sum($valores) / $n : 0;
        if ($n < 2 || $media <= 0) {
            return 0.0;
        }
        $diferencias = 0;
        foreach ($valores as $a) {
            foreach ($valores as $b) {
                $diferencias += abs($a - $b);
            }
        }

        return $diferencias / (2 * $n * $n * $media);
    }

    /** Racha más larga de días seguidos. */
    public static function rachaMaxima(array $fechas): int
    {
        sort($fechas);
        $mejor = 0;
        $actual = 0;
        $anterior = null;
        foreach ($fechas as $f) {
            $actual = $anterior !== null && Carbon::parse($anterior)->addDay()->toDateString() === $f ? $actual + 1 : 1;
            $mejor = max($mejor, $actual);
            $anterior = $f;
        }

        return $mejor;
    }

    /** Las semanas sin dato toman el último valor conocido (las primeras, el primero). */
    private static function rellenar(array $serie): array
    {
        $primero = collect($serie)->first(fn ($v) => $v !== null);
        $ultimo = $primero;
        foreach ($serie as $k => $v) {
            $serie[$k] = $v ?? $ultimo;
            $ultimo = $serie[$k];
        }

        return $serie;
    }
}
