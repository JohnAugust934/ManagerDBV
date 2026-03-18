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

    public function test_master_pode_criar_convite_e_envia_email()
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);
        $master = User::factory()->create(['role' => 'master', 'club_id' => $club->id]);

        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'novo@clube.com',
            'role' => 'conselheiro',
        ]);

        $response->assertRedirect(route('invites.index'));
        $this->assertDatabaseHas('invitations', [
            'email' => 'novo@clube.com',
            'role' => 'conselheiro',
            'club_id' => $club->id,
        ]);

        // CORREÇÃO: Mudamos de assertSent para assertQueued
        Mail::assertQueued(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('novo@clube.com');
        });
    }

    public function test_usuario_pode_se_registrar_com_convite()
    {
        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);

        $invite = Invitation::create([
            'email' => 'convidado@clube.com',
            'token' => 'token-falso-123',
            'role' => 'conselheiro',
            'club_id' => $club->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->post(route('register.store_invite'), [
            'token' => 'token-falso-123',
            'name' => 'Usuário Convidado',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Removido o 'absolute: false' para evitar o erro 500 do Laravel Router
        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'convidado@clube.com',
            'role' => 'conselheiro',
            'club_id' => $club->id,
        ]);

        $inviteUsado = Invitation::where('email', 'convidado@clube.com')->first();
        $this->assertNotNull($inviteUsado->registered_at);
    }

    public function test_master_reaproveita_convite_pendente_sem_duplicar_registro()
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);
        $master = User::factory()->create(['role' => 'master', 'club_id' => $club->id]);

        $conviteExistente = Invitation::create([
            'email' => 'pendente@clube.com',
            'token' => 'token-antigo',
            'role' => 'conselheiro',
            'club_id' => $club->id,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'pendente@clube.com',
            'role' => 'tesoureiro',
        ]);

        $response->assertRedirect(route('invites.index'));
        $response->assertSessionHas('success', 'Convite pendente atualizado e reenviado com sucesso!');

        $this->assertDatabaseCount('invitations', 1);

        $conviteAtualizado = $conviteExistente->fresh();
        $this->assertEquals('tesoureiro', $conviteAtualizado->role);
        $this->assertNotEquals('token-antigo', $conviteAtualizado->token);

        Mail::assertQueued(ClubInvitation::class, function ($mail) {
            return $mail->hasTo('pendente@clube.com');
        });
    }

    public function test_nao_recria_convite_ja_utilizado()
    {
        Mail::fake();

        $club = Club::create(['nome' => 'Clube Orion', 'cidade' => 'São Paulo', 'associacao' => 'APL']);
        $master = User::factory()->create(['role' => 'master', 'club_id' => $club->id]);

        Invitation::create([
            'email' => 'usado@clube.com',
            'token' => 'token-usado',
            'role' => 'conselheiro',
            'club_id' => $club->id,
            'expires_at' => now()->addDay(),
            'registered_at' => now(),
        ]);

        $response = $this->actingAs($master)->from(route('invites.create'))->post(route('invites.store'), [
            'email' => 'usado@clube.com',
            'role' => 'conselheiro',
        ]);

        $response->assertRedirect(route('invites.create'));
        $response->assertSessionHas('error', 'Este convite já foi utilizado. Como o e-mail não pode ser reutilizado, faça o gerenciamento diretamente no cadastro de usuários.');

        $this->assertDatabaseCount('invitations', 1);
        Mail::assertNothingQueued();
    }
}
