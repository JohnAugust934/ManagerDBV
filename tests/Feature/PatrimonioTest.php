<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Patrimonio;
use App\Models\PatrimonioManutencao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatrimonioTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_ver_lista_de_patrimonio()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);

        // GlobalScope ClubScope aplica filtro — registro deve ter club_id correto.
        Patrimonio::create([
            'item' => 'Barraca Iglu',
            'quantidade' => 2,
            'valor_estimado' => 200.00,
            'estado_conservacao' => 'Bom',
            'observacoes' => 'Barraca para 4 pessoas',
            'club_id' => $club->id,
        ]);

        $response = $this->actingAs($user)->get(route('patrimonio.index'));

        $response->assertStatus(200);
        $response->assertSee('Inventário de Patrimônio');
        $response->assertSee('Barraca Iglu');
    }

    public function test_usuario_pode_cadastrar_novo_item()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);

        $dados = [
            'item' => 'Lampião a Gás',
            'quantidade' => 1,
            'valor_estimado' => 150.50,
            'estado_conservacao' => 'Novo',
            'data_aquisicao' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)->post(route('patrimonio.store'), $dados);

        $response->assertRedirect(route('patrimonio.index'));
        $this->assertDatabaseHas('patrimonios', ['item' => 'Lampião a Gás', 'club_id' => $club->id]);
    }

    public function test_busca_patrimonio_case_insensitive()
    {
        $club = Club::create(['nome' => 'Clube Busca', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);

        Patrimonio::create([
            'item' => 'Mochila Cargueira',
            'quantidade' => 5,
            'valor_estimado' => 500,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);

        Patrimonio::create([
            'item' => 'Fogareiro',
            'quantidade' => 2,
            'valor_estimado' => 100,
            'estado_conservacao' => 'Regular',
            'club_id' => $club->id,
        ]);

        $response = $this->actingAs($user)->get(route('patrimonio.index', ['search' => 'mochila']));
        $response->assertSee('Mochila Cargueira');
        $response->assertDontSee('Fogareiro');

        $response2 = $this->actingAs($user)->get(route('patrimonio.index', ['search' => 'CARGUEIRA']));
        $response2->assertSee('Mochila Cargueira');
    }

    public function test_pode_acessar_formulario_de_edicao()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);
        $patrimonio = Patrimonio::create([
            'item' => 'Barraca',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);

        $response = $this->actingAs($user)->get(route('patrimonio.edit', $patrimonio));

        $response->assertStatus(200);
        $response->assertSee('Barraca');
    }

    public function test_pode_atualizar_patrimonio()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);
        $patrimonio = Patrimonio::create([
            'item' => 'Corda Velha',
            'quantidade' => 3,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);

        $response = $this->actingAs($user)->put(route('patrimonio.update', $patrimonio), [
            'item' => 'Corda Nova',
            'quantidade' => 5,
            'estado_conservacao' => 'Bom',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('patrimonios', ['id' => $patrimonio->id, 'item' => 'Corda Nova', 'quantidade' => 5]);
    }

    public function test_mudanca_de_estado_cria_manutencao_automatica()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);
        $patrimonio = Patrimonio::create([
            'item' => 'Lona',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);

        $this->actingAs($user)->put(route('patrimonio.update', $patrimonio), [
            'item' => 'Lona',
            'quantidade' => 1,
            'estado_conservacao' => 'Ruim',
        ]);

        $this->assertDatabaseHas('patrimonio_manutencoes', [
            'patrimonio_id' => $patrimonio->id,
            'estado_anterior' => 'Bom',
            'estado_novo' => 'Ruim',
        ]);
    }

    public function test_pode_excluir_patrimonio()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);
        $patrimonio = Patrimonio::create([
            'item' => 'Item a Excluir',
            'quantidade' => 1,
            'estado_conservacao' => 'Ruim',
            'club_id' => $club->id,
        ]);

        $this->actingAs($user)->delete(route('patrimonio.destroy', $patrimonio));

        $this->assertDatabaseMissing('patrimonios', ['id' => $patrimonio->id]);
    }

    public function test_pode_registrar_manutencao_manual()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);
        $patrimonio = Patrimonio::create([
            'item' => 'Tenda',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);

        $response = $this->actingAs($user)->post(route('patrimonio.manutencoes.store', $patrimonio), [
            'data' => '2025-06-01',
            'estado_novo' => 'Regular',
            'descricao' => 'Costura rasgada no zipper',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('patrimonio_manutencoes', [
            'patrimonio_id' => $patrimonio->id,
            'descricao' => 'Costura rasgada no zipper',
        ]);
    }

    public function test_pode_excluir_manutencao()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);
        $patrimonio = Patrimonio::create([
            'item' => 'Fogareiro',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);
        $manutencao = PatrimonioManutencao::create([
            'patrimonio_id' => $patrimonio->id,
            'user_id' => $user->id,
            'data' => '2025-01-01',
            'estado_anterior' => 'Novo',
            'estado_novo' => 'Bom',
            'descricao' => 'Revisão geral',
        ]);

        $this->actingAs($user)->delete(route('patrimonio.manutencoes.destroy', [$patrimonio, $manutencao]));

        $this->assertDatabaseMissing('patrimonio_manutencoes', ['id' => $manutencao->id]);
    }

    public function test_patrimonio_de_outro_clube_nao_aparece_na_lista()
    {
        $club = Club::create(['nome' => 'Meu Clube', 'cidade' => 'SP']);
        $outroClub = Club::create(['nome' => 'Outro Clube', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);

        Patrimonio::create([
            'item' => 'Item do Meu Clube',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);
        Patrimonio::create([
            'item' => 'Item do Outro Clube',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
            'club_id' => $outroClub->id,
        ]);

        $response = $this->actingAs($user)->get(route('patrimonio.index'));

        $response->assertSee('Item do Meu Clube');
        $response->assertDontSee('Item do Outro Clube');
    }

    public function test_usuario_sem_financeiro_nao_acessa_patrimonio()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // Secretário não possui permissão de financeiro.
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'secretario']);

        $this->actingAs($user)->get(route('patrimonio.index'))->assertForbidden();
    }

    public function test_editar_sem_mudar_estado_nao_gera_manutencao()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);
        $patrimonio = Patrimonio::create([
            'item' => 'Bandeira',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
            'club_id' => $club->id,
        ]);

        $this->actingAs($user)->put(route('patrimonio.update', $patrimonio), [
            'item' => 'Bandeira Nova',
            'quantidade' => 1,
            'estado_conservacao' => 'Bom',
        ])->assertRedirect(route('patrimonio.index'));

        $this->assertDatabaseCount('patrimonio_manutencoes', 0);
    }

    public function test_remover_manutencao_de_outro_item_retorna_404()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);

        $patrimonioA = Patrimonio::create(['item' => 'A', 'quantidade' => 1, 'estado_conservacao' => 'Bom', 'club_id' => $club->id]);
        $patrimonioB = Patrimonio::create(['item' => 'B', 'quantidade' => 1, 'estado_conservacao' => 'Bom', 'club_id' => $club->id]);
        $manutencao = PatrimonioManutencao::create([
            'patrimonio_id' => $patrimonioB->id,
            'user_id' => $user->id,
            'data' => '2025-01-01',
            'estado_anterior' => 'Bom',
            'estado_novo' => 'Ruim',
            'descricao' => 'Teste',
        ]);

        // URL usa o patrimônio errado: deve recusar com 404 e preservar o registro.
        $this->actingAs($user)
            ->delete(route('patrimonio.manutencoes.destroy', [$patrimonioA, $manutencao]))
            ->assertNotFound();

        $this->assertDatabaseHas('patrimonio_manutencoes', ['id' => $manutencao->id]);
    }
}
