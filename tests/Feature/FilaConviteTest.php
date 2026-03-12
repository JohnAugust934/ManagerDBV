<?php

namespace Tests\Feature;

use App\Mail\ClubInvitation;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FilaConviteTest extends TestCase
{
    use RefreshDatabase;

    public function test_envio_de_convite_deve_ir_para_a_fila_em_background()
    {
        // 1. Intercepta os envios de e-mail para verificar a fila sem enviar de verdade
        Mail::fake();

        // 2. Prepara um usuário Master para enviar o convite
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create([
            'club_id' => $clube->id,
            'role' => 'master',
        ]);

        // 3. Executa a ação de criar o convite
        $response = $this->actingAs($user)->post(route('invites.store'), [
            'email' => 'conselheiro@clube.com',
            'role' => 'conselheiro',
        ]);

        // 4. Verifica se a requisição não gerou erros
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('invites.index'));

        // 5. GARANTE que o e-mail da classe ClubInvitation foi colocado na fila
        Mail::assertQueued(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('conselheiro@clube.com');
        });
    }
}
