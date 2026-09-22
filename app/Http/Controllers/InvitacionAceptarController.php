<?php

namespace App\Http\Controllers;

use App\Models\Membresia;
use App\Services\InvitacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

/**
 * La persona del padrón abre el enlace de su correo y define su contraseña.
 * El enlace va firmado (middleware 'signed'); además se comprueba la huella
 * de la contraseña para que sirva una sola vez.
 */
class InvitacionAceptarController extends Controller
{
    private function vigente(Request $request, Membresia $membresia): bool
    {
        return $membresia->user
            && in_array($membresia->estado, ['invitado', 'suspendido'], true)
            && hash_equals(InvitacionService::huella($membresia->user->password), (string) $request->query('h'));
    }

    public function mostrar(Request $request, Membresia $membresia)
    {
        $membresia->load('user', 'institucion');

        return view('auth.invitacion', [
            'membresia' => $membresia,
            'vigente' => $this->vigente($request, $membresia),
        ]);
    }

    public function aceptar(Request $request, Membresia $membresia)
    {
        $membresia->load('user');

        if (!$this->vigente($request, $membresia)) {
            return redirect()->route('login')->with('error', 'Esta invitación ya se usó o no es válida. Si ya creaste tu contraseña, inicia sesión.');
        }

        $datos = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
            'acepto' => ['accepted'],
        ], [
            'password.required' => 'Elige una contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'acepto.accepted' => 'Para continuar necesitas aceptar el aviso de privacidad y los términos.',
        ]);

        DB::transaction(function () use ($membresia, $datos) {
            $membresia->user->forceFill([
                'password' => $datos['password'],
                'email_verified_at' => $membresia->user->email_verified_at ?? now(),
            ])->save();

            $membresia->forceFill(['estado' => 'activo', 'activado_en' => now()])->save();
        });

        Auth::login($membresia->user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Tu cuenta está lista. Bienvenida, bienvenido a A Tu Lado.');
    }
}
