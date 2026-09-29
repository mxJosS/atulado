<?php

namespace App\Http\Middleware;

use App\Support\Suplantacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Nadie usa el espacio personal (registro diario, cuestionarios, plan de
 * seguridad…) sin haber dado su consentimiento expreso para el tratamiento de
 * datos sensibles. Quien llegó por Google, por alta de administración o tenía
 * cuenta antes de que se pidiera, lo da una vez en /consentimiento.
 *
 * Con «Entrar como» la cuenta principal pasa para probar la plataforma, pero
 * el consentimiento no se registra: sólo lo da la persona dueña de la cuenta.
 */
class EnsureConsentimiento
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Suplantacion::activa()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && !$user->tieneConsentimiento()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Antes de continuar necesitas aceptar el aviso de privacidad.'], 403);
            }
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }
            // Los avisos de la redirección anterior (p. ej. «ya formas parte de…») llegan a la siguiente pantalla.
            $request->session()->reflash();

            return redirect()->route('consentimiento.show');
        }

        return $next($request);
    }
}
