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

    public function test_pode_listar_unidades()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);
        Unidade::create(['nome' => 'Unidade Visível', 'conselheiro' => 'Ana', 'club_id' => $club->id]);

        $response = $this->actingAs($user)->get(route('unidades.index'));

        $response->assertStatus(200);
        $response->assertSee('Unidade Visível');
    }

    public function test_pode_ver_detalhes_da_unidade()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);
        $unidade = Unidade::create(['nome' => 'Unidade Delta', 'conselheiro' => 'Carlos', 'club_id' => $club->id]);

        $response = $this->actingAs($user)->get(route('unidades.show', $unidade));

        $response->assertStatus(200);
        $response->assertSee('Unidade Delta');
    }

    public function test_instrutor_nao_pode_listar_unidades()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'instrutor']);

        $response = $this->actingAs($user)->get(route('unidades.index'));

        $response->assertStatus(403);
    }

    public function test_unidade_de_outro_clube_nao_aparece()
    {
        $club = Club::create(['nome' => 'Meu Clube', 'cidade' => 'SP']);
        $outroClub = Club::create(['nome' => 'Outro Clube', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);
        Unidade::create(['nome' => 'Minha Unidade', 'conselheiro' => 'X', 'club_id' => $club->id]);
        Unidade::create(['nome' => 'Unidade Alheia', 'conselheiro' => 'Y', 'club_id' => $outroClub->id]);

        $response = $this->actingAs($user)->get(route('unidades.index'));

        $response->assertSee('Minha Unidade');
        $response->assertDontSee('Unidade Alheia');
    }

    public function test_pode_ativar_ranking_da_unidade()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);
        $unidade = Unidade::create([
            'nome' => 'Unidade Rank',
            'conselheiro' => 'X',
            'club_id' => $club->id,
            'no_ranking' => false,
        ]);

        $response = $this->actingAs($user)->patch(route('unidades.toggle-ranking', $unidade));

        $response->assertRedirect();
        $this->assertTrue($unidade->fresh()->no_ranking);
    }
}
