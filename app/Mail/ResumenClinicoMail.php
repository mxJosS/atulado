<?php

namespace App\Mail;

use App\Models\EntregaResumen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso al profesional de que tiene un resumen clínico. El correo lleva sólo
 * el enlace (que vence): ningún dato clínico ni el nombre de la persona.
 */
class ResumenClinicoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public EntregaResumen $entrega,
        public string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address', 'hola@atulado.com.mx'), config('mail.from.name', 'A Tu Lado')),
            subject: 'Resumen clínico confidencial ' . $this->entrega->folio,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.resumen-clinico',
            text: 'emails.resumen-clinico-text',
            with: [
                'destinatario' => $this->entrega->destinatario_nombre,
                'folio' => $this->entrega->folio,
                'url' => $this->url,
                'vence' => $this->entrega->vence_en->format('d/m/Y H:i'),
            ],
        );
    }
}
