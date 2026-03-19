<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // <-- IMPORTAÇÃO NECESSÁRIA
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// A adição do "implements ShouldQueue" é o que faz a mágica acontecer
class ClubInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invitation;

    public $url;

    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
        // Gera o link completo com o token para o usuário clicar
        $this->url = route('register.invite', ['token' => $invitation->token]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convite para acessar o '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.club-invitation',
        );
    }
}
