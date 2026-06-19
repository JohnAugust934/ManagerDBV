<?php

namespace Tests\Feature\MultiTenant;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Isolamento nos caminhos fora dos global scopes: gestão de usuários, convites,
 * criação de dados e impersonação (modo suporte).
 */
class IsolamentoControllersTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_de_clube_nao_lista_usuarios_de_outro_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $userB = User::factory()->create(['club_id' => $clubB->id, 'role' => 'secretario', 'name' => 'Fulano do B']);

        $this->actingAs($masterA)->get(route('usuarios.index'))
            ->assertOk()
            ->assertDontSee('Fulano do B');
    }

    public function test_master_de_clube_nao_edita_nem_exclui_usuario_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $userB = User::factory()->create(['club_id' => $clubB->id, 'role' => 'secretario']);

        $this->actingAs($masterA)->get(route('usuarios.edit', $userB))->assertForbidden();
        $this->actingAs($masterA)->delete(route('usuarios.destroy', $userB))->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $userB->id]);
    }

    public function test_usuario_criado_recebe_o_clube_do_gestor(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');

        $this->actingAs($masterA)->post(route('usuarios.store'), [
            'name' => 'Novo Secretario',
            'email' => 'novosec@clube.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'secretario',
        ])->assertRedirect(route('usuarios.index'));

        $this->assertDatabaseHas('users', ['email' => 'novosec@clube.com', 'club_id' => $clubA->id]);
    }

    public function test_convite_recebe_o_clube_do_gestor(): void
    {
        Mail::fake();
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');

        $this->actingAs($masterA)->post(route('invites.store'), [
            'email' => 'convidado@clube.com',
            'role' => 'secretario',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('invitations', ['email' => 'convidado@clube.com', 'club_id' => $clubA->id]);
    }

    public function test_tela_de_configuracoes_mostra_o_proprio_clube_nao_o_primeiro(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube Alfa');
        ['master' => $masterB] = criarClubeComDados('Clube Beta');

        $this->actingAs($masterB)->get(route('club.edit'))
            ->assertOk()
            ->assertSee('Clube Beta')
            ->assertDontSee('Clube Alfa');
    }

    public function test_platform_admin_impersonando_lista_apenas_usuarios_do_clube(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        User::factory()->create(['club_id' => $clubA->id, 'role' => 'secretario', 'name' => 'Secretario do A']);
        User::factory()->create(['club_id' => $clubB->id, 'role' => 'secretario', 'name' => 'Secretario do B']);

        $admin = User::factory()->platformAdmin()->create();
        $this->actingAs($admin);
        $this->post(route('platform.enter', $clubA));

        $this->get(route('usuarios.index'))
            ->assertOk()
            ->assertSee('Secretario do A')
            ->assertDontSee('Secretario do B');
    }

    public function test_platform_admin_sem_clube_lista_apenas_admins_da_plataforma(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        User::factory()->create(['club_id' => $clubA->id, 'role' => 'secretario', 'name' => 'Secretario do Clube']);

        $admin = User::factory()->platformAdmin()->create(['name' => 'Admin Principal']);
        $colega = User::factory()->platformAdmin()->create(['name' => 'Admin Colega']);

        $this->actingAs($admin)->get(route('usuarios.index'))
            ->assertOk()
            ->assertSee('Admin Colega')
            ->assertDontSee('Secretario do Clube');
    }

    public function test_platform_admin_sem_clube_nao_gerencia_usuario_de_clube(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        $userClube = User::factory()->create(['club_id' => $clubA->id, 'role' => 'secretario']);

        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)->get(route('usuarios.edit', $userClube))->assertForbidden();
        $this->actingAs($admin)->delete(route('usuarios.destroy', $userClube))->assertForbidden();
    }

    public function test_platform_admin_convida_outro_admin_da_plataforma(): void
    {
        Mail::fake();
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)->post(route('invites.store'), [
            'email' => 'novoadmin@plataforma.com',
            'role' => 'platform_admin',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('invitations', [
            'email' => 'novoadmin@plataforma.com',
            'role' => 'platform_admin',
            'club_id' => null,
        ]);
    }

    public function test_convidado_de_plataforma_vira_admin_da_plataforma(): void
    {
        $invite = \App\Models\Invitation::create([
            'email' => 'futuro@plataforma.com',
            'token' => 'tok-platform-123',
            'role' => 'platform_admin',
            'club_id' => null,
            'expires_at' => now()->addDays(7),
        ]);

        $this->post(route('register.store_invite'), [
            'token' => $invite->token,
            'name' => 'Futuro Admin',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'futuro@plataforma.com',
            'role' => 'platform_admin',
            'is_platform_admin' => true,
            'club_id' => null,
        ]);
    }

    public function test_gestor_de_clube_nao_pode_convidar_admin_da_plataforma(): void
    {
        Mail::fake();
        ['master' => $masterA] = criarClubeComDados('Clube A');

        $this->actingAs($masterA)->post(route('invites.store'), [
            'email' => 'tentativa@plataforma.com',
            'role' => 'platform_admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('invitations', ['email' => 'tentativa@plataforma.com']);
    }

    public function test_platform_admin_em_modo_suporte_cria_dados_no_clube_impersonado(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin);
        $this->post(route('platform.enter', $clubA));

        $this->post(route('caixa.store'), [
            'descricao' => 'Doação em suporte',
            'tipo' => 'entrada',
            'valor' => 100,
            'data_movimentacao' => now()->toDateString(),
            'categoria' => 'Doações',
        ])->assertRedirect(route('caixa.index'));

        // Criado no clube impersonado, não órfão (club_id null).
        $this->assertDatabaseHas('caixas', ['descricao' => 'Doação em suporte', 'club_id' => $clubA->id]);
    }
}
