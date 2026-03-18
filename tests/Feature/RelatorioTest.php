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
        $response->assertSee('Gerador de Relatorio Personalizado');
        $response->assertSee('Selecione um relatorio');
        $response->assertSee('Contatos de Emergencia');
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

    public function test_relatorios_personalizados_funcionam_com_unidades_legadas_sem_club_id()
    {
        $this->unidade->update(['club_id' => null]);

        $this->mockPdfLoadView('relatorios.table', function (array $data) {
            $this->assertCount(1, $data['linhas']);
            $this->assertSame('Daniel Silva', $data['linhas'][0][0]);
        });

        $response = $this->actingAs($this->user)->post(route('relatorios.custom'), [
            'tipo' => 'desbravadores',
            'status' => 'ativos',
        ]);

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_gerar_relatorio_personalizado_caixa()
    {
        Caixa::create([
            'descricao' => 'Teste',
            'valor' => 50,
            'tipo' => 'entrada',
            'data_movimentacao' => now(),
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
