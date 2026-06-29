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

    public function test_caixa_isolado_por_clube_nao_vaza_lancamentos_de_outro_clube()
    {
        $clubeA = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $clubeB = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $userA = User::factory()->create(['club_id' => $clubeA->id, 'role' => 'tesoureiro']);

        Caixa::create(['descricao' => 'Lancamento do Clube A', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now()->format('Y-m-d'), 'categoria' => 'Campanha', 'club_id' => $clubeA->id]);
        Caixa::create(['descricao' => 'Lancamento do Clube B', 'valor' => 999, 'tipo' => 'entrada', 'data_movimentacao' => now()->format('Y-m-d'), 'categoria' => 'Campanha', 'club_id' => $clubeB->id]);

        $response = $this->actingAs($userA)->get(route('caixa.index'));

        $response->assertOk();
        $response->assertSee('Lancamento do Clube A');
        $response->assertDontSee('Lancamento do Clube B');
    }

    public function test_operacoes_de_caixa_geram_trilha_de_auditoria()
    {
        $clube = Club::create(['nome' => 'Clube Auditado', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        // store → acao "criado"
        $this->actingAs($user)->post(route('caixa.store'), [
            'descricao' => 'Entrada Auditada',
            'tipo' => 'entrada',
            'valor' => 75.00,
            'data_movimentacao' => now()->format('Y-m-d'),
            'categoria' => 'Doações',
        ])->assertRedirect(route('caixa.index'));

        $caixa = Caixa::where('descricao', 'Entrada Auditada')->firstOrFail();
        $this->assertDatabaseHas('caixa_audit_logs', [
            'caixa_id' => $caixa->id,
            'club_id' => $clube->id,
            'user_id' => $user->id,
            'acao' => 'criado',
        ]);

        // update → acao "editado"
        $this->actingAs($user)->put(route('caixa.update', $caixa), [
            'descricao' => 'Entrada Auditada Editada',
            'tipo' => 'entrada',
            'valor' => 80.00,
            'data_movimentacao' => now()->format('Y-m-d'),
            'categoria' => 'Doações',
        ])->assertRedirect(route('caixa.index'));

        $this->assertDatabaseHas('caixa_audit_logs', [
            'caixa_id' => $caixa->id,
            'user_id' => $user->id,
            'acao' => 'editado',
        ]);

        // destroy → acao "excluido" (registrado mesmo apos a exclusao do registro)
        $this->actingAs($user)->delete(route('caixa.destroy', $caixa))
            ->assertRedirect(route('caixa.index'));

        $this->assertDatabaseHas('caixa_audit_logs', [
            'caixa_id' => $caixa->id,
            'club_id' => $clube->id,
            'acao' => 'excluido',
        ]);
    }
}
