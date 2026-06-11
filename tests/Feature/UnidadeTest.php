<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_unidade_com_campos_obrigatorios()
    {
        // 1. Cria Clube e Diretor
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        // 2. Tenta criar unidade
        $response = $this->actingAs($user)->post(route('unidades.store'), [
            'nome' => 'Unidade Alpha',
            'conselheiro' => 'João',
            'grito_guerra' => 'Força total!',
        ]);

        // 3. Verificações
        $response->assertRedirect(route('unidades.index'));

        $this->assertDatabaseHas('unidades', [
            'nome' => 'Unidade Alpha',
            'conselheiro' => 'João',
            'club_id' => $club->id, // Garante que salvou o vínculo
        ]);
    }

    public function test_nao_pode_excluir_unidade_com_desbravadores()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        $unidade = Unidade::create([
            'nome' => 'Unidade Cheia',
            'conselheiro' => 'José',
            'club_id' => $club->id,
        ]);

        // Adiciona um membro manualmente
        Desbravador::create([
            'nome' => 'Membro 1',
            'unidade_id' => $unidade->id,
            'ativo' => true,
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
        ]);

        $response = $this->actingAs($user)->delete(route('unidades.destroy', $unidade->id));

        // Deve voltar com erro e NÃO apagar
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('unidades', ['id' => $unidade->id]);
    }

    public function test_pode_excluir_unidade_vazia()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        $unidade = Unidade::create([
            'nome' => 'Unidade Vazia',
            'conselheiro' => 'Maria',
            'club_id' => $club->id,
        ]);

        $response = $this->actingAs($user)->delete(route('unidades.destroy', $unidade->id));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('unidades', ['id' => $unidade->id]);
    }

    public function test_instrutor_nao_pode_listar_unidades()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $instrutor = User::factory()->create(['club_id' => $club->id, 'role' => 'instrutor']);

        $this->actingAs($instrutor)->get(route('unidades.index'))->assertForbidden();
    }

    public function test_instrutor_nao_pode_ver_detalhe_da_unidade()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $instrutor = User::factory()->create(['club_id' => $club->id, 'role' => 'instrutor']);
        $unidade = Unidade::create(['nome' => 'Alpha', 'conselheiro' => 'João', 'club_id' => $club->id]);

        $this->actingAs($instrutor)->get(route('unidades.show', $unidade))->assertForbidden();
    }

    public function test_nao_pode_editar_unidade_de_outro_clube()
    {
        $club = Club::create(['nome' => 'Meu Clube', 'cidade' => 'SP']);
        $outroClube = Club::create(['nome' => 'Outro Clube', 'cidade' => 'RJ']);
        $diretor = User::factory()->create(['club_id' => $club->id, 'role' => 'diretor']);

        $unidadeAlheia = Unidade::create(['nome' => 'Beta', 'conselheiro' => 'Maria', 'club_id' => $outroClube->id]);

        $this->actingAs($diretor)->get(route('unidades.edit', $unidadeAlheia))->assertForbidden();
        $this->actingAs($diretor)->put(route('unidades.update', $unidadeAlheia), [
            'nome' => 'Hackeada',
            'conselheiro' => 'Intruso',
        ])->assertForbidden();
        $this->assertDatabaseHas('unidades', ['id' => $unidadeAlheia->id, 'nome' => 'Beta']);
    }
}
