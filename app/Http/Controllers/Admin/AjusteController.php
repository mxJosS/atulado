<?php

namespace App\Http\Controllers\Admin;

use App\Models\BitacoraAdmin;
use App\Http\Controllers\Controller;
use App\Models\Ajuste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Interruptores de la plataforma que la administración cambia en caliente.
 */
class AjusteController extends Controller
{
    /**
     * Activa o desactiva el acceso con Google. Desactivado, el botón desaparece
     * del login y del registro para todos y la ruta de Google deja de servir:
     * sirve para que un salón se registre sólo con su correo institucional.
     */
    public function google(Request $request)
    {
        $habilitado = $request->boolean('habilitado');

        Ajuste::guardar(Ajuste::GOOGLE_HABILITADO, $habilitado ? '1' : '0', $request->user());
        BitacoraAdmin::registrar('ajuste_google', null, 'Acceso con Google ' . ($habilitado ? 'activado' : 'desactivado') . '.');
        Log::info('Acceso con Google ' . ($habilitado ? 'activado' : 'desactivado') . '.', ['por' => $request->user()->id]);

        return back()->with('success', $habilitado
            ? 'Acceso con Google activado: el botón vuelve a aparecer en el inicio de sesión y el registro.'
            : 'Acceso con Google desactivado: ahora sólo se puede entrar y registrarse con correo.');
    }
}
