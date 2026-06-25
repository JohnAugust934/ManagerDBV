<?php

namespace Tests\Feature\Financeiro;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integridade financeira de caixa/mensalidades:
 * - somatórios/saldo da coleção Eloquent batem com a agregação SQL (o mesmo
 *   oráculo SUM(CASE...) usado em RelatorioController);
 * - valores monetários (decimal(10,2)) não perdem precisão;
 * - trilha de autoria (RegistraAutoria) preenche created_by/updated_by;
 * - movimentações de um clube não contaminam os totais de outro.
 */
class IntegridadeFinanceiraTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Replica o oráculo de agregação do RelatorioController.
     *
     * @return array{entradas: float, saidas: float, saldo: float}
     */
    private function totaisViaSql(): array
    {
        $totais = Caixa::selectRaw("
            SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END) as total_entradas,
            SUM(CASE WHEN tipo = 'saida'   THEN valor ELSE 0 END) as total_saidas
        ")->first();

        $entradas = (float) ($totais->total_entradas ?? 0);
        $saidas = (float) ($totais->total_saidas ?? 0);

        return ['entradas' => $entradas, 'saidas' => $saidas, 'saldo' => $entradas - $saidas];
    }

    public function test_somatorios_da_colecao_batem_com_a_agregacao_sql(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        $this->actingAs($masterA);

        $entradas = [200.00, 19.99, 1234.56, 0.01];
        $saidas = [50.00, 7.07, 0.99];

        foreach ($entradas as $i => $v) {
            Caixa::create(['descricao' => "Entrada $i", 'valor' => $v, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubA->id]);
        }
        foreach ($saidas as $i => $v) {
            Caixa::create(['descricao' => "Saida $i", 'valor' => $v, 'tipo' => 'saida', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubA->id]);
        }

        $movimentacoes = Caixa::all();
        $entradasEloquent = (float) $movimentacoes->where('tipo', 'entrada')->sum('valor');
        $saidasEloquent = (float) $movimentacoes->where('tipo', 'saida')->sum('valor');
        $saldoEloquent = $entradasEloquent - $saidasEloquent;

        $sql = $this->totaisViaSql();

        // Coleção Eloquent vs. agregação SQL — devem bater à casa do centavo.
        $this->assertEqualsWithDelta($sql['entradas'], $entradasEloquent, 0.0001);
        $this->assertEqualsWithDelta($sql['saidas'], $saidasEloquent, 0.0001);
        $this->assertEqualsWithDelta($sql['saldo'], $saldoEloquent, 0.0001);

        // E ambos batem com o esperado calculado à mão.
        $this->assertSame('1.454,56', number_format($entradasEloquent, 2, ',', '.'));
        $this->assertSame('58,06', number_format($saidasEloquent, 2, ',', '.'));
        $this->assertSame('1.396,50', number_format($saldoEloquent, 2, ',', '.'));
    }

    public function test_valores_monetarios_nao_perdem_precisao(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        $this->actingAs($masterA);

        // 0.10 + 0.20 é o caso clássico de drift de ponto flutuante.
        Caixa::create(['descricao' => 'a', 'valor' => 0.10, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubA->id]);
        Caixa::create(['descricao' => 'b', 'valor' => 0.20, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubA->id]);

        $caixa = Caixa::where('descricao', 'a')->first();
        // O cast decimal:2 mantém a representação exata em string.
        $this->assertSame('0.10', (string) $caixa->valor);

        $soma = $this->totaisViaSql()['entradas'];
        $this->assertSame('0,30', number_format($soma, 2, ',', '.'));

        // Valor grande com centavos preserva as casas decimais.
        Caixa::create(['descricao' => 'grande', 'valor' => 1234567.89, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubA->id]);
        $this->assertSame('1234567.89', (string) Caixa::where('descricao', 'grande')->first()->valor);
    }

    public function test_autoria_preenchida_em_caixa_e_mensalidade(): void
    {
        ['club' => $clubA, 'master' => $masterA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        $this->actingAs($masterA);

        $caixa = Caixa::create(['descricao' => 'Com autoria', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubA->id]);
        $this->assertEquals($masterA->id, $caixa->created_by);
        $this->assertEquals($masterA->id, $caixa->updated_by);
        $this->assertTrue($caixa->criadoPor->is($masterA));

        $dbv = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $mens = Mensalidade::create(['desbravador_id' => $dbv->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);
        $this->assertEquals($masterA->id, $mens->created_by);

        // Edição por outro usuário atualiza updated_by, preserva created_by.
        $outro = User::factory()->create(['club_id' => $clubA->id, 'role' => 'tesoureiro']);
        $this->actingAs($outro);
        $caixa->update(['descricao' => 'Editado']);
        $caixa->refresh();
        $this->assertEquals($masterA->id, $caixa->created_by);
        $this->assertEquals($outro->id, $caixa->updated_by);
    }

    public function test_totais_isolados_por_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        // Sem global scope (contexto neutro): cria movimentações nos dois clubes.
        Caixa::create(['descricao' => 'A1', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubA->id]);
        Caixa::create(['descricao' => 'B1', 'valor' => 999, 'tipo' => 'entrada', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubB->id]);
        Caixa::create(['descricao' => 'B2', 'valor' => 7, 'tipo' => 'saida', 'data_movimentacao' => now()->toDateString(), 'categoria' => 'X', 'club_id' => $clubB->id]);

        // Autenticado no clube A, o oráculo SQL (sob global scope) só vê A.
        $this->actingAs($masterA);
        $totaisA = $this->totaisViaSql();
        $this->assertEqualsWithDelta(100.0, $totaisA['entradas'], 0.0001);
        $this->assertEqualsWithDelta(0.0, $totaisA['saidas'], 0.0001);
        $this->assertEqualsWithDelta(100.0, $totaisA['saldo'], 0.0001);
    }
}
