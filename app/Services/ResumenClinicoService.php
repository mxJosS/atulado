<?php

namespace App\Services;

use App\Models\AplicacionAsq;
use App\Models\AplicacionMdi;
use App\Models\AplicacionPuchol;
use App\Models\AplicacionWho5;
use App\Models\AuditoriaClinica;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\SerieVigilancia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Resumen clínico individual que se entrega al profesional designado.
 *
 * Arma, con datos reales de la persona, el documento por páginas: qué se
 * incluye lo decide el alcance elegido al emitirlo. Sólo lectura; quien
 * llama guarda el HTML, deja la huella en la bitácora y envía el enlace.
 */
class ResumenClinicoService
{
    /** Secciones opcionales del documento (clave => texto de la casilla). */
    public const ALCANCE = [
        'trayectoria' => 'Trayectoria del ánimo y reglas disparadas',
        'respuestas' => 'Respuestas WHO-5, MDI y ASQ ítem por ítem',
        'texto' => 'Texto libre con contexto de las banderas',
        'recursos' => 'Plan de seguridad y contactos de emergencia',
        'historial' => 'Historial completo de 12 meses',
    ];

    /** Marcadas por defecto: todo menos el historial de 12 meses. */
    public const ALCANCE_PREDETERMINADO = ['trayectoria', 'respuestas', 'texto', 'recursos'];

    /** Nombre corto de cada ítem del WHO-5, para «ítems que destacan». */
    private const WHO5_CORTO = [1 => 'ánimo', 2 => 'calma', 3 => 'energía', 4 => 'descanso', 5 => 'interés'];

    /** Nombre corto de cada ítem del MDI. */
    private const MDI_CORTO = [
        'i1' => 'tristeza', 'i2' => 'interés', 'i3' => 'energía', 'i4' => 'confianza', 'i5' => 'culpa',
        'i6' => 'la vida no vale la pena', 'i7' => 'concentración', 'i8' => 'inquietud/lentitud', 'i9' => 'sueño', 'i10' => 'apetito',
    ];

    private const RESULTADO_ASQ = [
        'POSITIVA_AGUDA' => 'Positiva aguda',
        'POSITIVA_NO_AGUDA' => 'Positiva no aguda',
        'NEGATIVA' => 'Negativa',
    ];

    private const PAGINA_TEXTO = 22;
    private const PAGINA_BITACORA = 28;

    public function __construct(private ExpedienteClinicoService $expediente)
    {
    }

    /**
     * @param  list<string>  $alcance  claves de self::ALCANCE
     * @param  array{folio:string, destinatario:string, destinatario_detalle:string, motivo:string, generado_por:User, generado_en:Carbon, vence_en:Carbon}  $emision
     * @return array{html:string, paginas:int, desde:Carbon, hasta:Carbon}
     */
    public function construir(Membresia $membresia, Institucion $institucion, array $alcance, array $emision): array
    {
        $alcance = array_values(array_intersect(array_keys(self::ALCANCE), $alcance));
        $datos = $this->datos($membresia, $institucion, $alcance, $emision);

        return [
            'html' => view('admin.instituciones.resumen-clinico', $datos)->render(),
            'paginas' => count($datos['paginas']),
            'desde' => $datos['desde'],
            'hasta' => $datos['hasta'],
        ];
    }

    /** @return array<string,mixed> */
    public function datos(Membresia $membresia, Institucion $institucion, array $alcance, array $emision): array
    {
        $ficha = $this->expediente->ficha($membresia);
        $user = $ficha['user'];
        $emitido = $emision['generado_en'];
        $historial = in_array('historial', $alcance, true);

        $hasta = $emitido->copy();
        $desde = $historial ? $emitido->copy()->subMonths(12)->addDay()->startOfDay() : $emitido->copy()->subDays(90)->startOfDay();

        $caso = $ficha['caso_abierto'] ?? $ficha['casos']->first();

        $logs = MoodLog::where('user_id', $user->id)->orderBy('logged_date')->orderBy('id')->get();
        $logs->each(fn ($l) => $l->setAttribute('valor', $l->valor_invertido ?? max(0, min(4, 5 - (int) $l->score))));

        $senales = SerieVigilancia::where('user_id', $user->id)->whereNotNull('ultima_senal')
            ->orderBy('fecha')->get(['fecha', 'ultima_senal']);

        $datos = $ficha + [
            'institucion' => $institucion,
            'alcance' => $alcance,
            'folio' => $emision['folio'],
            'destinatario' => $emision['destinatario'],
            'destinatarioDetalle' => $emision['destinatario_detalle'],
            'motivo' => $emision['motivo'],
            'generadoPor' => $emision['generado_por'],
            'generadoEn' => $emitido,
            'venceEn' => $emision['vence_en'],
            'desde' => $desde,
            'hasta' => $hasta,
            'caso' => $caso,
            'protocoloActivo' => $ficha['caso_abierto'] !== null,
            'identificacion' => $this->identificacion($membresia, $user, $logs, $caso, $emitido),
            'narrativa' => $this->narrativa($ficha, $caso, $logs),
            'grafica' => $this->grafica($logs, $senales, $ficha['base30'], $emitido),
            'instrumentos' => $this->instrumentos($ficha),
            'encadenamiento' => $this->encadenamiento($ficha['mdi'], $ficha['asq']),
            'textos' => $this->textos($logs, $historial ? $desde : $emitido->copy()->subDays(29)->startOfDay(), $historial ? null : 12),
            'recursos' => $this->recursos($ficha['plan']),
            'lineasCrisis' => config('clinical.crisis_numbers', []),
            'cronologia' => $this->cronologia($user->id, $caso, $logs, $emision),
            'queSigue' => $this->queSigue($caso),
        ];

        $anexo = $historial ? $this->anexo($user->id, $logs, $senales, $desde, $datos) : null;
        $datos['anexo'] = $anexo;
        $datos['paginas'] = $this->paginas($alcance, $datos, $anexo);

        return $datos;
    }

    /* ─────────────────────────── Secciones ─────────────────────────── */

    private function identificacion(Membresia $membresia, User $user, Collection $logs, ?EventoCrisis $caso, Carbon $emitido): array
    {
        $desde = $membresia->activado_en ?? $membresia->created_at ?? $user->created_at;
        $meses = $desde ? (int) $desde->diffInMonths($emitido) : null;

        $turno = $membresia->turno ? ucfirst($membresia->turno) : null;
        $jornada = trim(($turno ?? '') . ($membresia->horario ? ($turno ? ', ' : '') . $membresia->horario : ''));

        return [
            'jornada' => $jornada !== '' ? $jornada : null,
            'puesto' => $membresia->puesto,
            'tiempo' => $desde
                ? ($meses < 1 ? 'Menos de un mes' : $meses . ($meses === 1 ? ' mes' : ' meses')) . ' · ' . $logs->count() . ($logs->count() === 1 ? ' registro' : ' registros')
                : $logs->count() . ' registros',
            'caso' => $caso
                ? ExpedienteClinicoService::codigoCaso($caso) . ' · ' . ($caso->estaCerrado() ? 'cerrado' : 'abierto')
                    . ' ' . $caso->disparado_en?->format('d/m/Y H:i')
                : 'Sin caso de crisis',
        ];
    }

    /** @return list<string> párrafos */
    private function narrativa(array $ficha, ?EventoCrisis $caso, Collection $logs): array
    {
        $parrafos = [];

        if ($ficha['base30'] !== null) {
            $texto = 'En el registro diario (0 = excelente, 4 = terrible) la persona tiene una línea base de 30 días de '
                . $this->num($ficha['base30'])
                . ($ficha['movil7'] !== null ? ' y una media móvil de 7 días de ' . $this->num($ficha['movil7']) : '') . '.';

            $reglas = collect($ficha['reglas'])->sort()->map(
                fn ($fecha, $clave) => ExpedienteClinicoService::REGLAS[$clave][2] . ' (' . ExpedienteClinicoService::REGLAS[$clave][0] . ') el ' . $fecha->format('d/m')
            )->values()->all();

            $texto .= $reglas
                ? ' En los últimos 30 días se dispararon: ' . $this->lista($reglas) . '.'
                : ' En los últimos 30 días no se disparó ninguna regla de vigilancia.';

            $parrafos[] = $texto;
        } else {
            $parrafos[] = 'La persona aún no tiene suficientes registros diarios para calcular una línea base de 30 días.';
        }

        if (!$caso) {
            $parrafos[] = 'No hay un caso de crisis registrado para esta persona.'
                . ($ficha['clasificacion']
                    ? ' Su clasificación vigente proviene de ' . strtoupper((string) $ficha['clasificacion']->origen) . ' del ' . $ficha['clasificacion']->fecha->format('d/m/Y') . '.'
                    : '');

            return $parrafos;
        }

        $disparo = $caso->disparado_en ?? $caso->created_at;
        $cadena = [];

        $bandera = $logs->filter(fn ($l) => $l->bandera_lexica && $l->created_at && $l->created_at->between($disparo->copy()->subDays(3), $disparo))->last();
        if ($bandera) {
            $cadena[] = 'El registro del ' . $bandera->logged_date->format('d/m') . ' incluyó texto libre con términos de la categoría '
                . $this->lista($this->categorias($bandera->terminos_detectados ?? []), 'o') . ', lo que levantó una bandera para revisión humana';
        }

        $mdi = $ficha['mdi'];
        if ($mdi && $mdi->fecha->isSameDay($disparo)) {
            $cadena[] = "el MDI sumó {$mdi->total}/50 con el ítem 6 en {$mdi->i6}";
        }

        $asq = $ficha['asq'];
        if ($asq && $asq->fecha->isSameDay($disparo) && $asq->resultado !== 'NEGATIVA') {
            $cadena[] = $asq->resultado === 'POSITIVA_AGUDA'
                ? 'el ASQ resultó positivo agudo (pregunta 5 afirmativa o sin contestar)'
                : 'el ASQ resultó positivo no agudo';
        }

        $origen = match ($caso->origen) {
            'asq' => 'el tamizaje ASQ',
            'mdi' => 'el MDI (total de 30 o más)',
            'puchol' => 'el test del estado de ánimo (impulsos suicidas referidos)',
            'manual' => 'una elevación manual por criterio clínico',
            default => 'el motor clínico',
        };

        $texto = 'El caso ' . ExpedienteClinicoService::codigoCaso($caso) . ' se abrió el ' . $disparo->format('d/m/Y \a \l\a\s H:i')
            . " a partir de {$origen}, con nivel " . ($caso->nivel === 'ROJO_AGUDO' ? 'Rojo agudo' : 'Rojo') . '.';
        if ($cadena) {
            $texto .= ' ' . ucfirst($this->lista($cadena)) . '.';
        }

        if ($caso->origen === 'manual') {
            $elevacion = AuditoriaClinica::where('usuario_consultado_id', $caso->user_id)->where('accion', 'elevacion_nivel')
                ->where('created_at', '<=', $disparo->copy()->addMinute())->latest('id')->with('profesional:id,name')->first();
            if ($elevacion) {
                $texto .= ' Justificación de ' . ($elevacion->profesional?->name ?? 'el profesional') . ': «' . $elevacion->motivo . '».';
            }
        }

        $parrafos[] = $texto;

        return $parrafos;
    }

    /**
     * Gráfica de 30 días en SVG (sin JS, se imprime igual que en pantalla):
     * coordenadas ya calculadas de puntos, línea base, marcas de reglas y ejes.
     *
     * @return array<string,mixed>
     */
    private function grafica(Collection $logs, Collection $senales, ?float $base30, Carbon $emitido): array
    {
        $ancho = 640;
        $alto = 190;
        $izq = 34;
        $der = 12;
        $arriba = 12;
        $abajo = 26;
        $hoy = $emitido->copy()->startOfDay();
        $inicio = $hoy->copy()->subDays(29);

        $x = fn (int $i) => round($izq + $i * (($ancho - $izq - $der) / 29), 1);
        // 4 (terrible) arriba, 0 (excelente) abajo.
        $y = fn (float $v) => round($arriba + (4 - $v) * (($alto - $arriba - $abajo) / 4), 1);

        $porDia = $logs->filter(fn ($l) => $l->logged_date->between($inicio, $hoy))
            ->keyBy(fn ($l) => $l->logged_date->toDateString());
        $reglaPorDia = $senales->filter(fn ($s) => $s->fecha->between($inicio, $hoy))
            ->keyBy(fn ($s) => $s->fecha->toDateString());

        $puntos = [];
        $marcas = [];
        $etiquetas = [];
        for ($i = 0; $i < 30; $i++) {
            $dia = $inicio->copy()->addDays($i);
            $clave = $dia->toDateString();
            if ($i % 2 === 0 || $i === 29) {
                $etiquetas[] = ['x' => $x($i), 'texto' => $dia->format('d')];
            }
            if ($log = $porDia->get($clave)) {
                $puntos[] = ['x' => $x($i), 'y' => $y((int) $log->valor), 'v' => (int) $log->valor, 'dia' => $dia->format('d/m')];
            }
            if ($senal = $reglaPorDia->get($clave)) {
                $valor = $porDia->get($clave)?->valor;
                $marcas[] = [
                    'x' => $x($i),
                    'y' => $y($valor !== null ? (float) $valor : 4),
                    'regla' => ExpedienteClinicoService::REGLAS[$senal->ultima_senal][0] ?? '',
                ];
            }
        }

        return [
            'ancho' => $ancho,
            'alto' => $alto,
            'izq' => $izq,
            'der' => $der,
            'puntos' => $puntos,
            'linea' => implode(' ', array_map(fn ($p) => $p['x'] . ',' . $p['y'], $puntos)),
            'base' => $base30 !== null ? $y((float) $base30) : null,
            'base30' => $base30,
            'marcas' => $marcas,
            'etiquetas' => $etiquetas,
            'ejes' => array_map(fn ($v) => ['v' => $v, 'y' => $y($v)], [0, 1, 2, 3, 4]),
            'base_y' => $alto - $abajo,
        ];
    }

    /** @return list<array{instrumento:string, fecha:?string, resultado:string, interpretacion:string, destacan:string}> */
    private function instrumentos(array $ficha): array
    {
        $filas = [];
        $corte = (int) config('clinical.who5.corte_crudo_mdi', 12);

        foreach (['who5' => 'WHO-5', 'who5_anterior' => 'WHO-5 anterior'] as $clave => $nombre) {
            if (!$w = $ficha[$clave]) {
                continue;
            }
            $bajos = collect(range(1, 5))->filter(fn ($n) => (int) $w->{'i' . $n} <= 1);
            $filas[] = [
                'instrumento' => $nombre,
                'fecha' => $w->fecha->format('d/m/Y'),
                'resultado' => $w->escala . ' / 100',
                'interpretacion' => $w->crudo <= $corte
                    ? "Por debajo del corte (crudo {$w->crudo}/25)"
                    : ($bajos->isNotEmpty() ? 'Total en rango, con ítems en 0–1' : 'Dentro de rango'),
                'destacan' => $bajos->isNotEmpty()
                    ? ($bajos->count() === 1 ? 'Ítem ' : 'Ítems ') . $this->lista($bajos->all()) . ' en 0–1 (' . $this->lista($bajos->map(fn ($n) => self::WHO5_CORTO[$n])->all()) . ')'
                    : '—',
            ];
        }

        if ($m = $ficha['mdi']) {
            $valores = collect(self::MDI_CORTO)->mapWithKeys(fn ($c, $k) => [$k => $this->mdiValor($m, $k)]);
            $altos = $valores->filter(fn ($v, $k) => $v >= 4 && $k !== 'i6');
            $destacan = [];
            if ($m->i6 > 0) {
                $destacan[] = "Ítem 6 en {$m->i6}";
            }
            foreach ($altos as $k => $v) {
                $destacan[] = 'ítem ' . substr($k, 1) . ' (' . self::MDI_CORTO[$k] . ") en {$v}";
            }
            $filas[] = [
                'instrumento' => 'MDI',
                'fecha' => $m->fecha->format('d/m/Y'),
                'resultado' => $m->total . ' / 50',
                'interpretacion' => $this->nivelMdi((int) $m->total),
                'destacan' => $destacan ? ucfirst(implode(' · ', $destacan)) : '—',
            ];
        }

        if ($a = $ficha['asq']) {
            $positivas = collect(['p1', 'p2', 'p3', 'p4'])->filter(fn ($p) => in_array($a->{$p}, ['si', 'prefiero_no_contestar'], true))
                ->map(fn ($p) => substr($p, 1))->values();
            $destacan = [];
            if (in_array($a->p5, ['si', 'prefiero_no_contestar'], true)) {
                $destacan[] = 'Ítem 5 positivo';
            }
            if ($positivas->isNotEmpty()) {
                $destacan[] = ($positivas->count() === 1 ? 'ítem ' : 'ítems ') . $this->lista($positivas->all()) . ($positivas->count() === 1 ? ' positivo' : ' positivos');
            }
            $filas[] = [
                'instrumento' => 'ASQ',
                'fecha' => $a->fecha->format('d/m/Y'),
                'resultado' => self::RESULTADO_ASQ[$a->resultado] ?? $a->resultado,
                'interpretacion' => match ($a->resultado) {
                    'POSITIVA_AGUDA' => 'Ideación activa presente: riesgo inminente',
                    'POSITIVA_NO_AGUDA' => 'Ideación sin agudeza: contacto el mismo día',
                    default => 'Sin respuestas positivas',
                },
                'destacan' => $destacan ? ucfirst(implode(' · ', $destacan)) : '—',
            ];
        }

        if ($p = $ficha['puchol']) {
            $partes = [];
            $interpretaciones = [];
            foreach (['ansiedad' => 'A', 'fisica' => 'F', 'depresion' => 'D', 'suicidas' => 'S'] as $seccion => $letra) {
                if ($p->{$seccion} !== null) {
                    $partes[] = $letra . $p->{$seccion};
                    if ($t = PucholService::interpretar($seccion, (int) $p->{$seccion})) {
                        $interpretaciones[] = $t;
                    }
                }
            }
            $filas[] = [
                'instrumento' => 'Puchol',
                'fecha' => $p->fecha->format('d/m/Y'),
                'resultado' => implode(' · ', $partes) ?: 'Incompleto',
                'interpretacion' => $interpretaciones ? implode('; ', $interpretaciones) : 'Sin clave de interpretación',
                'destacan' => ($p->suicidas ?? 0) > 0 ? 'Impulsos suicidas referidos' : '—',
            ];
        }

        return $filas;
    }

    private function encadenamiento(?AplicacionMdi $mdi, ?AplicacionAsq $asq): ?string
    {
        if (!$mdi || !$asq || (int) $mdi->i6 === 0 || !$mdi->fecha->isSameDay($asq->fecha)) {
            return null;
        }

        return "El ítem 6 en {$mdi->i6} obliga a aplicar el ASQ de inmediato. El MDI se respondió a las "
            . $mdi->created_at?->format('H:i') . ' y el ASQ a las ' . $asq->created_at?->format('H:i') . '.';
    }

    /** @return list<array{cuando:Carbon, texto:string, categoria:string, bandera:bool}> */
    private function textos(Collection $logs, Carbon $desde, ?int $limite): array
    {
        $con = $logs->filter(fn ($l) => filled($l->journal_entry) && $l->logged_date->gte($desde))
            ->sortByDesc(fn ($l) => $l->created_at?->timestamp ?? 0)->values();

        if ($limite !== null && $con->count() > $limite) {
            // Primero las banderas, luego lo más reciente.
            $con = $con->sortBy(fn ($l) => $l->bandera_lexica ? 0 : 1, SORT_REGULAR, false)->take($limite)
                ->sortByDesc(fn ($l) => $l->created_at?->timestamp ?? 0)->values();
        }

        return $con->map(fn ($l) => [
            'cuando' => $l->created_at ?? $l->logged_date,
            'texto' => $l->journal_entry,
            'categoria' => $l->bandera_lexica ? implode(' · ', $this->categorias($l->terminos_detectados ?? [])) : '—',
            'bandera' => (bool) $l->bandera_lexica,
        ])->all();
    }

    /** @return list<array{titulo:string, items:list<string>}> */
    private function recursos($plan): array
    {
        if (!$plan) {
            return [];
        }

        $texto = fn ($lista) => collect((array) $lista)->map(fn ($v) => is_string($v) ? trim($v) : null)->filter()->values()->all();
        $contactos = fn ($lista) => collect((array) $lista)->map(fn ($c) => trim(($c['name'] ?? '')
            . (!empty($c['relationship']) ? ' (' . $c['relationship'] . ')' : '')
            . (!empty($c['note']) ? ' (' . $c['note'] . ')' : '')
            . (!empty($c['phone']) ? ' · ' . $c['phone'] : '')))->filter()->values()->all();

        return array_values(array_filter([
            ['titulo' => 'Señales que reconoce', 'items' => $texto($plan->warning_signs)],
            ['titulo' => 'Lo que le ayuda', 'items' => array_values(array_unique([...$texto($plan->internal_coping), ...$texto($plan->social_distractions)]))],
            ['titulo' => 'Lugares que le ayudan', 'items' => $texto($plan->safe_places)],
            ['titulo' => 'A quién puede llamar', 'items' => $contactos($plan->trusted_contacts)],
            ['titulo' => 'Profesionales', 'items' => $contactos($plan->professional_contacts)],
            ['titulo' => 'Entorno más seguro', 'items' => $texto(preg_split('/\r?\n/', (string) $plan->safe_environment_steps))],
            ['titulo' => 'Razones para vivir', 'items' => $texto($plan->reasons_to_live)],
        ], fn ($g) => $g['items'] !== []));
    }

    /**
     * Cronología: alrededor del caso (desde 24 h antes del disparo) o, sin
     * caso, de los últimos 7 días.
     *
     * @return list<array{cuando:?Carbon, texto:string}>
     */
    private function cronologia(int $userId, ?EventoCrisis $caso, Collection $logs, array $emision): array
    {
        $emitido = $emision['generado_en'];
        $desde = $caso ? ($caso->disparado_en ?? $caso->created_at)->copy()->subDay() : $emitido->copy()->subDays(7);
        $entre = fn (?Carbon $c) => $c && $c->gte($desde) && $c->lte($emitido);
        $eventos = collect();

        foreach ($logs as $l) {
            if ($entre($l->created_at)) {
                $eventos->push(['cuando' => $l->created_at, 'texto' => 'Registro diario: ' . (ExpedienteClinicoService::ESTADO_DIARIO[(int) $l->valor] ?? '—')
                    . (filled($l->journal_entry) ? ', con texto libre' : '')
                    . ($l->bandera_lexica ? '. El filtro léxico detecta la categoría ' . $this->lista($this->categorias($l->terminos_detectados ?? []), 'o') : '') . '.']);
            }
        }

        foreach (AplicacionWho5::where('user_id', $userId)->where('created_at', '>=', $desde)->get() as $a) {
            $eventos->push(['cuando' => $a->created_at, 'texto' => "WHO-5 ({$a->origen}): {$a->escala}/100."]);
        }
        foreach (AplicacionMdi::where('user_id', $userId)->where('created_at', '>=', $desde)->get() as $a) {
            $eventos->push(['cuando' => $a->created_at, 'texto' => "MDI: total {$a->total}/50, ítem 6 en {$a->i6}."]);
        }
        foreach (AplicacionAsq::where('user_id', $userId)->where('created_at', '>=', $desde)->get() as $a) {
            $eventos->push(['cuando' => $a->created_at, 'texto' => 'ASQ: ' . mb_strtolower(self::RESULTADO_ASQ[$a->resultado] ?? $a->resultado) . '.']);
        }

        if ($caso) {
            $codigo = ExpedienteClinicoService::codigoCaso($caso);
            $eventos->push(['cuando' => $caso->disparado_en, 'texto' => ($caso->nivel === 'ROJO_AGUDO' ? 'Rojo agudo' : 'Rojo') . ": se abre el caso {$codigo}."]);
            if ($caso->notificado_en) {
                $eventos->push(['cuando' => $caso->notificado_en, 'texto' => 'Un clínico ve el caso en la cola de atención.']);
            }
            if ($caso->contactado_en) {
                $sla = $caso->nivel === 'ROJO_AGUDO'
                    ? 'SLA: ' . (int) config('clinical.asq.tiempo_contacto_agudo_min', 5) . ' min'
                    : 'SLA: el mismo día';
                $eventos->push(['cuando' => $caso->contactado_en, 'texto' => 'Primer contacto humano'
                    . ($caso->contactadoPor ? ' de ' . $caso->contactadoPor->name : '') . ': '
                    . $caso->minutosHastaContacto() . " min desde el disparo ({$sla})."]);
            }
        }

        AuditoriaClinica::where('usuario_consultado_id', $userId)->where('created_at', '>=', $desde)
            ->with('profesional:id,name')->orderBy('id')->get()
            ->each(fn ($a) => $eventos->push([
                'cuando' => $a->created_at,
                'texto' => $a->accion_legible . ($a->profesional ? ' por ' . $a->profesional->name : '') . ($a->motivo ? '. Motivo: ' . $a->motivo : '') . '.',
            ]));

        $eventos->push(['cuando' => $emitido, 'texto' => "Emisión de este documento ({$emision['folio']})."]);

        $lista = $eventos->filter(fn ($e) => $e['cuando'] !== null)->sortBy(fn ($e) => $e['cuando']->timestamp)->values()->all();

        if ($caso && !$caso->estaCerrado()) {
            $lista[] = ['cuando' => null, 'texto' => 'Pendiente: cierre con contacto humano verificado y nota firmada del profesional.'];
        }

        return $lista;
    }

    private function queSigue(?EventoCrisis $caso): string
    {
        if ($caso && !$caso->estaCerrado()) {
            return 'El caso permanece abierto hasta que el profesional registre el contacto humano verificado y la nota de cierre. '
                . 'La plataforma no cierra este caso por vencimiento de tiempo ni por inactividad de la persona. '
                . 'Mientras el caso siga abierto, la persona recibe contenido de contención en lugar de cuestionarios nuevos.';
        }

        if ($caso) {
            return 'El caso ' . ExpedienteClinicoService::codigoCaso($caso) . ' se cerró con contacto humano verificado'
                . ($caso->verificadoPor ? ' por ' . $caso->verificadoPor->name : '') . '. '
                . 'Si reaparecen señales, un clínico acreditado puede reabrirlo; la reapertura queda en la bitácora.';
        }

        return 'No hay un caso de crisis abierto. La persona continúa con su registro diario y la vigilancia automática; '
            . 'cualquier señal nueva adelanta los cuestionarios y, si corresponde, abre un caso con contacto humano.';
    }

    /* ─────────────────────── Anexo de 12 meses ─────────────────────── */

    private function anexo(int $userId, Collection $logs, Collection $senales, Carbon $desde, array $datos): array
    {
        $meses = [];
        $cursor = $desde->copy()->startOfMonth();
        while ($cursor->lte($datos['hasta'])) {
            $delMes = $logs->filter(fn ($l) => $l->logged_date->isSameMonth($cursor));
            $meses[] = [
                'mes' => ucfirst($cursor->translatedFormat('F Y')),
                'registros' => $delMes->count(),
                'media' => $delMes->isNotEmpty() ? $this->num($delMes->avg('valor')) : '—',
                'dificiles' => $delMes->filter(fn ($l) => (int) $l->valor >= 3)->count(),
                'reglas' => $senales->filter(fn ($s) => $s->fecha->isSameMonth($cursor))->count(),
                'banderas' => $delMes->where('bandera_lexica', true)->count(),
            ];
            $cursor->addMonth();
        }

        $bitacora = AuditoriaClinica::where('usuario_consultado_id', $userId)->where('created_at', '>=', $desde)
            ->with('profesional:id,name')->orderByDesc('id')->get()
            ->map(fn ($a) => [
                'cuando' => $a->created_at,
                'que' => $a->accion_legible,
                'quien' => $a->profesional?->name,
                'detalle' => $a->motivo ?: $a->detalle,
            ])->all();

        return [
            'meses' => $meses,
            'casos' => $datos['casos'],
            'who5' => AplicacionWho5::where('user_id', $userId)->where('fecha', '>=', $desde->toDateString())->orderByDesc('fecha')->orderByDesc('id')->get(),
            'mdi' => AplicacionMdi::where('user_id', $userId)->where('fecha', '>=', $desde->toDateString())->orderByDesc('fecha')->orderByDesc('id')->get(),
            'asq' => AplicacionAsq::where('user_id', $userId)->where('fecha', '>=', $desde->toDateString())->orderByDesc('fecha')->orderByDesc('id')->get(),
            'puchol' => AplicacionPuchol::where('user_id', $userId)->where('fecha', '>=', $desde->toDateString())->orderByDesc('fecha')->orderByDesc('id')->get(),
            'textos' => $this->textos($logs, $desde, null),
            'bitacora' => $bitacora,
        ];
    }

    /* ─────────────────────────── Páginas ─────────────────────────── */

    /**
     * Qué secciones van en cada página. El documento base son 4 páginas;
     * el historial de 12 meses agrega el anexo.
     *
     * @return list<list<array{s:string, d?:mixed}>>
     */
    private function paginas(array $alcance, array $datos, ?array $anexo): array
    {
        $con = fn (string $clave) => in_array($clave, $alcance, true);

        $paginas = [
            array_filter([['s' => 'identificacion'], $con('trayectoria') ? ['s' => 'porque'] : null, $con('trayectoria') ? ['s' => 'trayectoria'] : null]),
            array_filter([['s' => 'instrumentos'], $con('respuestas') ? ['s' => 'who5'] : null, $con('respuestas') ? ['s' => 'mdi'] : null]),
            array_filter([$con('respuestas') ? ['s' => 'asq'] : null, $con('texto') ? ['s' => 'texto'] : null, $con('recursos') ? ['s' => 'recursos'] : null]),
            [['s' => 'cronologia'], ['s' => 'que_sigue']],
        ];

        if ($anexo) {
            $paginas[] = [['s' => 'anexo_portada'], ['s' => 'anexo_meses'], ['s' => 'anexo_casos']];
            $paginas[] = [['s' => 'anexo_who5'], ['s' => 'anexo_mdi']];
            $paginas[] = [['s' => 'anexo_asq'], ['s' => 'anexo_puchol']];
            foreach (array_chunk($anexo['textos'], self::PAGINA_TEXTO) ?: [[]] as $i => $bloque) {
                $paginas[] = [['s' => 'anexo_texto', 'd' => ['filas' => $bloque, 'continua' => $i > 0]]];
            }
            foreach (array_chunk($anexo['bitacora'], self::PAGINA_BITACORA) ?: [[]] as $i => $bloque) {
                $paginas[] = [['s' => 'anexo_bitacora', 'd' => ['filas' => $bloque, 'continua' => $i > 0]]];
            }
        }

        // Una página sin secciones (alcance mínimo) no se imprime.
        return array_values(array_map('array_values', array_filter($paginas, fn ($p) => $p !== [])));
    }

    /* ─────────────────────────── Utilidades ─────────────────────────── */

    private function mdiValor(AplicacionMdi $m, string $clave): int
    {
        return match ($clave) {
            'i8' => max((int) $m->i8a, (int) $m->i8b),
            'i10' => max((int) $m->i10a, (int) $m->i10b),
            default => (int) $m->{$clave},
        };
    }

    private function nivelMdi(int $total): string
    {
        return match (true) {
            $total >= (int) config('clinical.mdi.umbral_rojo_min', 30) => 'Nivel ROJO (severidad alta)',
            $total > (int) config('clinical.mdi.umbral_amarillo_max', 19) => 'Nivel NARANJA (leve a moderada)',
            default => 'Por debajo del rango de severidad',
        };
    }

    /** @param  list<string>  $terminos  términos detectados por el filtro léxico */
    public function categorias(array $terminos): array
    {
        $categorias = [];
        foreach ($terminos as $termino) {
            $encontrada = 'Término crítico';
            foreach (config('clinical.categorias_terminos', []) as $categoria => $lista) {
                if (in_array($termino, $lista, true)) {
                    $encontrada = $categoria;
                    break;
                }
            }
            $categorias[$encontrada] = true;
        }

        return array_keys($categorias) ?: ['Término crítico'];
    }

    private function num($valor): string
    {
        return number_format((float) $valor, 1, '.', '');
    }

    /** «a, b y c» */
    private function lista(array $partes, string $y = 'y'): string
    {
        $partes = array_values(array_filter(array_map('strval', $partes), fn ($p) => $p !== ''));
        if (count($partes) <= 1) {
            return $partes[0] ?? '';
        }
        $ultima = array_pop($partes);

        return implode(', ', $partes) . " {$y} {$ultima}";
    }
}
