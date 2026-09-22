<?php

namespace App\Http\Controllers;

use App\Models\EventoCrisis;
use App\Services\ClinicalEngineService;
use App\Services\PucholService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Bloques de preguntas que responde la persona usuaria.
 *
 * El navegador nunca recibe el nombre de los instrumentos, puntajes, niveles
 * ni rutas del motor: sólo cuál es el siguiente paso de la pantalla. Todo lo
 * demás se queda en el servidor y en el panel clínico.
 */
class AssessmentController extends Controller
{
    /** Clave de sesión donde el registro diario deja el origen del bloque 1. */
    public const SESION_ORIGEN = 'preguntas.origen_bloque_1';

    public function __construct(
        protected ClinicalEngineService $engine
    ) {}

    /**
     * Respuesta mínima para la pantalla.
     *
     * @param  'bloque_2'|'bloque_3'|'apoyo'|null  $siguiente
     */
    public static function respuesta(?string $siguiente, ?string $mensaje = null): JsonResponse
    {
        return response()->json(array_filter([
            'success' => true,
            'siguiente' => $siguiente,
            'mensaje' => $mensaje,
        ], fn ($v) => $v !== null));
    }

    /**
     * Bloque 1 (WHO-5).
     */
    public function submitWho5(Request $request)
    {
        $validated = $request->validate([
            'i1' => ['required', 'integer', 'between:0,5'],
            'i2' => ['required', 'integer', 'between:0,5'],
            'i3' => ['required', 'integer', 'between:0,5'],
            'i4' => ['required', 'integer', 'between:0,5'],
            'i5' => ['required', 'integer', 'between:0,5'],
        ]);

        // El origen lo decidió el motor al guardar el registro diario; no se acepta del navegador.
        $origen = $request->session()->pull(self::SESION_ORIGEN, 'programada');

        $resultado = $this->engine->procesarWHO5(Auth::user(), $validated, $origen);

        return $resultado['abrir_mdi']
            ? self::respuesta('bloque_2')
            : self::respuesta(null, 'Gracias por responder. Seguimos a tu lado.');
    }

    /**
     * Bloque 2 (MDI).
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
        ]);

        $resultado = $this->engine->procesarMDI(Auth::user(), $validated);

        // Estrés o sueño alto (ítems 3, 8 y 9): se adelanta la revisión mensual, a partir de mañana.
        $alto = (int) config('clinical.puchol.item_mdi_alto', 4);
        if (max($validated['i3'], $validated['i8a'], $validated['i8b'], $validated['i9']) >= $alto) {
            app(PucholService::class)->adelantar(Auth::user());
        }

        return match (true) {
            $resultado['abrir_asq'] => self::respuesta('bloque_3'),
            $resultado['nivel'] === 'ROJO' => self::respuesta('apoyo'),
            default => self::respuesta(null, 'Gracias por responder. Ajustamos tus sugerencias de bienestar.'),
        };
    }

    /**
     * Bloque 3 (ASQ).
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

        // La pregunta 5 sólo aplica si alguna de las cuatro primeras fue positiva.
        // Lo decide el servidor: el navegador no conoce la regla.
        $positiva = fn ($r) => in_array($r, ['si', 'prefiero_no_contestar'], true);
        $algunaPositiva = $positiva($validated['p1']) || $positiva($validated['p2'])
            || $positiva($validated['p3']) || $positiva($validated['p4']);

        if ($algunaPositiva && empty($validated['p5'])) {
            return self::respuesta('pregunta_extra');
        }
        if (!$algunaPositiva) {
            $validated['p5'] = null;
        }

        $resultado = $this->engine->procesarASQ(
            Auth::user(),
            $validated,
            $validated['metodo'] ?? null,
            $validated['fecha_intento'] ?? null
        );

        return $resultado['nivel'] !== null
            ? self::respuesta('apoyo')
            : self::respuesta(null, 'Gracias por tu honestidad. Cuentas con nosotros en todo momento.');
    }

    /**
     * Bloque 4 (revisión mensual repartida). La persona lo responde tras el
     * registro diario o desde su plan de seguridad.
     */
    public function submitRevision(Request $request, PucholService $revision)
    {
        $letra = (string) $request->input('bloque');
        abort_unless(isset(PucholService::BLOQUES[$letra]), 422, 'Bloque no válido.');

        $reglas = ['bloque' => ['required', 'in:A,B,C,D']];
        foreach (PucholService::BLOQUES[$letra] as $item) {
            $reglas[$item] = ['required', 'integer', 'between:0,4'];
        }
        $validated = $request->validate($reglas);

        $siguiente = $revision->responder(Auth::user(), $letra, $validated);

        return $siguiente === 'apoyo'
            ? self::respuesta('apoyo')
            : self::respuesta(null, 'Gracias por responder. Seguimos a tu lado.');
    }

    /**
     * ¿Hay que mostrar el mensaje de apoyo mientras la persona escribe?
     *
     * La lista de términos vive sólo en el servidor (config/clinical.php);
     * el navegador recibe un sí o un no. El texto no se guarda aquí: se
     * guarda, como siempre, al enviar el registro del día.
     */
    public function revisarTexto(Request $request): JsonResponse
    {
        $validated = $request->validate(['texto' => ['nullable', 'string', 'max:5000']]);

        return response()->json([
            'apoyo' => $this->engine->analizarTextoLibre($validated['texto'] ?? null)['bandera_lexica'],
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
        if (!$profesional->isClinicoAcreditado()) {
            return response()->json(['error' => 'No autorizado. Solo un profesional acreditado puede verificar el cierre.'], 403);
        }

        if ($evento->contactado_en === null) {
            return response()->json([
                'success' => false,
                'error' => 'Antes de cerrar hay que registrar el contacto humano con la persona.',
            ], 422);
        }

        $exito = $this->engine->verificarCierreCaso($evento, $profesional, $validated['notas_cierre']);

        return response()->json([
            'success' => $exito,
            'message' => $exito
                ? 'El caso ha sido verificado y cerrado con éxito.'
                : 'No fue posible cerrar el caso.',
        ], $exito ? 200 : 422);
    }
}
