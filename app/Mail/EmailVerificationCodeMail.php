<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Código de 6 dígitos por correo. Dos usos:
 *   verificacion  confirmar el correo al crear la cuenta
 *   acceso        administración y equipo clínico entrando desde un dispositivo,
 *                 navegador o red nuevos ($dispositivo e $ip dicen desde dónde)
 */
class EmailVerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $name,
        public string $proposito = 'verificacion',
        public ?string $dispositivo = null,
        public ?string $ip = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address', 'hola@atulado.com.mx'), config('mail.from.name', 'A Tu Lado')),
            subject: $this->proposito === 'acceso'
                ? 'Tu código para entrar a A Tu Lado: ' . $this->code
                : 'Tu código de verificación de A Tu Lado: ' . $this->code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
            text: 'emails.verification-code-text',
            with: [
                'code' => $this->code,
                'name' => $this->name,
                'acceso' => $this->proposito === 'acceso',
                'dispositivo' => $this->dispositivo,
                'ip' => $this->ip,
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
