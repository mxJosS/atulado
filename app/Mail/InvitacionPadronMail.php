<?php

namespace App\Mail;

use App\Models\Membresia;
use App\Services\InvitacionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Invitación a activar la cuenta. Va por la cola: un envío masivo de cientos
 * de correos no puede hacerse dentro de la petición web.
 */
class InvitacionPadronMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Membresia $membresia,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address', 'hola@atulado.com.mx'), config('mail.from.name', 'A Tu Lado')),
            subject: $this->membresia->institucion->nombre_corto . ' te invita a A Tu Lado',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitacion',
            text: 'emails.invitacion-text',
            with: [
                'nombre' => $this->membresia->user->name,
                'institucion' => $this->membresia->institucion->nombre_corto,
                'url' => $this->url,
                'dias' => InvitacionService::DIAS_VIGENCIA,
            ],
        );
    }
}
