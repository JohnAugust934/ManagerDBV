<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Patrimonio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de isolamento para recursos financeiros e patrimoniais.
 *
 * Cobertura complementar ao IsolamentoTenantTest (que cobre Desbravador e Evento).
 * Invariante: nunca retornar 200 com dados de outro clube via ID direto.
 */
class IsolamentoRecursosTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Caixa
    // -------------------------------------------------------------------------

    public function test_usuario_nao_ve_caixa_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $caixaB = Caixa::factory()->forClube($clubB->id)->create(['descricao' => 'Caixa Secreta B']);

        $this->actingAs($masterA)
            ->get(route('caixa.edit', $caixaB))
            ->assertNotFound();
    }

    public function test_usuario_nao_edita_caixa_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $caixaB = Caixa::factory()->forClube($clubB->id)->create(['valor' => 100]);

        $this->actingAs($masterA)
            ->put(route('caixa.update', $caixaB), [
                'descricao' => 'Adulterado',
                'tipo' => 'entrada',
                'valor' => 9999,
                'data_movimentacao' => now()->toDateString(),
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('caixas', ['descricao' => 'Adulterado']);
    }

    public function test_usuario_nao_exclui_caixa_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $caixaB = Caixa::factory()->forClube($clubB->id)->create();

        $this->actingAs($masterA)
            ->delete(route('caixa.destroy', $caixaB))
            ->assertNotFound();

        $this->assertDatabaseHas('caixas', ['id' => $caixaB->id]);
    }

    public function test_listagem_de_caixa_mostra_apenas_dados_do_proprio_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        Caixa::factory()->forClube($clubA->id)->create(['descricao' => 'Entrada A']);
        Caixa::factory()->forClube($clubB->id)->create(['descricao' => 'Entrada B']);

        $this->actingAs($masterA)
            ->get(route('caixa.index'))
            ->assertOk()
            ->assertSee('Entrada A')
            ->assertDontSee('Entrada B');
    }

    // -------------------------------------------------------------------------
    // Patrimônio
    // -------------------------------------------------------------------------

    public function test_usuario_nao_ve_patrimonio_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $patrimonioB = Patrimonio::create([
            'club_id' => $clubB->id,
            'item' => 'Tendas Secretas B',
            'quantidade' => 1,
            'valor_estimado' => 500,
            'estado_conservacao' => 'bom',
        ]);

        $this->actingAs($masterA)
            ->get(route('patrimonio.edit', $patrimonioB))
            ->assertNotFound();
    }

    public function test_usuario_nao_edita_patrimonio_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $patrimonioB = Patrimonio::create([
            'club_id' => $clubB->id,
            'item' => 'Item Original B',
            'quantidade' => 1,
            'valor_estimado' => 500,
            'estado_conservacao' => 'bom',
        ]);

        $this->actingAs($masterA)
            ->put(route('patrimonio.update', $patrimonioB), [
                'item' => 'Item Adulterado',
                'quantidade' => 99,
                'valor_estimado' => 1,
                'estado_conservacao' => 'ruim',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('patrimonios', ['item' => 'Item Original B']);
        $this->assertDatabaseMissing('patrimonios', ['item' => 'Item Adulterado']);
    }

    public function test_usuario_nao_exclui_patrimonio_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $patrimonioB = Patrimonio::create([
            'club_id' => $clubB->id,
            'item' => 'Item Protegido B',
            'quantidade' => 1,
            'valor_estimado' => 200,
            'estado_conservacao' => 'bom',
        ]);

        $this->actingAs($masterA)
            ->delete(route('patrimonio.destroy', $patrimonioB))
            ->assertNotFound();

        $this->assertDatabaseHas('patrimonios', ['id' => $patrimonioB->id]);
    }

    public function test_listagem_de_patrimonio_mostra_apenas_dados_do_proprio_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        Patrimonio::create(['club_id' => $clubA->id, 'item' => 'Tenda A', 'quantidade' => 1, 'valor_estimado' => 100, 'estado_conservacao' => 'bom']);
        Patrimonio::create(['club_id' => $clubB->id, 'item' => 'Tenda B', 'quantidade' => 1, 'valor_estimado' => 100, 'estado_conservacao' => 'bom']);

        $this->actingAs($masterA)
            ->get(route('patrimonio.index'))
            ->assertOk()
            ->assertSee('Tenda A')
            ->assertDontSee('Tenda B');
    }

    // -------------------------------------------------------------------------
    // Mensalidades (pagar de outro clube)
    // -------------------------------------------------------------------------

    public function test_usuario_nao_paga_mensalidade_de_desbravador_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB, 'unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvB = \App\Models\Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);
        $mensalidadeB = \App\Models\Mensalidade::create([
            'desbravador_id' => $dbvB->id,
            'mes' => now()->month,
            'ano' => now()->year,
            'valor' => 50,
            'status' => 'pendente',
        ]);

        $this->actingAs($masterA)
            ->post(route('mensalidades.pagar', $mensalidadeB->id))
            ->assertNotFound();

        $this->assertDatabaseHas('mensalidades', ['id' => $mensalidadeB->id, 'status' => 'pendente']);
    }
}
