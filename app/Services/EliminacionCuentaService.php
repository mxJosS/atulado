<?php

namespace App\Services;

use App\Models\EventoCrisis;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Eliminación de una cuenta, la pida la persona o la administración.
 *
 * Se borran la cuenta, su historial, su foto y sus documentos. Los casos de
 * crisis y la bitácora de accesos se conservan sin identidad (user_id = null)
 * durante 5 años, como dice el aviso de privacidad.
 */
class EliminacionCuentaService
{
    public function casosAbiertos(User $user): int
    {
        return EventoCrisis::where('user_id', $user->id)->abiertos()->count();
    }

    public function eliminar(User $user): void
    {
        $archivos = $user->professionalVerifications()->pluck('document_path')->filter()->all();
        $avatar = $user->avatar && !str_starts_with($user->avatar, 'http') ? $user->avatar : null;

        DB::transaction(function () use ($user) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete();
        });

        // Los archivos, fuera de la transacción: si falla el borrado de un
        // archivo, la cuenta ya no existe y el archivo queda huérfano, no al revés.
        if ($archivos !== []) {
            Storage::disk('local')->delete($archivos);
            Storage::disk('public')->delete($archivos);
        }
        if ($avatar) {
            Storage::disk('public')->delete($avatar);
        }
    }
}
