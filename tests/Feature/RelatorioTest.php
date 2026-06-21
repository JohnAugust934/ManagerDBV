<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Club $clube;

    protected Classe $classe;

    protected Unidade $unidade;

    protected Desbravador $desbravador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);

        $this->user = User::factory()->create([
            'club_id' => $this->clube->id,
            'role' => 'diretor',
        ]);

        $this->unidade = Unidade::factory()->create([
            'club_id' => $this->clube->id,
            'nome' => 'Lobos',
        ]);

        $this->classe = Classe::factory()->create(['nome' => 'Companheiro']);

        $this->desbravador = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'classe_atual' => $this->classe->id,
            'ativo' => true,
            'nome' => 'Daniel Silva',
            'nome_responsavel' => 'Maria Silva',
            'telefone_responsavel' => '11999999999',
            'alergias' => 'Amendoim',
            'plano_saude' => 'Plano Teste',
        ]);
    }

    public function test_pode_acessar_central_de_relatorios()
    {
        $response = $this->actingAs($this->user)->get(route('relatorios.index'));

        $response->assertStatus(200);
        $response->assertSee('Gerador com Filtros Avançados');
        $response->assertSee('Selecione um relatório');
        $response->assertSee('Contatos de Emergência');
        $response->assertSee('method="GET"', false);
        $response->assertDontSee('option value="desbravadores" selected', false);
    }

    public function test_lista_de_desbravadores_usa_nome_da_classe_e_filtra_por_clube()
    {
        $clubeExterno = Club::create(['nome' => 'Outro Clube', 'cidade' => 'RJ']);
        $unidadeExterna = Unidade::factory()->create(['club_id' => $clubeExterno->id, 'nome' => 'Falcao']);
        $classeExterna = Classe::factory()->create(['nome' => 'Pesquisador']);

        Desbravador::factory()->create([
            'unidade_id' => $unidadeExterna->id,
            'classe_atual' => $classeExterna->id,
            'ativo' => true,
            'nome' => 'Visitante Externo',
        ]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Lista de Desbravadores', $data['titulo']);
            $this->assertCount(1, $data['linhas']);
            $this->assertSame('Daniel Silva', $data['linhas'][0][0]);
            $this->assertSame('Lobos', $data['linhas'][0][1]);
            $this->assertSame('Companheiro', $data['linhas'][0][2]);
            $this->assertNotSame((string) $this->classe->id, $data['linhas'][0][2]);
            $this->assertSame('Clube Teste', $data['clubeNome']);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'desbravadores',
            'status' => 'ativos',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_unidade_sem_club_id_e_rejeitada_pelo_banco()
    {
        // O vazamento que este teste guardava (unidade sem club_id aparecendo para
        // outro clube) agora é IMPOSSÍVEL: a Fase 2 tornou unidades.club_id NOT NULL.
        // Garantia no nível do banco, mais forte que a verificação anterior em runtime.
        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->unidade->update(['club_id' => null]);
    }

    public function test_pode_gerar_relatorio_personalizado_caixa()
    {
        // GlobalScope ClubScope aplica o filtro — registro deve ter club_id do clube corrente.
        Caixa::create([
            'descricao' => 'Teste',
            'valor' => 50,
            'tipo' => 'entrada',
            'data_movimentacao' => now(),
            'club_id' => $this->clube->id,
        ]);

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'caixa',
            'tipo_movimentacao' => 'todos',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_fichas_medicas_em_lote()
    {
        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'fichas_medicas',
            'status' => 'ativos',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_ficha_completa_em_lote_respeita_filtros_e_carrega_dados_relacionados()
    {
        $outraUnidade = Unidade::factory()->create([
            'club_id' => $this->clube->id,
            'nome' => 'Aguias',
        ]);

        $evento = Evento::create([
            'nome' => 'Acampamento',
            'data_inicio' => now(),
            'data_fim' => now()->addDay(),
            'local' => 'Sitio',
            'valor' => 30,
            'club_id' => $this->clube->id,
        ]);

        $this->desbravador->eventos()->attach($evento->id, [
            'pago' => true,
            'autorizacao_entregue' => true,
        ]);

        Frequencia::create([
            'desbravador_id' => $this->desbravador->id,
            'data' => now(),
            'presente' => true,
            'pontual' => true,
            'biblia' => true,
            'uniforme' => true,
        ]);

        Desbravador::factory()->create([
            'unidade_id' => $outraUnidade->id,
            'ativo' => false,
            'nome' => 'Inativo Fora do Filtro',
        ]);

        $this->mockPdfLoadView('relatorios.fichas_completas_lote', function (array $data) {
            $this->assertCount(1, $data['desbravadores']);
            $this->assertSame('Daniel Silva', $data['desbravadores'][0]['nome']);
            $this->assertSame('Companheiro', $data['desbravadores'][0]['classe']);
            $this->assertSame('Maria Silva', $data['desbravadores'][0]['nome_responsavel']);
            $this->assertSame('Lobos', $data['desbravadores'][0]['unidade']);
            $this->assertSame('Acampamento', $data['desbravadores'][0]['eventos'][0]['nome']);
            $this->assertSame(30, $data['desbravadores'][0]['frequencias']['pontos']);
            $this->assertSame('Somente ativos', $data['filtros']['Status']);
            $this->assertSame('Lobos', $data['filtros']['Unidade']);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'fichas_completas',
            'status' => 'ativos',
            'unidade_id' => $this->unidade->id,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_de_frequencia_com_pontuacao_calculada()
    {
        Frequencia::create([
            'desbravador_id' => $this->desbravador->id,
            'data' => now(),
            'presente' => true,
            'pontual' => true,
            'biblia' => true,
            'uniforme' => true,
        ]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Relatório de Frequência', $data['titulo']);
            $this->assertSame('Daniel Silva', $data['linhas'][0][0]);
            $this->assertSame('1', $data['linhas'][0][2]);
            $this->assertSame('30', $data['linhas'][0][7]);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'frequencia',
            'status' => 'ativos',
            'mes' => now()->month,
            'ano' => now()->year,
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_relatorio_de_aniversariantes_mostra_mes_em_portugues()
    {
        $this->desbravador->update([
            'data_nascimento' => now()->setMonth(3)->setDay(10),
        ]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Março', $data['metricas'][0]['value']);
            $this->assertSame('Março', $data['filtros']['Mês de aniversário']);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'aniversariantes',
            'status' => 'ativos',
            'mes_aniversario' => 3,
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_relatorio_de_ranking_individual_considera_apenas_ano_atual()
    {
        Frequencia::create([
            'desbravador_id' => $this->desbravador->id,
            'data' => now(),
            'presente' => true,
            'pontual' => true,
            'biblia' => true,
            'uniforme' => true,
        ]);

        Frequencia::create([
            'desbravador_id' => $this->desbravador->id,
            'data' => now()->subYear(),
            'presente' => true,
            'pontual' => true,
            'biblia' => true,
            'uniforme' => true,
        ]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame((string) now()->year, $data['metricas'][0]['value']);
            $this->assertSame('30', $data['linhas'][0][4]);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'ranking_desbravadores',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_de_inadimplencia_filtrado()
    {
        Mensalidade::create([
            'desbravador_id' => $this->desbravador->id,
            'mes' => now()->subMonth()->month,
            'ano' => now()->subMonth()->year,
            'valor' => 35.50,
            'status' => 'pendente',
        ]);

        $inativo = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'ativo' => false,
            'nome' => 'Inativo Devendo',
        ]);

        Mensalidade::create([
            'desbravador_id' => $inativo->id,
            'mes' => now()->subMonth()->month,
            'ano' => now()->subMonth()->year,
            'valor' => 40.00,
            'status' => 'pendente',
        ]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Relatório de Inadimplência', $data['titulo']);
            $this->assertCount(1, $data['linhas']);
            $this->assertSame('Daniel Silva', $data['linhas'][0][0]);
            $this->assertSame('Lobos', $data['linhas'][0][1]);
            $this->assertSame('Somente ativos', $data['filtros']['Status']);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'inadimplencia',
            'status' => 'ativos',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_de_contatos_de_emergencia()
    {
        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Contatos de Emergência', $data['titulo']);
            $this->assertSame('Daniel Silva', $data['linhas'][0][0]);
            $this->assertSame('Maria Silva', $data['linhas'][0][2]);
            $this->assertSame('Amendoim', $data['linhas'][0][4]);
            $this->assertSame('Plano Teste', $data['linhas'][0][5]);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'contatos_emergencia',
            'status' => 'ativos',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_personalizado_por_get_para_permitir_recarregar_pdf()
    {
        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Contatos de Emergência', $data['titulo']);
            $this->assertSame('Daniel Silva', $data['linhas'][0][0]);
        });

        $response = $this->actingAs($this->user)->get(route('relatorios.custom', [
            'tipo' => 'contatos_emergencia',
            'status' => 'ativos',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_relatorio_de_especialidades_agrupa_por_especialidade_e_filtra_por_clube()
    {
        $esp = \App\Models\Especialidade::factory()->create(['nome' => 'Culinária', 'area' => 'Artes e Habilidades Manuais', 'is_oficial' => true, 'is_avancada' => false]);
        $this->desbravador->especialidades()->attach($esp->id, ['data_conclusao' => now()->toDateString()]);

        // Desbravador de outro clube não deve aparecer
        $clubeExterno = Club::create(['nome' => 'Outro Clube', 'cidade' => 'RJ']);
        $unidadeExterna = Unidade::factory()->create(['club_id' => $clubeExterno->id]);
        $desbExterno = Desbravador::factory()->create(['unidade_id' => $unidadeExterna->id, 'ativo' => true]);
        $espExterna = \App\Models\Especialidade::factory()->create(['nome' => 'Fotografia']);
        $desbExterno->especialidades()->attach($espExterna->id, ['data_conclusao' => now()->toDateString()]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Relatório de Especialidades', $data['titulo']);
            $this->assertCount(1, $data['linhas']);
            $this->assertSame('Culinária', $data['linhas'][0][0]);
            $this->assertSame('Artes e Habilidades Manuais', $data['linhas'][0][1]);
            $this->assertSame('Oficial', $data['linhas'][0][2]);
            $this->assertSame('1', $data['linhas'][0][3]);
            $this->assertSame('100%', $data['linhas'][0][4]);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'especialidades',
            'status' => 'ativos',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_relatorio_de_progresso_de_classe_calcula_percentual_corretamente()
    {
        $req1 = \App\Models\Requisito::create(['classe_id' => $this->classe->id, 'codigo' => 'A1', 'descricao' => 'Req 1', 'categoria' => 'Geral']);
        $req2 = \App\Models\Requisito::create(['classe_id' => $this->classe->id, 'codigo' => 'A2', 'descricao' => 'Req 2', 'categoria' => 'Geral']);

        // Desbravador cumpriu apenas 1 dos 2 requisitos
        $this->desbravador->requisitosCumpridos()->attach($req1->id, ['data_conclusao' => now()->toDateString()]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Progresso de Classe', $data['titulo']);
            $this->assertCount(1, $data['linhas']);

            $linha = $data['linhas'][0];
            $this->assertSame('Daniel Silva', $linha[0]);
            $this->assertSame('Lobos', $linha[1]);
            $this->assertSame('Companheiro', $linha[2]);
            $this->assertSame('2', $linha[3]);   // total requisitos
            $this->assertSame('1', $linha[4]);   // cumpridos
            $this->assertSame('50%', $linha[5]); // progresso
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'progresso_classe',
            'status' => 'ativos',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_relatorio_de_eventos_lista_inscricoes_pagamentos_e_autorizacoes()
    {
        $evento = Evento::create([
            'nome' => 'Acampamento Anual',
            'data_inicio' => now()->startOfYear()->addMonths(2),
            'data_fim' => now()->startOfYear()->addMonths(2)->addDays(3),
            'local' => 'Sítio das Flores',
            'valor' => 80.00,
            'club_id' => $this->clube->id,
        ]);

        // 1 inscrito: pagou mas não entregou autorização
        $this->desbravador->eventos()->attach($evento->id, [
            'pago' => true,
            'autorizacao_entregue' => false,
        ]);

        // Evento de outro clube não deve aparecer no relatório
        $clubeExterno = Club::create(['nome' => 'Clube B', 'cidade' => 'MG']);
        Evento::create([
            'nome' => 'Evento Externo',
            'data_inicio' => now(),
            'local' => 'Outro Local',
            'valor' => 0,
            'club_id' => $clubeExterno->id,
        ]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertSame('Relatório de Eventos', $data['titulo']);
            $this->assertCount(1, $data['linhas']);

            $linha = $data['linhas'][0];
            $this->assertSame('Acampamento Anual', $linha[0]);
            $this->assertSame('Sítio das Flores', $linha[2]);
            $this->assertSame('1', $linha[3]); // inscritos
            $this->assertSame('1', $linha[4]); // pagos
            $this->assertSame('0', $linha[5]); // autorização OK
            $this->assertSame('1', $linha[6]); // pendentes
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'eventos',
            'ano' => now()->year,
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    // -------------------------------------------------------------------------
    // Testes para as correções da Parte 1 do Plano de Escalabilidade
    // -------------------------------------------------------------------------

    public function test_index_calcula_saldo_caixa_via_agregacao_sql_sem_carregar_todos_registros(): void
    {
        Caixa::create(['descricao' => 'Entrada 1', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $this->clube->id]);
        Caixa::create(['descricao' => 'Entrada 2', 'valor' => 50,  'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $this->clube->id]);
        Caixa::create(['descricao' => 'Saída 1',   'valor' => 30,  'tipo' => 'saida',   'data_movimentacao' => now(), 'club_id' => $this->clube->id]);

        $response = $this->actingAs($this->user)->get(route('relatorios.index'));

        $response->assertOk();
        // saldo esperado = (100 + 50) - 30 = 120
        $response->assertViewHas('stats', fn ($stats) => $stats['saldo_caixa'] === 120.0);
    }

    public function test_index_calcula_patrimonio_total_com_quantidade_via_agregacao_sql(): void
    {
        \App\Models\Patrimonio::create([
            'item' => 'Barraca',
            'quantidade' => 3,
            'valor_estimado' => 200.00,
            'estado_conservacao' => 'bom',
            'club_id' => $this->clube->id,
        ]);
        \App\Models\Patrimonio::create([
            'item' => 'Lanterna',
            'quantidade' => 5,
            'valor_estimado' => 40.00,
            'estado_conservacao' => 'bom',
            'club_id' => $this->clube->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('relatorios.index'));

        $response->assertOk();
        // (3 * 200) + (5 * 40) = 600 + 200 = 800
        $response->assertViewHas('stats', fn ($stats) => $stats['patrimonio_total'] === 800.0);
    }

    public function test_index_saldo_caixa_ignora_registros_de_outro_clube(): void
    {
        $outroClube = Club::create(['nome' => 'Clube Rival', 'cidade' => 'RJ']);

        Caixa::create(['descricao' => 'Entrada própria', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $this->clube->id]);
        Caixa::create(['descricao' => 'Entrada alheia', 'valor' => 9999, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $outroClube->id]);

        $response = $this->actingAs($this->user)->get(route('relatorios.index'));

        $response->assertOk();
        $response->assertViewHas('stats', fn ($stats) => $stats['saldo_caixa'] === 100.0);
    }

    private function mockPdfLoadView(string $expectedView, callable $assertion): void
    {
        $pdfWrapper = \Mockery::mock(DomPdfWrapper::class);
        $pdfWrapper->shouldReceive('setPaper')->andReturnSelf();
        $pdfWrapper->shouldReceive('stream')->andReturn(response('pdf', 200, ['content-type' => 'application/pdf']));

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) use ($expectedView, $assertion) {
                $this->assertSame($expectedView, $view);
                $assertion($data);

                return true;
            })
            ->andReturn($pdfWrapper);
    }
}
