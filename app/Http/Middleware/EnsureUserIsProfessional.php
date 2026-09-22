<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsProfessional
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para publicar un artículo.');
        }

        if (!$user->isProfessional()) {
            return redirect()->route('profile.show')
                ->with('error', 'Para publicar artículos en la revista científica debes ser un Profesional de la Salud verificado. Por favor, completa tu solicitud de acreditación al final de tu perfil.');
        }

        return $next($request);
    }
}
