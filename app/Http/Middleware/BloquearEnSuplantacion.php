<?php

namespace App\Http\Middleware;

use App\Support\Suplantacion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lo que sólo decide la persona dueña de la cuenta (consentimiento, uso de sus
 * datos, eliminar la cuenta) no se hace desde «Entrar como».
 */
class BloquearEnSuplantacion
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Suplantacion::activa()) {
            $mensaje = 'Estás dentro de otra cuenta con «Entrar como». El consentimiento, el uso de datos y la eliminación de la cuenta sólo los decide su dueño.';

            if ($request->expectsJson()) {
                abort(403, $mensaje);
            }

            return back()->with('error', $mensaje);
        }

        return $next($request);
    }
}
