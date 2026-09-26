<?php

namespace App\Services;

use App\Models\AuditoriaClinica;
use App\Models\EntregaResumen;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Indicadores del detalle de una institución: semáforo por departamento,
 * cola de atención clínica y cumplimiento del protocolo.
 *
 * El semáforo y la cola son del plano clínico acreditado (como la lista de
 * colaboradores): una cuenta de administración sin acreditación sólo recibe
 * el cumplimiento del protocolo, que es operativo y no dice nada de nadie.
 */
class IndicadoresInstitucionService
{
    /** Ventana del cumplimiento del protocolo. */
    public const DIAS_PROTOCOLO = 90;

    public const SLA_CONTACTO_MIN = 30;

    private const ORIGEN_CASO = [
        'who5' => 'WHO-5 bajo el corte',
        'mdi' => 'MDI en rango severo',
        'asq' => 'ASQ positiva',
        'puchol' => 'Cuestionario Puchol',
        'manual' => 'Elevación manual por un clínico',
    ];

    public function __construct(private EstructuraInstitucionalService $estructura)
    {
    }

    /**
     * @param  bool  $planoClinico  el semáforo y la cola son del plano clínico acreditado;
     *                              sin acreditación sólo se calcula el cumplimiento del protocolo.
     */
    public function resumen(Institucion $institucion, bool $planoClinico = false): array
    {
        $hoy = Carbon::today();

        if (!$planoClinico) {
            return ['departamentos' => null, 'cola' => null, 'protocolo' => $this->protocolo($institucion, $hoy)];
        }

        $miembros = Membresia::query()->where('institucion_id', $institucion->id)->enPadron()->soloColaboradores()
            ->get(['id', 'user_id', 'estado', 'departamento_id', 'activado_en', 'created_at']);
        $usuarios = $miembros->pluck('user_id')->map(fn ($u) => (int) $u)->unique()->values()->all();

        $abiertos = EventoCrisis::query()->where('institucion_id', $institucion->id)->abiertos()->porPrioridad()
            ->with('user:id,name')
            ->get();

        $niveles = PlataformaService::conCasosAbiertos(self::nivelesVigentes($usuarios, $hoy), $abiertos, $usuarios);
        $dias = self::diasConRegistro($usuarios, $hoy->copy()->subDays(29), $hoy);
        $ultimoRegistro = self::ultimoRegistro($usuarios);
        $who5 = self::ultimaWho5($usuarios, $hoy);

        $limiteSilencio = $hoy->copy()->subDays(PlataformaService::DIAS_SILENCIO)->toDateString();
        $enSilencio = $miembros->where('estado', 'activo')
            ->filter(fn ($m) => ($ultimoRegistro[(int) $m->user_id] ?? '9999') <= $limiteSilencio)
            ->count();

        return [
            'departamentos' => $this->porDepartamento($institucion, $miembros, $niveles, $dias, $who5, $abiertos, $hoy),
            'cola' => $this->cola($abiertos, $enSilencio),
            'protocolo' => $this->protocolo($institucion, $hoy),
        ];
    }

    /* ──────────────────────────── Semáforo por departamento ──────────────────────────── */

    private function porDepartamento(Institucion $institucion, Collection $miembros, array $niveles, array $dias, array $who5, Collection $abiertos, Carbon $hoy): array
    {
        // Cada área hoja del árbol es una fila. Una macro-área con personas
        // asignadas directamente tiene además su propia fila.
        $filas = [];
        $areaDe = [];
        foreach ($this->estructura->arbol($institucion) as $macro) {
            $m = $macro['modelo'];
            $filas[$m->id] = ['nombre' => $m->nombre];
            $areaDe[$m->id] = $m->id;
            foreach ($macro['hijos'] as $hijo) {
                $filas[$hijo['modelo']->id] = ['nombre' => $m->nombre . ' — ' . $hijo['modelo']->nombre];
                $areaDe[$hijo['modelo']->id] = $hijo['modelo']->id;
            }
        }
        $filas[0] = ['nombre' => 'Sin área asignada'];

        $casosPorUsuario = $abiertos->groupBy('user_id');
        $grupos = $miembros->groupBy(fn ($m) => $areaDe[(int) $m->departamento_id] ?? 0);

        $resultado = [];
        foreach ($filas as $id => $fila) {
            $gente = $grupos->get($id, collect());
            if ($gente->isEmpty()) {
                continue;
            }
            $activos = $gente->where('estado', 'activo');
            $barra = PlataformaService::barraSemaforo(
                $activos->pluck('user_id'),
                $gente->where('estado', '!=', 'activo')->pluck('user_id'),
                $niveles,
            );
            $casos = $gente->flatMap(fn ($m) => $casosPorUsuario->get($m->user_id, collect()));
            $escalas = $gente->map(fn ($m) => $who5[(int) $m->user_id] ?? null)->filter(fn ($v) => $v !== null);

            $resultado[] = [
                'nombre' => $fila['nombre'],
                'padron' => $gente->count(),
                'activos' => $activos->count(),
                'barra' => $barra,
                'who5' => $escalas->isNotEmpty() ? (int) round($escalas->avg()) : null,
                'adherencia' => self::adherencia($activos, $dias, $hoy),
                'alertas' => $casos->count(),
                'nivel_alerta' => $casos->contains(fn ($c) => in_array($c->nivel, ['ROJO', 'ROJO_AGUDO'], true)) ? 'rojo' : ($casos->isNotEmpty() ? 'naranja' : null),
            ];
        }

        usort($resultado, fn ($a, $b) => ($b['alertas'] <=> $a['alertas']) ?: strcasecmp($a['nombre'], $b['nombre']));

        $general = PlataformaService::barraSemaforo(
            $miembros->where('estado', 'activo')->pluck('user_id'),
            $miembros->where('estado', '!=', 'activo')->pluck('user_id'),
            $niveles,
        );

        // Hallazgo: el área que concentra las alertas frente a su peso en el padrón.
        $totalAlertas = array_sum(array_column($resultado, 'alertas'));
        $totalPadron = max(1, $miembros->count());
        $hallazgo = null;
        if ($totalAlertas > 0 && count($resultado) > 1) {
            $mayor = $resultado[0];
            $hallazgo = sprintf(
                '%s concentra el %d%% de las alertas abiertas con el %d%% del padrón.',
                $mayor['nombre'],
                (int) round($mayor['alertas'] / $totalAlertas * 100),
                (int) round($mayor['padron'] / $totalPadron * 100)
            );
        } elseif ($totalAlertas === 0) {
            $hallazgo = 'Ninguna área tiene alertas abiertas.';
        }

        return ['general' => $general, 'filas' => $resultado, 'hallazgo' => $hallazgo];
    }

    /* ──────────────────────────── Cola de atención ──────────────────────────── */

    private function cola(Collection $abiertos, int $enSilencio): array
    {
        $casos = $abiertos->map(function (EventoCrisis $c) {
            $agudo = $c->nivel === 'ROJO_AGUDO';
            $origen = self::ORIGEN_CASO[$c->origen] ?? 'Motor clínico';

            return [
                'codigo' => ExpedienteClinicoService::codigoCaso($c),
                'titulo' => $origen . ($agudo ? ' · aguda' : ''),
                'persona' => $c->user?->name,
                'nivel' => $c->nivel,
                'tono' => in_array($c->nivel, ['ROJO', 'ROJO_AGUDO'], true) ? 'crit' : 'warn',
                'icono' => match (true) {
                    $agudo => 'fa-heart-pulse',
                    $c->origen === 'asq' => 'fa-comment-dots',
                    $c->origen === 'manual' => 'fa-user-doctor',
                    default => 'fa-triangle-exclamation',
                },
                'detalle' => ($c->contactado_en
                        ? 'Contacto humano registrado ' . self::cuando($c->contactado_en) . '. Falta verificar el cierre.'
                        : 'Protocolo activo desde ' . $c->disparado_en?->format('H:i') . '. Contacto humano pendiente.')
                    . ($c->salida_sin_contacto ? ' Salió del botón de crisis sin contacto.' : ''),
                'cuando' => self::cuando($c->disparado_en),
            ];
        })->values()->all();

        return ['casos' => $casos, 'abiertos' => count($casos), 'silencio' => $enSilencio];
    }

    /* ──────────────────────────── Cumplimiento del protocolo ──────────────────────────── */

    private function protocolo(Institucion $institucion, Carbon $hoy): array
    {
        $desde = $hoy->copy()->subDays(self::DIAS_PROTOCOLO - 1)->startOfDay();
        $casos = EventoCrisis::query()->where('institucion_id', $institucion->id)
            ->where('disparado_en', '>=', $desde)
            ->get(['id', 'disparado_en', 'contactado_en', 'estado', 'cierre_verificado_por', 'salida_sin_contacto']);

        $total = $casos->count();
        $aTiempo = $casos->filter(fn ($c) => $c->contactado_en
            && abs($c->disparado_en->diffInMinutes($c->contactado_en)) <= self::SLA_CONTACTO_MIN)->count();
        $cerrados = $casos->where('estado', 'cerrado');
        $verificados = $cerrados->filter(fn ($c) => $c->cierre_verificado_por !== null)->count();

        $entregas = EntregaResumen::query()
            ->whereIn('membresia_id', Membresia::query()->where('institucion_id', $institucion->id)->select('id'))
            ->where('created_at', '>=', $desde)
            ->count();

        $accesos = AuditoriaClinica::query()->where('institucion_id', $institucion->id)
            ->where('accion', 'consulta_detalle')
            ->where('created_at', '>=', $desde);
        $totalAccesos = (clone $accesos)->count();
        $sinMotivo = (clone $accesos)->where(fn ($q) => $q->whereNull('motivo')->orWhere('motivo', ''))->count();

        $nda = $institucion->profesional_nda_hasta;

        return [
            'dias' => self::DIAS_PROTOCOLO,
            'casos' => $total,
            'contacto_a_tiempo' => $aTiempo,
            'contacto_pct' => $total > 0 ? (int) round($aTiempo / $total * 100) : null,
            'cierre_verificado' => $verificados,
            'en_curso' => $total - $cerrados->count(),
            'salidas_sin_contacto' => $casos->where('salida_sin_contacto', true)->count(),
            'entregas' => $entregas,
            'nda' => $nda === null ? 'sin fecha de NDA' : ($nda->isPast() && !$nda->isToday() ? 'NDA vencido' : 'NDA vigente'),
            'nda_vigente' => $nda !== null && ($nda->isFuture() || $nda->isToday()),
            'accesos' => $totalAccesos,
            'accesos_sin_motivo' => $sinMotivo,
        ];
    }

    /* ──────────────────────────── Fuentes compartidas ──────────────────────────── */

    /**
     * Última clasificación de cada persona en los 30 días previos al corte.
     *
     * @param  array<int>  $usuarios
     * @return array<int, string> user_id → nivel
     */
    public static function nivelesVigentes(array $usuarios, Carbon $corte): array
    {
        if ($usuarios === []) {
            return [];
        }

        $niveles = [];
        foreach (array_chunk($usuarios, 900) as $bloque) {
            DB::table('clasificaciones')
                ->whereIn('user_id', $bloque)
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
        }

        return $niveles;
    }

    /** @return array<int, array<string, true>> user_id → días (Y-m-d) con registro diario */
    public static function diasConRegistro(array $usuarios, Carbon $desde, Carbon $hasta): array
    {
        $dias = [];
        foreach (array_chunk($usuarios, 900) as $bloque) {
            DB::table('mood_logs')
                ->whereIn('user_id', $bloque)
                ->whereDate('logged_date', '>=', $desde->toDateString())
                ->whereDate('logged_date', '<=', $hasta->toDateString())
                ->select('user_id', 'logged_date')
                ->cursor()
                ->each(function ($f) use (&$dias) {
                    $dias[(int) $f->user_id][substr((string) $f->logged_date, 0, 10)] = true;
                });
        }

        return $dias;
    }

    /** @return array<int, string> user_id → fecha de su último registro diario */
    public static function ultimoRegistro(array $usuarios): array
    {
        $ultimos = [];
        foreach (array_chunk($usuarios, 900) as $bloque) {
            DB::table('mood_logs')->whereIn('user_id', $bloque)
                ->groupBy('user_id')->selectRaw('user_id, max(logged_date) as ultimo')
                ->get()
                ->each(function ($f) use (&$ultimos) {
                    $ultimos[(int) $f->user_id] = substr((string) $f->ultimo, 0, 10);
                });
        }

        return $ultimos;
    }

    /** @return array<int, int> user_id → escala 0–100 de su último WHO-5 hasta el corte */
    public static function ultimaWho5(array $usuarios, Carbon $corte, ?Carbon $desde = null): array
    {
        $escalas = [];
        foreach (array_chunk($usuarios, 900) as $bloque) {
            DB::table('aplicaciones_who5')->whereIn('user_id', $bloque)
                ->whereDate('fecha', '<=', $corte->toDateString())
                ->when($desde, fn ($q) => $q->whereDate('fecha', '>=', $desde->toDateString()))
                ->orderBy('fecha')->orderBy('id')
                ->select('user_id', 'escala')
                ->cursor()
                ->each(function ($f) use (&$escalas) {
                    $escalas[(int) $f->user_id] = (int) $f->escala;
                });
        }

        return $escalas;
    }

    /**
     * Días con registro ÷ días posibles desde que activó la cuenta (tope 30),
     * la misma regla que el tablero de la plataforma.
     */
    public static function adherencia(Collection $activos, array $dias, Carbon $hoy, int $ventana = 30): ?int
    {
        $posibles = 0;
        $conRegistro = 0;
        foreach ($activos as $m) {
            $desde = ($m->activado_en ?? $m->created_at)?->copy()->startOfDay();
            $n = $desde ? max(1, min($ventana, (int) $desde->diffInDays($hoy) + 1)) : $ventana;
            $posibles += $n;
            $conRegistro += min($n, count($dias[(int) $m->user_id] ?? []));
        }

        return $posibles > 0 ? (int) round($conRegistro / $posibles * 100) : null;
    }

    private static function cuando(?Carbon $momento): string
    {
        if ($momento === null) {
            return '';
        }

        return match (true) {
            $momento->isToday() => 'hoy ' . $momento->format('H:i'),
            $momento->isYesterday() => 'ayer ' . $momento->format('H:i'),
            default => $momento->format('d/m H:i'),
        };
    }
}
