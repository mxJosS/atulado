<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProbarCorreo extends Command
{
    protected $signature = 'atulado:probar-correo {email : A dónde mandar el correo de prueba}';

    protected $description = 'Envía un correo de prueba con la configuración actual para confirmar que el SMTP funciona';

    public function handle(): int
    {
        $email = $this->argument('email');
        $config = config('mail.mailers.' . config('mail.default'));

        $this->line('Transporte: ' . config('mail.default'));
        $this->line('Servidor:   ' . ($config['host'] ?? '—') . ':' . ($config['port'] ?? '—') . ' (' . ($config['scheme'] ?? 'sin esquema') . ')');
        $this->line('Remitente:  ' . config('mail.from.name') . ' <' . config('mail.from.address') . '>');

        try {
            Mail::raw(
                "Este es un correo de prueba de A Tu Lado.\n\nSi lo recibiste, la configuración de correo funciona.\n\n" . now()->format('d/m/Y H:i'),
                fn ($m) => $m->to($email)->subject('Prueba de correo · A Tu Lado')
            );
        } catch (Throwable $e) {
            $this->error('No se pudo enviar: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Enviado a {$email}. Revisa también la carpeta de spam.");

        return self::SUCCESS;
    }
}
