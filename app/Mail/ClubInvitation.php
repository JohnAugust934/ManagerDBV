<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClubInvitation extends Mailable
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
            subject: 'Convite para se juntar ao DBV Manager',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.club-invitation',
        );
    }
}
