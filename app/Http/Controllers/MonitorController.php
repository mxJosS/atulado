<?php

namespace App\Http\Controllers;

use App\Services\MonitorServidorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        ])->withHeaders(self::CABECERAS);
    }

    public function datos(Request $request, MonitorServidorService $monitor): JsonResponse
    {
        $this->soloAdministracion($request);

        return response()->json($monitor->muestra())->withHeaders(self::CABECERAS);
    }
}
