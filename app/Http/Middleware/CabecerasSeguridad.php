<?php

namespace App\Http\Middleware;

use App\Support\AvisoErrores;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad en todas las respuestas web.
 *
 * La CSP permite 'unsafe-inline' porque las vistas usan scripts, estilos y
 * onclick en línea; aun así limita de dónde se cargan scripts, estilos,
 * fuentes y conexiones, prohíbe incrustar la app en sitios ajenos y bloquea
 * plugins. Si algo deja de cargar en producción, ATULADO_CSP=report la pasa a
 * sólo-reporte (y =off la quita) sin tocar código.
 *
 * Una cabecera que el controlador ya puso (p. ej. el monitor o el resumen
 * clínico, más estrictos) se respeta.
 */
class CabecerasSeguridad
{
    private const CSP = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://kit.fontawesome.com https://cdn.jsdelivr.net",
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://ka-f.fontawesome.com https://ka-p.fontawesome.com",
        "font-src 'self' data: https://fonts.gstatic.com https://ka-f.fontawesome.com https://ka-p.fontawesome.com",
        "img-src 'self' data: blob: https:",
        "connect-src 'self' https://ka-f.fontawesome.com https://ka-p.fontawesome.com",
        "media-src 'self'",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "frame-ancestors 'self'",
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $h = $response->headers;

        $poner = function (string $nombre, string $valor) use ($h): void {
            if (!$h->has($nombre)) {
                $h->set($nombre, $valor);
            }
        };

        $poner('X-Content-Type-Options', 'nosniff');
        $poner('X-Frame-Options', 'SAMEORIGIN');
        $poner('Referrer-Policy', 'strict-origin-when-cross-origin');
        $poner('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');
        $poner('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        if ($request->isSecure()) {
            $poner('Strict-Transport-Security', 'max-age=31536000');
        }

        $modo = config('atulado.csp', 'enforce');
        if ($modo !== 'off') {
            $poner($modo === 'report' ? 'Content-Security-Policy-Report-Only' : 'Content-Security-Policy', implode('; ', self::CSP));
        }

        $h->remove('X-Powered-By');

        if (in_array($response->getStatusCode(), [401, 403, 419, 429], true)) {
            AvisoErrores::contarRechazo($response->getStatusCode(), (string) $request->ip(), $request->method() . ' ' . $request->path());
        }

        return $response;
    }
}
