<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_admin) {
            if ($request->expectsJson()) {
                abort(403, 'Acceso denegado. No tienes permisos para ingresar al área administrativa.');
            }
            return redirect()->route('dashboard')
                ->with('error', 'Acceso restringido: Esta sección requiere permisos administrativos.');
        }

        return $next($request);
    }
}
