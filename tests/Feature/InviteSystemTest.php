<?php

namespace Tests\Feature;

use App\Mail\ClubInvitation;
use App\Models\Club;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InviteSystemTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // CENÁRIO 1 — Fluxo feliz: master cria convite, e-mail é disparado
    // -------------------------------------------------------------------------
    // ClubInvitation NÃO implementa mais ShouldQueue, portanto o envio é
    // SÍNCRONO. A asserção correta é assertSent(), não assertQueued().
    // -------------------------------------------------------------------------
    public function test_master_pode_criar_convite_e_envia_email(): void
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);
        $master = User::factory()->create(['role' => 'master', 'club_id' => $club->id]);

        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'novo@clube.com',
            'role'  => 'conselheiro',
        ]);

        $response->assertRedirect(route('invites.index'));
        $response->assertSessionHas('success', 'Convite gerado e enviado com sucesso!');

        $this->assertDatabaseHas('invitations', [
            'email'   => 'novo@clube.com',
            'role'    => 'conselheiro',
            'club_id' => $club->id,
        ]);

        // Envio síncrono → assertSent (não assertQueued)
        Mail::assertSent(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('novo@clube.com');
        });
    }

    // -------------------------------------------------------------------------
    // CENÁRIO 2 — Usuário se registra usando um convite válido
    // -------------------------------------------------------------------------
    public function test_usuario_pode_se_registrar_com_convite(): void
    {
        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);

        Invitation::create([
            'email'      => 'convidado@clube.com',
            'token'      => 'token-falso-123',
            'role'       => 'conselheiro',
            'club_id'    => $club->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token'                 => 'token-falso-123',
            'name'                  => 'Usuário Convidado',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email'   => 'convidado@clube.com',
            'role'    => 'conselheiro',
            'club_id' => $club->id,
        ]);

        $inviteUsado = Invitation::where('email', 'convidado@clube.com')->first();
        $this->assertNotNull($inviteUsado->registered_at);
    }

    // -------------------------------------------------------------------------
    // CENÁRIO 3 — Reaproveitamento: convite pendente é atualizado, não duplicado
    // -------------------------------------------------------------------------
    public function test_master_reaproveita_convite_pendente_sem_duplicar_registro(): void
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);
        $master = User::factory()->create(['role' => 'master', 'club_id' => $club->id]);

        $conviteExistente = Invitation::create([
            'email'      => 'pendente@clube.com',
            'token'      => 'token-antigo',
            'role'       => 'conselheiro',
            'club_id'    => $club->id,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'pendente@clube.com',
            'role'  => 'tesoureiro',
        ]);

        $response->assertRedirect(route('invites.index'));
        $response->assertSessionHas('success', 'Convite pendente atualizado e reenviado com sucesso!');

        // Apenas um registro no banco — sem duplicata
        $this->assertDatabaseCount('invitations', 1);

        $conviteAtualizado = $conviteExistente->fresh();
        $this->assertEquals('tesoureiro', $conviteAtualizado->role);
        $this->assertNotEquals('token-antigo', $conviteAtualizado->token);

        // Envio síncrono → assertSent (não assertQueued)
        Mail::assertSent(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('pendente@clube.com');
        });
    }

    // -------------------------------------------------------------------------
    // CENÁRIO 4 — Convite já utilizado não pode ser recriado
    // -------------------------------------------------------------------------
    public function test_nao_recria_convite_ja_utilizado(): void
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);
        $master = User::factory()->create(['role' => 'master', 'club_id' => $club->id]);

        Invitation::create([
            'email'         => 'usado@clube.com',
            'token'         => 'token-usado',
            'role'          => 'conselheiro',
            'club_id'       => $club->id,
            'expires_at'    => now()->addDay(),
            'registered_at' => now(),
        ]);

        $response = $this->actingAs($master)
            ->from(route('invites.create'))
            ->post(route('invites.store'), [
                'email' => 'usado@clube.com',
                'role'  => 'conselheiro',
            ]);

        $response->assertRedirect(route('invites.create'));
        $response->assertSessionHas('error', 'Este convite já foi utilizado. Como o e-mail não pode ser reutilizado, faça o gerenciamento diretamente no cadastro de usuários.');

        $this->assertDatabaseCount('invitations', 1);

        // Envio síncrono → assertNothingSent (não assertNothingQueued)
        Mail::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // CENÁRIO 5 — Usuário sem permissão 'master' não pode convidar role 'master'
    // -------------------------------------------------------------------------
    // O Gate 'gestao-acessos' chama $user->temPermissao('gestao_acessos').
    // Para o diretor ter esse acesso, extra_permissions deve conter a string
    // 'gestao_acessos' (underscore), não 'gestao-acessos' (hífen).
    // -------------------------------------------------------------------------
    public function test_usuario_com_gestao_acessos_nao_pode_convidar_master(): void
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'Sao Paulo', 'associacao' => 'APL']);
        $diretor = User::factory()->create([
            'role'              => 'diretor',
            'club_id'           => $club->id,
            'extra_permissions' => ['gestao_acessos'], // underscore: chave correta do Gate
        ]);

        $response = $this->actingAs($diretor)
            ->from(route('invites.create'))
            ->post(route('invites.store'), [
                'email' => 'master-convite@clube.com',
                'role'  => 'master',
            ]);

        // O role 'master' não está nos allowedInvitableRoles() do diretor →
        // validação falha com erro de campo 'role'
        $response->assertRedirect(route('invites.create'));
        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('invitations', ['email' => 'master-convite@clube.com']);

        // Envio síncrono → assertNothingSent (não assertNothingQueued)
        Mail::assertNothingSent();
    }
}
