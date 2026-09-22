<?php

namespace App\Services;

use App\Mail\InvitacionPadronMail;
use App\Models\Membresia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Invitaciones por correo a las personas del padrón.
 *
 * El enlace va firmado, vence en DIAS_VIGENCIA días y lleva una huella de la
 * contraseña actual: en cuanto la persona define la suya, el enlace deja de
 * servir. Reenviar genera un enlace nuevo.
 */
class InvitacionService
{
    public const DIAS_VIGENCIA = 14;

    /** Envíos por segundo: el plan de Resend admite 2. */
    private const POR_SEGUNDO = 2;

    public function url(Membresia $membresia): string
    {
        return URL::temporarySignedRoute('invitacion.mostrar', now()->addDays(self::DIAS_VIGENCIA), [
            'membresia' => $membresia->id,
            'h' => self::huella($membresia->user->password),
        ]);
    }

    public static function huella(string $password): string
    {
        return substr(hash('sha256', $password . config('app.key')), 0, 20);
    }

    /** Quien ya activó su cuenta o está dado de baja no necesita invitación. */
    public static function invitable(Membresia $m): bool
    {
        return in_array($m->estado, ['invitado', 'suspendido'], true) && $m->user?->email;
    }

    /**
     * Encola un correo por persona, espaciados para no pasar el límite del
     * proveedor. Devuelve cuántos se encolaron.
     *
     * @param  Collection<int,Membresia>  $membresias
     */
    public function enviar(Collection $membresias): int
    {
        $enviadas = 0;

        foreach ($membresias->filter(fn ($m) => self::invitable($m))->values() as $i => $membresia) {
            Mail::to($membresia->user->email)->later(
                now()->addSeconds(intdiv($i, self::POR_SEGUNDO)),
                new InvitacionPadronMail($membresia, $this->url($membresia))
            );

            $membresia->forceFill(['invitado_en' => now()])->save();
            $enviadas++;
        }

        return $enviadas;
    }
}
