<?php

namespace App\Support;

use App\Models\BitacoraAdmin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * «Entrar como»: una cuenta principal de administración abre la sesión de
 * otra cuenta para probar la plataforma tal como la ve esa persona. La sesión
 * recuerda quién entró, para volver a su cuenta con un clic.
 *
 * Mientras dura no cuenta como uso real de la persona: no activa su lugar en
 * el padrón ni suma visitas a la analítica.
 */
class Suplantacion
{
    public const CLAVE = 'suplantacion';

    public static function activa(): bool
    {
        $request = request();

        return $request->hasSession() && $request->session()->has(self::CLAVE);
    }

    /** La cuenta principal que entró como otra persona, o null si no hay suplantación. */
    public static function adminOriginal(): ?User
    {
        return self::activa() ? User::find(session(self::CLAVE . '.admin_id')) : null;
    }

    public static function iniciar(User $admin, User $cuenta): void
    {
        BitacoraAdmin::registrar('suplantacion_inicio', $cuenta, "{$admin->email} entró como {$cuenta->email}.");

        // Antes de Auth::login, para que el evento Login ya la vea activa.
        session()->put(self::CLAVE, ['admin_id' => $admin->id]);
        Auth::login($cuenta);
        session()->regenerate();
    }

    /** Regresa a la cuenta principal. Devuelve esa cuenta, o null si ya no existe. */
    public static function terminar(): ?User
    {
        $cuenta = Auth::user();
        $admin = User::find(session(self::CLAVE . '.admin_id'));

        if ($admin) {
            // Todavía con la clave en sesión: el evento Login no la cuenta como entrada.
            Auth::login($admin);
            BitacoraAdmin::registrar('suplantacion_fin', $cuenta, "{$admin->email} regresó a su cuenta desde la de {$cuenta?->email}.");
        }

        session()->forget(self::CLAVE);
        session()->regenerate();

        return $admin;
    }
}
