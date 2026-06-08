<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsuarioManagementTest extends TestCase
{
    use RefreshDatabase;

    private Club $club;
    private User $master;

    protected function setUp(): void
    {
        parent::setUp();

        $this->club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP', 'associacao' => 'APaC']);
        $this->master = User::factory()->create(['role' => 'master', 'club_id' => $this->club->id]);
    }

    public function test_master_pode_listar_usuarios()
    {
        $response = $this->actingAs($this->master)->get(route('usuarios.index'));

        $response->assertStatus(200);
    }

    public function test_pode_acessar_formulario_de_criacao()
    {
        $response = $this->actingAs($this->master)->get(route('usuarios.create'));

        $response->assertStatus(200);
    }

    public function test_master_pode_criar_usuario_diretor()
    {
        $response = $this->actingAs($this->master)->post(route('usuarios.store'), [
            'name' => 'Novo Diretor',
            'email' => 'diretor@teste.com',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'role' => 'diretor',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'diretor@teste.com', 'role' => 'diretor']);
    }

    public function test_nao_pode_criar_usuario_sem_campos_obrigatorios()
    {
        $response = $this->actingAs($this->master)->post(route('usuarios.store'), [
            'name' => '',
            'email' => '',
            'role' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }

    public function test_nao_master_nao_pode_criar_usuario_master()
    {
        $diretor = User::factory()->create([
            'role' => 'diretor',
            'club_id' => $this->club->id,
            'extra_permissions' => ['gestao_acessos'],
        ]);

        $response = $this->actingAs($diretor)->post(route('usuarios.store'), [
            'name' => 'Novo Master',
            'email' => 'master2@teste.com',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'role' => 'master',
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'master2@teste.com']);
    }

    public function test_pode_editar_usuario()
    {
        $usuario = User::factory()->create(['role' => 'secretario', 'club_id' => $this->club->id]);

        $response = $this->actingAs($this->master)->get(route('usuarios.edit', $usuario));

        $response->assertStatus(200);
        $response->assertSee($usuario->name);
    }

    public function test_pode_atualizar_usuario()
    {
        $usuario = User::factory()->create(['role' => 'secretario', 'club_id' => $this->club->id]);

        $response = $this->actingAs($this->master)->put(route('usuarios.update', $usuario), [
            'name' => 'Nome Atualizado',
            'email' => $usuario->email,
            'role' => 'tesoureiro',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'name' => 'Nome Atualizado', 'role' => 'tesoureiro']);
    }

    public function test_pode_atualizar_usuario_sem_trocar_senha()
    {
        $usuario = User::factory()->create([
            'role' => 'secretario',
            'club_id' => $this->club->id,
            'password' => Hash::make('senhaOriginal'),
        ]);

        $this->actingAs($this->master)->put(route('usuarios.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'role' => 'secretario',
        ]);

        $this->assertTrue(Hash::check('senhaOriginal', $usuario->fresh()->password));
    }

    public function test_nao_pode_excluir_a_si_mesmo()
    {
        $response = $this->actingAs($this->master)->delete(route('usuarios.destroy', $this->master));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->master->id]);
    }

    public function test_pode_excluir_outro_usuario()
    {
        $usuario = User::factory()->create(['role' => 'secretario', 'club_id' => $this->club->id]);

        $this->actingAs($this->master)->delete(route('usuarios.destroy', $usuario));

        $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
    }

    public function test_nao_master_nao_pode_editar_usuario_de_outro_clube()
    {
        $outroClub = Club::create(['nome' => 'Outro Clube', 'cidade' => 'RJ', 'associacao' => 'APC']);
        $usuarioOutroClub = User::factory()->create(['role' => 'secretario', 'club_id' => $outroClub->id]);

        $diretor = User::factory()->create([
            'role' => 'diretor',
            'club_id' => $this->club->id,
            'extra_permissions' => ['gestao_acessos'],
        ]);

        $response = $this->actingAs($diretor)->get(route('usuarios.edit', $usuarioOutroClub));

        $response->assertStatus(403);
    }

    public function test_usuario_sem_gestao_acessos_nao_acessa()
    {
        $secretario = User::factory()->create(['role' => 'secretario', 'club_id' => $this->club->id]);

        $response = $this->actingAs($secretario)->get(route('usuarios.index'));

        $response->assertStatus(403);
    }
}
