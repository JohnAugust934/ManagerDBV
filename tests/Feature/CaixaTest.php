<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaixaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_logado_pode_ver_o_caixa_com_totais_corretos()
    {
        // 1. Setup
        $clube = Club::create(['nome' => 'Clube Financeiro', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        // 2. Criação de dados manualmente (sem factory)
        Caixa::create([
            'descricao' => 'Venda de Biscoitos',
            'valor' => 200.00,
            'tipo' => 'entrada',
            'data_movimentacao' => now()->format('Y-m-d'),
            'categoria' => 'Campanha',
            'club_id' => $clube->id,
        ]);

        Caixa::create([
            'descricao' => 'Compra de Material',
            'valor' => 50.00,
            'tipo' => 'saida',
            'data_movimentacao' => now()->format('Y-m-d'),
            'categoria' => 'Secretaria',
            'club_id' => $clube->id,
        ]);

        // Saldo esperado: 200 - 50 = 150

        // 3. Ação
        $response = $this->actingAs($user)->get(route('caixa.index'));

        // 4. Verificação
        $response->assertStatus(200);

        // Verifica textos na tela
        $response->assertSee('Fluxo de Caixa');
        $response->assertSee('Venda de Biscoitos');
        $response->assertSee('Compra de Material');

        // Verifica formatação de moeda (valores aproximados ou string formatada)
        // Nota: O number_format pode gerar espaços não-quebráveis em alguns sistemas,
        // então buscamos partes da string.
        $response->assertSee('200,00');
        $response->assertSee('50,00');

        // Verifica se o saldo calculado está na view (150,00)
        $response->assertSee('150,00');
    }

    public function test_pode_criar_uma_entrada_no_caixa()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        $dados = [
            'descricao' => 'Doação da Igreja',
            'tipo' => 'entrada',
            'valor' => 150.50,
            'data_movimentacao' => now()->format('Y-m-d'),
            'categoria' => 'Doações',
        ];

        $response = $this->actingAs($user)->post(route('caixa.store'), $dados);

        $response->assertRedirect(route('caixa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('caixas', [
            'descricao' => 'Doação da Igreja',
            'valor' => 150.50,
            'tipo' => 'entrada',
        ]);
    }

    public function test_validacao_de_campos_obrigatorios_caixa()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        $response = $this->actingAs($user)->post(route('caixa.store'), []);

        $response->assertSessionHasErrors(['descricao', 'valor', 'tipo', 'data_movimentacao']);
    }
}
