<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Solicitud de /contacto para el buzón corporativo: una empresa o institución
 * que pide una prueba piloto, un plan u otra cosa. «Responder a» es quien la
 * envió, para contestarle directo desde el correo.
 *
 * $datos viene de EnviarContactoRequest::datosContacto(): interes,
 * interes_texto, nombre, puesto, organizacion, sector, personas, email,
 * telefono y mensaje (los opcionales en null).
 */
class ContactoCorporativoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $datos,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address', 'hola@atulado.com.mx'), config('mail.from.name', 'A Tu Lado')),
            replyTo: [new Address($this->datos['email'], $this->datos['nombre'])],
            subject: 'Contacto corporativo · ' . $this->datos['interes_texto'] . ' · ' . $this->datos['organizacion'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contacto-corporativo',
            text: 'emails.contacto-corporativo-text',
            with: [
                'datos' => $this->datos,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
