<?php

namespace App\Mail;

use App\Models\Comunicado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComunicadoResponsavel extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Comunicado $comunicado,
        public readonly string $nomeDesbravador,
        public readonly string $nomeClube,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[{$this->nomeClube}] {$this->comunicado->titulo}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comunicado-responsavel');
    }
}
