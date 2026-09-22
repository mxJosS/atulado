<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CrearSuperAdmin extends Command
{
    protected $signature = 'atulado:superadmin
                            {email : Correo de la cuenta}
                            {--nombre= : Nombre visible}
                            {--password= : Contraseña; si se omite se genera una}
                            {--clinico : Otorga también el permiso clínico acreditado}';

    protected $description = 'Crea o promueve una cuenta a superadministrador de A Tu Lado';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("«{$email}» no es un correo válido.");

            return self::FAILURE;
        }

        $protegidos = config('atulado.superadmin_emails', []);
        if (!in_array($email, $protegidos, true)) {
            $this->warn("Aviso: {$email} no está en atulado.superadmin_emails.");
            $this->warn('Tendrá permisos de administrador, pero no la protección de cuenta principal.');
            $this->warn('Para dársela, añádelo a ATULADO_SUPERADMIN_EMAILS en el .env.');
        }

        $password = $this->option('password') ?: Str::password(16);
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill([
                'is_admin' => true,
                'role' => 'admin',
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            if ($this->option('clinico')) {
                $user->forceFill(['is_clinico_atulado' => true]);
            }

            if ($this->option('password')) {
                $user->forceFill(['password' => Hash::make($password)]);
                $this->info('Contraseña actualizada.');
            }

            $user->save();
            $this->info("Cuenta {$email} promovida a superadministrador.");

            return self::SUCCESS;
        }

        $user = new User();
        $user->forceFill([
            'name' => $this->option('nombre') ?: 'Administrador A tu lado',
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
            'role' => 'admin',
            'avatar_color' => 'dark',
            'email_verified_at' => now(),
        ]);

        if ($this->option('clinico')) {
            $user->forceFill(['is_clinico_atulado' => true]);
        }

        $user->save();

        $this->info("Cuenta {$email} creada como superadministrador.");

        if (!$this->option('password')) {
            $this->newLine();
            $this->warn("Contraseña generada: {$password}");
            $this->warn('Guárdala ahora: no vuelve a mostrarse.');
        }

        return self::SUCCESS;
    }
}
