<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea la cuenta administradora principal si no existe.
     *
     * Usa firstOrCreate a propósito: con updateOrCreate, cada despliegue
     * reescribía la contraseña y descartaba la que el equipo hubiera cambiado.
     */
    public function run(): void
    {
        $email = strtolower(trim((string) env('ATULADO_ADMIN_EMAIL', 'admin@atulado.com.mx')));
        $password = (string) env('ATULADO_ADMIN_PASSWORD', '');

        if ($password === '') {
            $password = Str::password(16);
            $this->command?->warn("Contraseña generada para {$email}: {$password}");
            $this->command?->warn('Guárdala ahora: no vuelve a mostrarse. Defínela en ATULADO_ADMIN_PASSWORD para fijarla.');
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => env('ATULADO_ADMIN_NAME', 'Administrador A tu lado'),
                'password' => Hash::make($password),
                'is_admin' => true,
                'role' => 'admin',
                'avatar_color' => 'dark',
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->command?->info("Cuenta administradora creada: {$email}");
        } else {
            $this->command?->info("La cuenta {$email} ya existía; no se modificó.");
        }
    }
}
