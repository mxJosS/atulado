<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panel compartido: lo usan la administración y el personal clínico
 * acreditado. Lo que es sólo de administración sigue detrás de 'admin'.
 */
class EnsureUserCanUsePanel
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->puedeUsarPanel()) {
            if ($request->expectsJson()) {
                abort(403, 'Acceso denegado.');
            }

            return redirect()->route('dashboard')
                ->with('error', 'Acceso restringido: esta sección es para administración o personal clínico acreditado.');
        }

        return $next($request);
    }
}
