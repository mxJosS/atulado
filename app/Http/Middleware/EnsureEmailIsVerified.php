<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_admin && !$user->hasVerifiedEmail()) {
            // Allow access to verification routes and logout
            if ($request->routeIs('verification.code.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                abort(403, 'Tu dirección de correo electrónico no ha sido verificada.');
            }

            return redirect()->route('verification.code.notice')
                ->with('info', 'Por favor confirma el código de 6 dígitos que enviamos a tu correo para activar tu cuenta.');
        }

        return $next($request);
    }
}
