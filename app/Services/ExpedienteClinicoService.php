<?php

namespace App\Services;

use App\Models\AplicacionAsq;
use App\Models\AplicacionMdi;
use App\Models\AplicacionPuchol;
use App\Models\AplicacionWho5;
use App\Models\AuditoriaClinica;
use App\Models\Clasificacion;
use App\Models\ContactoEmergencia;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\SerieVigilancia;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Plano clínico de una institución: la lista de colaboradores con su
 * semáforo y la ficha individual. Sólo lectura; quien llama decide si el
 * usuario tiene acreditación clínica y deja la huella en la bitácora.
 */
class ExpedienteClinicoService
{
    public const POR_PAGINA = 25;

    /** Orden de prioridad clínica: primero lo más grave. */
    public const PRIORIDAD = ['ROJO_AGUDO' => 0, 'ROJO' => 1, 'NARANJA' => 2, 'AMARILLO' => 3, 'SILENCIO' => 4, 'VERDE' => 5, 'SIN_DATOS' => 6];

    public const ETIQUETA_NIVEL = [
        'ROJO_AGUDO' => 'Rojo agudo', 'ROJO' => 'Rojo', 'NARANJA' => 'Naranja', 'AMARILLO' => 'Amarillo',
        'VERDE' => 'Verde', 'SILENCIO' => 'Silencio', 'SIN_DATOS' => 'Sin datos',
    ];

    public const COLOR_NIVEL = [
        'ROJO_AGUDO' => '#6E140C', 'ROJO' => '#B02418', 'NARANJA' => '#D9660F', 'AMARILLO' => '#DCAF00',
        'VERDE' => '#1E8449', 'SILENCIO' => '#8EADA4', 'SIN_DATOS' => '#A5B8B0',
    ];

    public const REGLAS = [
        'R1_DESVIACION' => ['R1', 'desviación', 'Desviación de línea base'],
        'R2_PERSISTENCIA' => ['R2', 'persistencia', 'Persistencia de días en Mal o Terrible'],
        'R3_CAIDA' => ['R3', 'caída', 'Caída abrupta sostenida'],
        'R4_SILENCIO' => ['R4', 'silencio', 'Silencio tras patrón regular'],
    ];

    /** Valor invertido del registro diario: 0 = Excelente … 4 = Terrible. */
    public const ESTADO_DIARIO = [0 => 'Excelente', 1 => 'Bien', 2 => 'Regular', 3 => 'Mal', 4 => 'Terrible'];

    public const WHO5_PREGUNTAS = [
        1 => 'Me he sentido alegre y de buen humor',
        2 => 'Me he sentido tranquilo y relajado',
        3 => 'Me he sentido activo y enérgico',
        4 => 'Me he despertado fresco y descansado',
        5 => 'Mi vida cotidiana ha estado llena de cosas que me interesan',
    ];

    public const WHO5_ESCALA = [0 => 'Nunca', 1 => 'De vez en cuando', 2 => 'Menos de la mitad', 3 => 'Más de la mitad', 4 => 'La mayor parte', 5 => 'Todo el tiempo'];

    public const MDI_PREGUNTAS = [
        'i1' => '¿Se ha sentido triste o con el ánimo bajo?',
        'i2' => '¿Ha perdido interés en sus actividades cotidianas?',
        'i3' => '¿Se ha sentido falto de energía y fuerzas?',
        'i4' => '¿Se ha sentido con menos confianza en sí mismo(a)?',
        'i5' => '¿Ha tenido mala conciencia o sentimientos de culpa?',
        'i6' => '¿Ha sentido que la vida no valía la pena vivirla?',
        'i7' => '¿Ha tenido dificultad para concentrarse?',
        'i8' => '¿Se ha sentido inquieto(a) (8a) o lento(a) (8b)?',
        'i9' => '¿Ha tenido dificultades para dormir?',
        'i10' => '¿Ha tenido menos (10a) o más (10b) apetito?',
    ];

    public const ASQ_PREGUNTAS = [
        'p1' => 'En las últimas semanas, ¿ha deseado estar muerto(a)?',
        'p2' => 'En las últimas semanas, ¿ha sentido que usted o su familia estarían mejor si estuviera muerto(a)?',
        'p3' => 'En la última semana, ¿ha estado pensando en suicidarse?',
        'p4' => '¿Alguna vez ha intentado suicidarse?',
        'p5' => '¿Está pensando en suicidarse en este momento?',
    ];

    /* ════════════════════════ Lista ════════════════════════ */

    /**
     * @param  array{q?:?string, departamento?:?string, turno?:?string, semaforo?:?string, bandera?:bool}  $filtros
     */
    public function colaboradores(Institucion $institucion, array $filtros, int $pagina = 1): LengthAwarePaginator
    {
        $hoy = Carbon::today();

        $consulta = Membresia::query()
            ->where('institucion_id', $institucion->id)
            ->enPadron()
            ->with(['user:id,name,email', 'departamento:id,nombre,parent_id'])
            ->addSelect([
                'membresias.*',
                'nivel_clasif' => Clasificacion::select('nivel')
                    ->whereColumn('clasificaciones.user_id', 'membresias.user_id')
                    ->orderByDesc('fecha')->orderByDesc('id')->limit(1),
                'nivel_crisis' => EventoCrisis::select('nivel')
                    ->whereColumn('eventos_crisis.user_id', 'membresias.user_id')
                    ->where('estado', '!=', 'cerrado')
                    ->orderByRaw("case when nivel = 'ROJO_AGUDO' then 0 else 1 end")->limit(1),
                'ultimo_registro' => MoodLog::selectRaw('max(created_at)')
                    ->whereColumn('mood_logs.user_id', 'membresias.user_id'),
                'banderas_recientes' => MoodLog::selectRaw('count(*)')
                    ->whereColumn('mood_logs.user_id', 'membresias.user_id')
                    ->where('bandera_lexica', true)
                    ->whereDate('logged_date', '>=', $hoy->copy()->subDays(14)),
            ]);

        if ($q = trim((string) ($filtros['q'] ?? ''))) {
            $folio = preg_match('/^col-?0*(\d+)$/i', $q, $m) ? (int) $m[1] : null;
            $consulta->where(function ($w) use ($q, $folio) {
                $w->where('numero_empleado', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
                if ($folio) {
                    $w->orWhere('membresias.id', $folio);
                }
            });
        }

        if ($departamento = (int) ($filtros['departamento'] ?? 0)) {
            $ids = $institucion->departamentos()->where('id', $departamento)->orWhere('parent_id', $departamento)->pluck('id');
            $consulta->whereIn('departamento_id', $ids->all() ?: [0]);
        }

        if (in_array($filtros['turno'] ?? null, ['matutino', 'vespertino', 'nocturno', 'mixto'], true)) {
            $consulta->where('turno', $filtros['turno']);
        }

        $todas = $consulta->get()->each(function (Membresia $m) use ($hoy) {
            $m->setAttribute('semaforo', $this->semaforo(
                $m->nivel_clasif,
                $m->nivel_crisis,
                $m->ultimo_registro ? Carbon::parse($m->ultimo_registro) : null,
                $hoy
            ));
        });

        $semaforo = strtoupper((string) ($filtros['semaforo'] ?? ''));
        if ($semaforo !== '') {
            $todas = $todas->filter(fn ($m) => $semaforo === 'ROJO'
                ? in_array($m->semaforo['nivel'], ['ROJO', 'ROJO_AGUDO'], true)
                : $m->semaforo['nivel'] === $semaforo);
        }

        if (!empty($filtros['bandera'])) {
            $todas = $todas->filter(fn ($m) => (int) $m->banderas_recientes > 0);
        }

        $ordenadas = $todas->sortBy([
            fn ($a, $b) => self::PRIORIDAD[$a->semaforo['nivel']] <=> self::PRIORIDAD[$b->semaforo['nivel']],
            fn ($a, $b) => strcasecmp((string) $a->user?->name, (string) $b->user?->name),
        ])->values();

        $pagina = max(1, $pagina);
        $filas = $ordenadas->slice(($pagina - 1) * self::POR_PAGINA, self::POR_PAGINA)->values();

        return new LengthAwarePaginator(
            $this->completarFilas($filas, $hoy),
            $ordenadas->count(),
            self::POR_PAGINA,
            $pagina,
            ['pageName' => 'pagina']
        );
    }

    /**
     * Carga en bloque, sólo para la página visible, lo que la lista necesita.
     *
     * @param  Collection<int,Membresia>  $filas
     * @return Collection<int,array<string,mixed>>
     */
    private function completarFilas(Collection $filas, Carbon $hoy): Collection
    {
        $ids = $filas->pluck('user_id')->all();
        if ($ids === []) {
            return collect();
        }

        $who5 = $this->ultimaPorUsuario(AplicacionWho5::class, $ids);
        $mdi = $this->ultimaPorUsuario(AplicacionMdi::class, $ids);
        $asq = $this->ultimaPorUsuario(AplicacionAsq::class, $ids);

        $contactos = ContactoEmergencia::whereIn('user_id', $ids)
            ->orderByDesc('es_principal')->orderBy('id')->get()->groupBy('user_id')->map->first();

        $senales = SerieVigilancia::whereIn('user_id', $ids)
            ->whereNotNull('ultima_senal')
            ->whereDate('fecha', '>=', $hoy->copy()->subDays(7))
            ->orderByDesc('fecha')->get()->groupBy('user_id')->map->first();

        $fechas = MoodLog::whereIn('user_id', $ids)
            ->whereDate('logged_date', '>=', $hoy->copy()->subDays(400))
            ->get(['user_id', 'logged_date'])
            ->groupBy('user_id')
            ->map(fn ($logs) => $logs->map(fn ($l) => $l->logged_date->toDateString())->unique()->all());

        return $filas->map(function (Membresia $m) use ($who5, $mdi, $asq, $contactos, $senales, $fechas, $hoy) {
            $senalesFila = [];
            if ($senal = $senales->get($m->user_id)) {
                $regla = self::REGLAS[$senal->ultima_senal] ?? null;
                if ($regla) {
                    $senalesFila[] = ['texto' => $regla[0] . ' ' . $regla[1], 'tono' => 'amber'];
                }
            }
            if ($m->semaforo['nivel'] === 'SILENCIO') {
                $senalesFila[] = ['texto' => 'R4 silencio', 'tono' => 'amber'];
            }
            if ((int) $m->banderas_recientes > 0) {
                $senalesFila[] = ['texto' => 'bandera léxica', 'tono' => 'red'];
            }

            return [
                'membresia' => $m,
                'semaforo' => $m->semaforo,
                'who5' => $who5->get($m->user_id)?->escala,
                'mdi' => $mdi->get($m->user_id)?->total,
                'asq' => $this->resumenAsq($asq->get($m->user_id)),
                'ultimo_registro' => $m->ultimo_registro ? Carbon::parse($m->ultimo_registro) : null,
                'racha' => self::racha($fechas->get($m->user_id, []), $hoy),
                'senales' => $senalesFila,
                'contacto' => $contactos->get($m->user_id),
            ];
        });
    }

    /** @param class-string<\Illuminate\Database\Eloquent\Model> $modelo */
    private function ultimaPorUsuario(string $modelo, array $ids): Collection
    {
        return $modelo::whereIn('user_id', $ids)
            ->orderByDesc('fecha')->orderByDesc('id')
            ->get()->groupBy('user_id')->map->first();
    }

    /**
     * Semáforo efectivo: un caso de crisis abierto manda sobre la última
     * clasificación; el silencio sólo se marca si no hay rojo encima.
     *
     * @return array{nivel:string, etiqueta:string, dias_silencio:?int}
     */
    public function semaforo(?string $clasificacion, ?string $crisisAbierta, ?Carbon $ultimoRegistro, ?Carbon $hoy = null): array
    {
        $hoy ??= Carbon::today();
        $nivel = $clasificacion ?: null;

        if ($crisisAbierta === 'ROJO_AGUDO') {
            $nivel = 'ROJO_AGUDO';
        } elseif ($crisisAbierta !== null && $nivel !== 'ROJO_AGUDO') {
            $nivel = 'ROJO';
        }

        $diasSilencio = $ultimoRegistro ? (int) $ultimoRegistro->copy()->startOfDay()->diffInDays($hoy) : null;
        $umbral = (int) config('clinical.surveillance.dias_silencio', 5);

        if (!in_array($nivel, ['ROJO', 'ROJO_AGUDO'], true) && $diasSilencio !== null && $diasSilencio >= $umbral) {
            return ['nivel' => 'SILENCIO', 'etiqueta' => "Silencio {$diasSilencio} d", 'dias_silencio' => $diasSilencio];
        }

        $nivel = isset(self::PRIORIDAD[$nivel ?? '']) ? $nivel : 'SIN_DATOS';

        return ['nivel' => $nivel, 'etiqueta' => self::ETIQUETA_NIVEL[$nivel], 'dias_silencio' => $diasSilencio];
    }

    /** @return array{texto:string, tono:string} */
    public function resumenAsq(?AplicacionAsq $asq): array
    {
        return match ($asq?->resultado) {
            'POSITIVA_AGUDA' => ['texto' => 'Positiva aguda', 'tono' => 'red'],
            'POSITIVA_NO_AGUDA' => ['texto' => 'Positiva', 'tono' => 'red'],
            'NEGATIVA' => ['texto' => 'Negativa', 'tono' => 'green'],
            default => ['texto' => 'No aplica', 'tono' => ''],
        };
    }

    /**
     * Días consecutivos con registro que terminan hoy o ayer.
     *
     * @param  list<string>  $fechas  Y-m-d
     */
    public static function racha(array $fechas, ?Carbon $hoy = null): int
    {
        $hoy ??= Carbon::today();
        $dias = array_flip($fechas);
        $cursor = isset($dias[$hoy->toDateString()]) ? $hoy->copy() : $hoy->copy()->subDay();
        $racha = 0;

        while (isset($dias[$cursor->toDateString()])) {
            $racha++;
            $cursor->subDay();
        }

        return $racha;
    }

    public static function codigoCaso(EventoCrisis $evento): string
    {
        return 'CR-' . ($evento->disparado_en ?? $evento->created_at)->format('Y') . '-' . str_pad((string) $evento->id, 4, '0', STR_PAD_LEFT);
    }

    /* ════════════════════════ Ficha ════════════════════════ */

    /** @return array<string,mixed> */
    public function ficha(Membresia $membresia): array
    {
        $membresia->loadMissing(['user', 'departamento.padre', 'institucion']);
        $user = $membresia->user;
        $hoy = Carbon::today();

        $logs = MoodLog::where('user_id', $user->id)->orderByDesc('logged_date')->orderByDesc('id')->get();
        $logs->each(fn ($l) => $l->setAttribute('valor', $l->valor_invertido ?? max(0, min(4, 5 - (int) $l->score))));
        $logs30 = $logs->filter(fn ($l) => $l->logged_date->gte($hoy->copy()->subDays(29)));

        $series = SerieVigilancia::where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->get();
        $serieActual = $series->first();
        $senalesPorFecha = $series->whereNotNull('ultima_senal')
            ->groupBy(fn ($s) => $s->fecha->toDateString())->map(fn ($g) => $g->first()->ultima_senal);

        $who5 = AplicacionWho5::where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->get();
        $mdi = AplicacionMdi::where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->get();
        $asq = AplicacionAsq::where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->get();
        $ciclosPuchol = AplicacionPuchol::where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->take(6)->get();
        $puchol = $ciclosPuchol->first(fn ($c) => $c->tieneAlgunaSeccion());

        $clasificacion = Clasificacion::where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->first();
        $casos = EventoCrisis::where('user_id', $user->id)->with(['contactadoPor:id,name', 'verificadoPor:id,name'])
            ->orderByDesc('disparado_en')->get();
        $casoAbierto = $casos->first(fn ($c) => !$c->estaCerrado());

        $ultimoRegistro = $logs->first()?->created_at;
        $semaforo = $this->semaforo($clasificacion?->nivel, $casoAbierto?->nivel ?? ($casoAbierto ? 'ROJO' : null), $ultimoRegistro, $hoy);

        $reglas = [];
        foreach ($series->whereNotNull('ultima_senal')->filter(fn ($s) => $s->fecha->gte($hoy->copy()->subDays(30))) as $s) {
            $reglas[$s->ultima_senal] ??= $s->fecha;
        }
        if ($semaforo['nivel'] === 'SILENCIO') {
            $reglas['R4_SILENCIO'] = $hoy;
        }

        $diario30 = $logs30->sortBy('logged_date')->values();

        return [
            'membresia' => $membresia,
            'user' => $user,
            'semaforo' => $semaforo,
            'area' => $membresia->departamento
                ? trim(($membresia->departamento->padre?->nombre ? $membresia->departamento->padre->nombre . ' › ' : '') . $membresia->departamento->nombre)
                : null,
            'clasificacion' => $clasificacion,
            'casos' => $casos,
            'caso_abierto' => $casoAbierto,
            'reglas' => $reglas,
            'contactos' => ContactoEmergencia::where('user_id', $user->id)->orderByDesc('es_principal')->orderBy('id')->get(),
            'plan' => $user->safetyPlan,

            'grafica_animo' => [
                'labels' => $diario30->map(fn ($l) => $l->logged_date->format('d/m'))->all(),
                'data' => $diario30->pluck('valor')->map(fn ($v) => (int) $v)->all(),
                'base30' => $serieActual?->base30 ?? ($logs30->isNotEmpty() ? round($logs30->avg('valor'), 2) : null),
            ],
            'base30' => $serieActual?->base30,
            'movil7' => $serieActual?->movil7,
            'diario' => $logs->take(14)->map(fn ($l) => [
                'log' => $l,
                'regla' => $senalesPorFecha->get($l->logged_date->toDateString()),
            ]),
            'registros_30' => $logs30->count(),
            'adherencia' => (int) round($logs30->count() / 30 * 100),
            'racha' => self::racha($logs->map(fn ($l) => $l->logged_date->toDateString())->unique()->all(), $hoy),

            'who5' => $who5->first(),
            'who5_anterior' => $who5->get(1),
            'who5_historial' => $who5->take(12)->reverse()->values(),
            'mdi' => $mdi->first(),
            'asq' => $asq->first(),
            'asq_resumen' => $this->resumenAsq($asq->first()),
            'lexico' => $logs->filter(fn ($l) => $l->bandera_lexica)->take(30)->values(),
            'puchol' => $puchol,
            'puchol_historial' => $ciclosPuchol,

            'bitacora' => $this->bitacora($user->id, $casos),
        ];
    }

    /**
     * Línea de tiempo: disparos, contactos y cierres de casos, y cada
     * entrada de la bitácora clínica sobre esta persona.
     *
     * @return Collection<int,array{cuando:Carbon, que:string, detalle:?string, tono:string}>
     */
    private function bitacora(int $userId, Collection $casos): Collection
    {
        $eventos = collect();

        foreach ($casos as $caso) {
            $codigo = self::codigoCaso($caso);
            $eventos->push([
                'cuando' => $caso->disparado_en ?? $caso->created_at,
                'que' => ($caso->nivel === 'ROJO_AGUDO' ? 'Rojo agudo' : 'Rojo') . " — caso {$codigo} abierto",
                'detalle' => 'Origen: ' . ($caso->origen ? strtoupper($caso->origen) : 'motor clínico')
                    . ($caso->estoy_con_alguien ? ' · la persona indicó «estoy con alguien»' : '')
                    . ($caso->salida_sin_contacto ? ' · salió de la pantalla de crisis sin contacto' : ''),
                'tono' => 'red',
            ]);

            if ($caso->contactado_en) {
                $minutos = $caso->minutosHastaContacto();
                $eventos->push([
                    'cuando' => $caso->contactado_en,
                    'que' => "Contacto humano registrado — {$codigo}",
                    'detalle' => trim(($caso->contactadoPor?->name ? 'Por ' . $caso->contactadoPor->name . ' · ' : '') . "{$minutos} min desde el disparo"),
                    'tono' => 'amber',
                ]);
            }
        }

        AuditoriaClinica::where('usuario_consultado_id', $userId)->with('profesional:id,name')
            ->latest('id')->take(100)->get()
            ->each(fn ($a) => $eventos->push([
                'cuando' => $a->created_at,
                'que' => $a->accion_legible . ($a->profesional ? ' — ' . $a->profesional->name : ''),
                'detalle' => $a->motivo ? 'Motivo: «' . $a->motivo . '»' : $a->detalle,
                'tono' => $a->accion === 'cierre_crisis' ? 'green' : '',
            ]));

        return $eventos->sortByDesc('cuando')->values();
    }
}
