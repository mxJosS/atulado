<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventoCrisis;
use App\Models\Membresia;
use App\Services\ClinicalEngineService;
use App\Services\ExpedienteClinicoService;
use Illuminate\Http\Request;

/**
 * Cola de atención de casos de crisis. Se atiende de forma manual: el
 * sistema no manda avisos automáticos, así que alguien del equipo clínico
 * tiene que revisar esta pantalla. Lo que vio por primera vez queda marcado
 * como notificado (notificado_en).
 */
class ColaAtencionController extends Controller
{
    public function __construct(private ClinicalEngineService $motor)
    {
    }

    public function index(Request $request)
    {
        $esClinico = $request->user()->isClinicoAcreditado();

        $abiertos = EventoCrisis::visiblesPara($request->user())->abiertos()->porPrioridad()
            ->with(['user:id,name,email', 'user.contactosEmergencia', 'institucion:id,slug,nombre_corto', 'contactadoPor:id,name'])
            ->get();

        // Sólo un clínico "ve" el caso: un administrador sin acreditación no lo marca.
        if ($esClinico) {
            EventoCrisis::whereIn('id', $abiertos->whereNull('notificado_en')->pluck('id'))
                ->update(['notificado_en' => now()]);
        }

        $membresias = Membresia::whereIn('user_id', $abiertos->pluck('user_id'))
            ->whereIn('estado', ['invitado', 'activo', 'suspendido'])
            ->get()->keyBy(fn ($m) => $m->user_id . '-' . $m->institucion_id);

        $cerrados = EventoCrisis::visiblesPara($request->user())->where('estado', 'cerrado')
            ->with(['institucion:id,nombre_corto', 'verificadoPor:id,name'])
            ->latest('updated_at')->take(15)->get();

        return view('admin.cola.index', compact('abiertos', 'cerrados', 'membresias', 'esClinico'));
    }

    public function contacto(Request $request, EventoCrisis $caso)
    {
        abort_unless($request->user()->isClinicoAcreditado(), 403, 'Se requiere acreditación clínica.');
        abort_unless($request->user()->puedeAtenderInstitucion($caso->institucion_id), 404);
        $datos = $request->validate(['nota' => ['nullable', 'string', 'max:1000']]);

        if ($caso->estaCerrado()) {
            return back()->with('error', 'Ese caso ya está cerrado.');
        }

        if (!$this->motor->registrarContactoHumano($caso, $request->user(), $datos['nota'] ?? null)) {
            return back()->with('error', 'Ese caso ya está cerrado.');
        }

        return back()->with('success', 'Contacto humano registrado en ' . ExpedienteClinicoService::codigoCaso($caso) . '.');
    }

    public function reabrir(Request $request, EventoCrisis $caso)
    {
        abort_unless($request->user()->isClinicoAcreditado(), 403, 'Se requiere acreditación clínica.');
        abort_unless($request->user()->puedeAtenderInstitucion($caso->institucion_id), 404);
        $datos = $request->validate(...self::reglasReapertura());

        return back()->with(...self::mensajeReapertura(
            $this->motor->reabrirCaso($caso, $request->user(), trim($datos['motivo'])),
            $caso
        ));
    }

    /** @return array{0: array<string,mixed>, 1: array<string,string>} */
    public static function reglasReapertura(): array
    {
        return [
            ['motivo' => ['required', 'string', 'min:10', 'max:1000']],
            [
                'motivo.required' => 'Indica por qué se reabre el caso: queda en la bitácora.',
                'motivo.min' => 'Describe el motivo con un poco más de detalle.',
            ],
        ];
    }

    /** @return array{0: string, 1: string} tipo de aviso y texto */
    public static function mensajeReapertura(string $resultado, EventoCrisis $caso): array
    {
        $codigo = ExpedienteClinicoService::codigoCaso($caso);

        return match ($resultado) {
            'reabierto' => ['success', "{$codigo} reabierto. Vuelve a la cola y sólo se cierra con contacto humano verificado."],
            'otro_abierto' => ['error', "La persona ya tiene otro caso abierto; atiéndelo ahí en lugar de reabrir {$codigo}."],
            'no_cerrado' => ['error', "{$codigo} no está cerrado."],
            default => ['error', 'Se requiere acreditación clínica.'],
        };
    }

    public function cerrar(Request $request, EventoCrisis $caso)
    {
        abort_unless($request->user()->isClinicoAcreditado(), 403, 'Se requiere acreditación clínica.');
        abort_unless($request->user()->puedeAtenderInstitucion($caso->institucion_id), 404);
        $datos = $request->validate([
            'notas' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'notas.required' => 'El cierre necesita la nota del contacto verificado.',
            'notas.min' => 'Describe el cierre con un poco más de detalle.',
        ]);

        if (!$this->motor->verificarCierreCaso($caso, $request->user(), trim($datos['notas']))) {
            return back()->with('error', $caso->contactado_en === null
                ? 'No se puede cerrar un caso sin contacto humano registrado.'
                : 'El caso no se pudo cerrar.');
        }

        return back()->with('success', ExpedienteClinicoService::codigoCaso($caso) . ' cerrado con contacto humano verificado.');
    }
}
