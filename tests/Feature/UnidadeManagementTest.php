<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnidadeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_unidade_com_campos_obrigatorios()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->post(route('unidades.store'), [
            'nome' => 'Unidade Alpha',
            'conselheiro' => 'João',
            'grito_guerra' => 'Força total!',
        ]);

        $response->assertRedirect(route('unidades.index'));

        $this->assertDatabaseHas('unidades', [
            'nome' => 'Unidade Alpha',
            'conselheiro' => 'João',
            'club_id' => $club->id,
        ]);
    }

    public function test_nao_pode_criar_sem_conselheiro()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->post(route('unidades.store'), [
            'nome' => 'Unidade Beta',
            'grito_guerra' => 'Grito',
        ]);

        $response->assertSessionHasErrors(['conselheiro']);
    }

    public function test_pode_editar_unidade()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        // CORREÇÃO: Garante que a unidade criada pertença ao clube do usuário
        $unidade = Unidade::create([
            'nome' => 'Unidade Antiga',
            'conselheiro' => 'José',
            'club_id' => $club->id, // <--- O VÍNCULO IMPORTANTE
        ]);

        $response = $this->actingAs($user)->put(route('unidades.update', $unidade), [
            'nome' => 'Unidade Nova',
            'conselheiro' => 'Maria',
            'grito_guerra' => 'Novo Grito',
        ]);

        // Redireciona para index após update (padrão do Controller)
        $response->assertRedirect(route('unidades.index'));

        $this->assertDatabaseHas('unidades', [
            'id' => $unidade->id,
            'nome' => 'Unidade Nova',
            'conselheiro' => 'Maria',
        ]);
    }
}
