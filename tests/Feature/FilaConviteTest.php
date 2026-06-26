<?php

namespace Tests\Feature;

use App\Mail\ClubInvitation;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * FilaConviteTest
 *
 * DECISÃO ARQUITETURAL (registrada aqui para rastreabilidade):
 * -------------------------------------------------------------------
 * O InvitationController chama Mail::to()->queue() de forma ASSÍNCRONA.
 * O e-mail é enfileirado no driver "database" e processado pelo worker.
 * Isso evita que o SMTP bloqueie o request e segure conexão com o banco.
 *
 * IMPACTO NOS TESTES:
 * - Mail::assertQueued()     → CORRETO para o envio assíncrono atual
 * - Mail::assertSent()       → NÃO deve ser usado (nada é enviado no request)
 * - Mail::assertNothingSent() → confirma que o SMTP NÃO é chamado no request
 * -------------------------------------------------------------------
 */
class FilaConviteTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // CENÁRIO 1 — O e-mail é ENFILEIRADO (assíncrono, sem bloquear o request)
    // -------------------------------------------------------------------------
    public function test_convite_e_enviado_sincronamente_sem_passar_pela_fila(): void
    {
        Mail::fake();

        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $master = User::factory()->create([
            'club_id' => $clube->id,
            'role' => 'master',
        ]);

        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'conselheiro@clube.com',
            'role' => 'conselheiro',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('invites.index'));
        $response->assertSessionHas('success', 'Convite gerado e enviado com sucesso!');

        // Mail::queue() → assertQueued verifica o bucket da fila
        Mail::assertQueued(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('conselheiro@clube.com');
        });

        // SMTP não é chamado no request — nada enviado de forma síncrona
        Mail::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // CENÁRIO 2 — Apenas UM e-mail é enfileirado por convite
    // -------------------------------------------------------------------------
    public function test_apenas_um_email_e_disparado_por_convite(): void
    {
        Mail::fake();

        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $master = User::factory()->create([
            'club_id' => $clube->id,
            'role' => 'master',
        ]);

        $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'instrutor@clube.com',
            'role' => 'instrutor',
        ]);

        // Exatamente 1 e-mail enfileirado, sem duplicatas
        Mail::assertQueuedCount(1);
        Mail::assertNothingSent();
    }
}
