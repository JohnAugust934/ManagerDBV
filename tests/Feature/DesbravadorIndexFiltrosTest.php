<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesbravadorIndexFiltrosTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_envia_unidades_apenas_do_clube_do_usuario()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $outro = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        Unidade::factory()->create(['club_id' => $clube->id, 'nome' => 'Falcões']);
        Unidade::factory()->create(['club_id' => $clube->id, 'nome' => 'Águias']);
        Unidade::factory()->create(['club_id' => $outro->id, 'nome' => 'Intrusa']);

        $response = $this->actingAs($user)->get(route('desbravadores.index'));

        $response->assertOk();
        $unidades = $response->viewData('unidades');
        $this->assertCount(2, $unidades);
        $this->assertNotContains('Intrusa', $unidades->pluck('nome')->all());
    }

    public function test_paginacao_preserva_filtros_de_busca()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);

        // 15 desbravadores cujo nome casa com a busca → mais de uma página (10/pág).
        for ($i = 1; $i <= 15; $i++) {
            Desbravador::factory()->create([
                'unidade_id' => $unidade->id,
                'club_id' => $clube->id,
                'nome' => "Pesquisavel {$i}",
                'ativo' => true,
            ]);
        }

        $response = $this->actingAs($user)->get(route('desbravadores.index', ['search' => 'Pesquisavel']));

        $response->assertOk();
        // O link da próxima página deve carregar o parâmetro de busca (withQueryString).
        $response->assertSee('search=Pesquisavel', false);
    }
}
