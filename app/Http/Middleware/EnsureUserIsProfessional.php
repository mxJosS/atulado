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
                ->with('error', 'Debes iniciar sesi?n para publicar un art?culo.');
        }

        if (!$user->isProfessional()) {
            return redirect()->route('profile.show')
                ->with('error', 'Para publicar art?culos en la revista cient?fica debes ser un Profesional de la Salud verificado. Por favor, completa tu solicitud de acreditaci?n al final de tu perfil.');
        }

        return $next($request);
    }
}
