<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Aviso interno de error en producción. Se envía en el acto, sin cola. */
class AvisoErrorMail extends Mailable
{
    public function __construct(
        public string $titulo,
        public string $cuerpo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[A Tu Lado] Error en producción: ' . $this->titulo);
    }

    public function content(): Content
    {
        return new Content(htmlString: '<pre style="font: 13px/1.5 monospace; white-space: pre-wrap;">' . e($this->cuerpo) . '</pre>');
    }
}
