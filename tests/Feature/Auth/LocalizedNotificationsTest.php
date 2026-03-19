<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizedNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_de_redefinicao_de_senha_usa_textos_em_portugues()
    {
        $user = User::factory()->create(['name' => 'Maria']);
        $mailMessage = (new ResetPassword('token-teste'))->toMail($user);

        $this->assertSame('Redefinicao de senha - '.config('app.name'), $mailMessage->subject);
        $this->assertSame('Redefinir senha', $mailMessage->actionText);
        $this->assertStringContainsString('Recebemos uma solicitacao', $mailMessage->introLines[0]);
    }

    public function test_email_de_verificacao_usa_textos_em_portugues()
    {
        $user = User::factory()->create(['name' => 'Maria']);
        $mailMessage = (new VerifyEmail)->toMail($user);

        $this->assertSame('Confirmacao de e-mail - '.config('app.name'), $mailMessage->subject);
        $this->assertSame('Confirmar e-mail', $mailMessage->actionText);
        $this->assertStringContainsString('Confirme seu endereco de e-mail', $mailMessage->introLines[0]);
    }
}
