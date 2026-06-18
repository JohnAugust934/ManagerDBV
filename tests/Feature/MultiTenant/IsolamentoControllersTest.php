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
