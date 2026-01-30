<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invitation;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_pode_criar_convite()
    {
        $master = User::factory()->create(['role' => 'master']);

        $response = $this->actingAs($master)->post(route('invites.store'), [
            'email' => 'novo@teste.com',
            'role' => 'diretor',
            'extra_permissions' => ['financeiro']
        ]);

        $response->assertRedirect(route('invites.index'));

        $this->assertDatabaseHas('invitations', [
            'email' => 'novo@teste.com',
            'role' => 'diretor'
        ]);
    }

    public function test_usuario_pode_se_registrar_com_convite()
    {
        // CORREÇÃO: Adicionado campo 'cidade'
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'São Paulo']);

        $invite = Invitation::create([
            'email' => 'convidado@teste.com',
            'token' => 'token_unico_123',
            'role' => 'conselheiro',
            'club_id' => $club->id,
            'extra_permissions' => []
        ]);

        // Acessa a tela de registro
        $response = $this->get(route('register.invite', ['token' => 'token_unico_123']));
        $response->assertStatus(200);
        $response->assertSee('convidado@teste.com');

        // Envia o formulário
        $response = $this->post(route('register.store_invite'), [
            'token' => 'token_unico_123',
            'name' => 'João Convidado',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'convidado@teste.com',
            'name' => 'João Convidado',
            'role' => 'conselheiro',
            'club_id' => $club->id
        ]);

        $this->assertNotNull($invite->fresh()->registered_at);
    }
}
