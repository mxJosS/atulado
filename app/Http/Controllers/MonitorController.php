<?php

namespace App\Http\Controllers;

use App\Models\BitacoraAdmin;
use App\Services\MonitorServidorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Validator;

/**
 * Monitor del servidor en tiempo real. Sólo se registra si existe
 * ATULADO_MONITOR_RUTA y sólo lo ve quien escribe la ruta exacta y además
 * es administrador de A Tu Lado. No hay enlaces a él en la web, no se
 * indexa y no se guarda en caché.
 */
class MonitorController extends Controller
{
    private const CABECERAS = [
        'Cache-Control' => 'no-store, private',
        'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        'Referrer-Policy' => 'no-referrer',
        'X-Frame-Options' => 'DENY',
    ];

    /**
     * Quien no es administrador recibe 404, no 403: para esa persona el
     * monitor no existe.
     */
    private function soloAdministracion(Request $request): void
    {
        abort_unless((bool) $request->user()?->is_admin, 404);
    }

    public function panel(Request $request)
    {
        $this->soloAdministracion($request);

        return response()->view('monitor.panel', [
            'rutaDatos' => '/' . config('atulado.monitor.ruta') . '/datos',
            'rutaReinicio' => '/' . config('atulado.monitor.ruta') . '/reiniciar',
        ])->withHeaders(self::CABECERAS);
    }

    public function datos(Request $request, MonitorServidorService $monitor): JsonResponse
    {
        $this->soloAdministracion($request);

        return response()->json($monitor->muestra())->withHeaders(self::CABECERAS);
    }

    /**
     * Reinicia los servicios (MySQL, PHP-FPM, Nginx y la cola) o la máquina
     * completa. Pide la contraseña de la cuenta y escribir «REINICIAR». El
     * trabajo lo hace deploy/atulado-reiniciar.sh con sudo; la app sólo puede
     * ejecutar ese script.
     */
    public function reiniciar(Request $request): JsonResponse
    {
        $this->soloAdministracion($request);

        $validador = Validator::make($request->all(), [
            'tipo' => ['required', 'in:servicios,maquina'],
            'password' => ['required', 'current_password'],
            'confirmacion' => ['required', 'in:REINICIAR'],
        ], [
            'tipo.*' => 'Elige qué reiniciar.',
            'password.required' => 'Escribe tu contraseña.',
            'password.current_password' => 'La contraseña no es correcta.',
            'confirmacion.*' => 'Escribe REINICIAR en mayúsculas para confirmar.',
        ]);
        if ($validador->fails()) {
            return response()->json(['ok' => false, 'mensaje' => $validador->errors()->first()], 422)
                ->withHeaders(self::CABECERAS);
        }

        $tipo = $request->input('tipo');
        $script = (string) config('atulado.monitor.reinicio');
        $que = $tipo === 'maquina' ? 'el servidor completo' : 'los servicios';

        Log::warning("Monitor: {$request->user()->email} (id {$request->user()->id}) pidió reiniciar {$que} desde {$request->ip()}.");

        BitacoraAdmin::registrar('servidor_reinicio', null, "Reinicio de {$que} solicitado desde el monitor.");
        $resultado = Process::timeout(20)->run(['sudo', '-n', $script, $tipo]);

        if (!$resultado->successful()) {
            Log::error("Monitor: el reinicio de {$que} falló: " . trim($resultado->errorOutput() ?: $resultado->output()));

            return response()->json([
                'ok' => false,
                'mensaje' => 'El servidor no permitió el reinicio. Falta instalar el script y la regla de sudo: ver deploy/atulado-reiniciar.sh.',
            ], 500)->withHeaders(self::CABECERAS);
        }

        return response()->json([
            'ok' => true,
            'tipo' => $tipo,
            'mensaje' => $tipo === 'maquina'
                ? 'El servidor se reinicia en 5 segundos. La página vuelve sola en uno o dos minutos.'
                : 'Los servicios se reinician en 5 segundos. La página vuelve sola en unos segundos.',
        ])->withHeaders(self::CABECERAS);
    }
}
