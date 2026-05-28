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
 * O ClubInvitation originalmente implementava ShouldQueue, enfileirando
 * o envio no driver "database". Isso causava e-mails presos indefinidamente
 * quando nenhum worker (queue:work) estava ativo em produção.
 *
 * CORREÇÃO APLICADA: ShouldQueue foi removido do ClubInvitation.
 * O InvitationController chama Mail::to()->send() de forma SÍNCRONA,
 * garantindo que o e-mail seja disparado imediatamente na requisição.
 *
 * IMPACTO NOS TESTES:
 * - Mail::assertQueued()    → NÃO deve ser usado (nada vai para a fila)
 * - Mail::assertSent()      → CORRETO para o envio síncrono atual
 * - Mail::assertNothingQueued() → confirma que a fila NÃO é usada
 * -------------------------------------------------------------------
 */
class FilaConviteTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // CENÁRIO 1 — O e-mail é ENVIADO imediatamente (síncrono, sem fila)
    // -------------------------------------------------------------------------
    public function test_convite_e_enviado_sincronamente_sem_passar_pela_fila(): void
    {
        // 1. Intercepta todos os envios sem enviar de verdade
        Mail::fake();

        // 2. Prepara o clube e o usuário master
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $master = User::factory()->create([
            'club_id' => $clube->id,
            'role'    => 'master',
        ]);

        // 3. Dispara a criação do convite
        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'conselheiro@clube.com',
            'role'  => 'conselheiro',
        ]);

        // 4. Verifica HTTP e sessão
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('invites.index'));
        $response->assertSessionHas('success', 'Convite gerado e enviado com sucesso!');

        // 5. ClubInvitation NÃO implementa ShouldQueue → envio é síncrono
        //    assertSent() verifica o bucket de envios imediatos
        Mail::assertSent(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('conselheiro@clube.com');
        });

        // 6. Confirma explicitamente que NADA foi enfileirado
        //    (protege contra regressão caso ShouldQueue seja reativado sem querer)
        Mail::assertNothingQueued();
    }

    // -------------------------------------------------------------------------
    // CENÁRIO 2 — Apenas UM e-mail é disparado por convite
    // -------------------------------------------------------------------------
    public function test_apenas_um_email_e_disparado_por_convite(): void
    {
        Mail::fake();

        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $master = User::factory()->create([
            'club_id' => $clube->id,
            'role'    => 'master',
        ]);

        $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'instrutor@clube.com',
            'role'  => 'instrutor',
        ]);

        // assertSentCount garante idempotência: exatamente 1 e-mail, sem duplicatas
        Mail::assertSentCount(1);
        Mail::assertNothingQueued();
    }
}
