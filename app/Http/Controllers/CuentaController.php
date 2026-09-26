<?php

namespace App\Http\Controllers;

use App\Services\EliminacionCuentaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * La persona elimina su propia cuenta desde el perfil (derecho de cancelación).
 */
class CuentaController extends Controller
{
    public function eliminar(Request $request, EliminacionCuentaService $servicio)
    {
        $user = $request->user();

        // La administración se da de baja desde el panel, para no dejar la
        // plataforma sin nadie que la opere.
        if ($user->is_admin) {
            return back()->with('error', 'Las cuentas de administración se eliminan desde el panel de Usuarios, por otra cuenta de administración.');
        }

        $reglas = ['confirmacion' => ['required', 'in:ELIMINAR']];
        // Quien creó su cuenta con Google no conoce una contraseña: le basta la confirmación.
        if (!$user->google_id) {
            $reglas['password_eliminar'] = ['required', 'current_password'];
        }
        $request->validateWithBag('eliminarCuenta', $reglas, [
            'confirmacion.*' => 'Escribe ELIMINAR en mayúsculas para confirmar.',
            'password_eliminar.required' => 'Escribe tu contraseña para confirmar.',
            'password_eliminar.current_password' => 'La contraseña no es correcta.',
        ]);

        // Con un caso de crisis abierto, alguien del equipo clínico tiene que
        // poder contactarte: primero se cierra el caso.
        if ($servicio->casosAbiertos($user) > 0) {
            return back()->with('error', 'Tienes un caso de acompañamiento abierto. Cuando el equipo clínico lo cierre podrás eliminar tu cuenta; si no quieres esperar, escríbenos a privacidad@atulado.com.mx.');
        }

        Auth::logout();
        $servicio->eliminar($user);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Tu cuenta y tu información se eliminaron. Gracias por haber estado aquí; si algún día lo necesitas, puedes volver a crear una cuenta.');
    }
}
