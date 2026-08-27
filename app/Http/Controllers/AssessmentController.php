<?php

namespace App\Http\Controllers;

use App\Models\EventoCrisis;
use App\Services\ClinicalEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentController extends Controller
{
    public function __construct(
        protected ClinicalEngineService $engine
    ) {}

    /**
     * Procesar respuestas de la Capa 1: WHO-5
     */
    public function submitWho5(Request $request)
    {
        $validated = $request->validate([
            'i1' => ['required', 'integer', 'between:0,5'],
            'i2' => ['required', 'integer', 'between:0,5'],
            'i3' => ['required', 'integer', 'between:0,5'],
            'i4' => ['required', 'integer', 'between:0,5'],
            'i5' => ['required', 'integer', 'between:0,5'],
            'origen' => ['nullable', 'string', 'in:programada,adelantada,ruta_b'],
        ]);

        $user = Auth::user();
        $origen = $validated['origen'] ?? 'programada';

        $resultado = $this->engine->procesarWHO5($user, $validated, $origen);

        return response()->json([
            'success' => true,
            'resultado' => $resultado,
        ]);
    }

    /**
     * Procesar respuestas de la Capa 2: MDI
     */
    public function submitMdi(Request $request)
    {
        $validated = $request->validate([
            'i1' => ['required', 'integer', 'between:0,5'],
            'i2' => ['required', 'integer', 'between:0,5'],
            'i3' => ['required', 'integer', 'between:0,5'],
            'i4' => ['required', 'integer', 'between:0,5'],
            'i5' => ['required', 'integer', 'between:0,5'],
            'i6' => ['required', 'integer', 'between:0,5'],
            'i7' => ['required', 'integer', 'between:0,5'],
            'i8a' => ['required', 'integer', 'between:0,5'],
            'i8b' => ['required', 'integer', 'between:0,5'],
            'i9' => ['required', 'integer', 'between:0,5'],
            'i10a' => ['required', 'integer', 'between:0,5'],
            'i10b' => ['required', 'integer', 'between:0,5'],
            'origen' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $resultado = $this->engine->procesarMDI($user, $validated, $validated['origen'] ?? null);

        return response()->json([
            'success' => true,
            'resultado' => $resultado,
        ]);
    }

    /**
     * Procesar respuestas de la Capa 3: ASQ
     */
    public function submitAsq(Request $request)
    {
        $validated = $request->validate([
            'p1' => ['required', 'string', 'in:si,no,prefiero_no_contestar'],
            'p2' => ['required', 'string', 'in:si,no,prefiero_no_contestar'],
            'p3' => ['required', 'string', 'in:si,no,prefiero_no_contestar'],
            'p4' => ['required', 'string', 'in:si,no,prefiero_no_contestar'],
            'p5' => ['nullable', 'string', 'in:si,no,prefiero_no_contestar'],
            'metodo' => ['nullable', 'string', 'max:500'],
            'fecha_intento' => ['nullable', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        $resultado = $this->engine->procesarASQ(
            $user,
            $validated,
            $validated['metodo'] ?? null,
            $validated['fecha_intento'] ?? null
        );

        return response()->json([
            'success' => true,
            'resultado' => $resultado,
        ]);
    }

    /**
     * Registrar acción en pantalla de contención de crisis
     */
    public function registrarAccionCrisis(Request $request)
    {
        $validated = $request->validate([
            'tipo_accion' => ['required', 'string', 'in:estoy_con_alguien,salida_sin_contacto,llamar_linea,llamar_contacto,ver_recursos'],
            'evento_id' => ['nullable', 'integer', 'exists:eventos_crisis,id'],
        ]);

        $user = Auth::user();
        $evento = $validated['evento_id'] 
            ? EventoCrisis::where('id', $validated['evento_id'])->where('user_id', $user->id)->first()
            : $user->active_crisis_event;

        if ($evento) {
            if ($validated['tipo_accion'] === 'estoy_con_alguien') {
                $this->engine->registrarEstoyConAlguien($evento);
            } elseif ($validated['tipo_accion'] === 'salida_sin_contacto') {
                $this->engine->registrarSalidaSinContacto($evento);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Evento registrado correctamente.',
        ]);
    }

    /**
     * Cerrar caso de crisis (exclusivo para profesional con contacto humano verificado)
     */
    public function cerrarCasoCrisis(Request $request, EventoCrisis $evento)
    {
        $validated = $request->validate([
            'notas_cierre' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $profesional = Auth::user();
        if (!$profesional->isProfessional()) {
            return response()->json(['error' => 'No autorizado. Solo un profesional de la salud puede verificar el cierre.'], 403);
        }

        $exito = $this->engine->verificarCierreCaso($evento, $profesional, $validated['notas_cierre']);

        return response()->json([
            'success' => $exito,
            'message' => 'El caso ha sido verificado y cerrado con éxito.',
        ]);
    }
}
