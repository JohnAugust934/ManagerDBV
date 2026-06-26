<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Ata;
use App\Models\Ato;
use App\Models\AttendanceColumn;
use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\RankingSnapshot;
use App\Models\RelatorioGerado;
use App\Services\ClubExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Completa a cobertura de isolamento por tenant nos pontos ainda não exercitados:
 * - update/destroy cross-clube em Evento e Desbravador (route-model binding → 404);
 * - prova de que a exportação (ClubExportService, todo via withoutGlobalScopes())
 *   reaplica o filtro club_id para TODAS as tabelas, não só desbravadores;
 * - Mensalidade::resolveClubIdFromParent() resolve o clube correto;
 * - RelatorioGerado é isolado pelo global scope.
 *
 * Complementa IsolamentoTenantTest, IsolamentoRecursosTest e IsolamentoSemGlobalScopeTest.
 */
class IsolamentoCoberturaCompletaTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Evento — update/destroy cross-clube
    // -------------------------------------------------------------------------

    public function test_usuario_nao_edita_evento_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $eventoB = Evento::factory()->forClube($clubB->id)->create(['nome' => 'Acampamento B']);

        $this->actingAs($masterA)
            ->put(route('eventos.update', $eventoB), [
                'nome' => 'Adulterado',
                'data_inicio' => now()->toDateString(),
                'data_fim' => now()->addDay()->toDateString(),
                'local' => 'X',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('eventos', ['id' => $eventoB->id, 'nome' => 'Acampamento B']);
        $this->assertDatabaseMissing('eventos', ['nome' => 'Adulterado']);
    }

    public function test_usuario_nao_exclui_evento_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $eventoB = Evento::factory()->forClube($clubB->id)->create();

        $this->actingAs($masterA)
            ->delete(route('eventos.destroy', $eventoB))
            ->assertNotFound();

        $this->assertDatabaseHas('eventos', ['id' => $eventoB->id]);
    }

    // -------------------------------------------------------------------------
    // Desbravador — update/destroy cross-clube
    // -------------------------------------------------------------------------

    public function test_usuario_nao_edita_desbravador_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'nome' => 'Original B']);

        $this->actingAs($masterA)
            ->put(route('desbravadores.update', $dbvB), [
                'nome' => 'Adulterado',
                'unidade_id' => $unidadeB->id,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('desbravadores', ['id' => $dbvB->id, 'nome' => 'Original B']);
        $this->assertDatabaseMissing('desbravadores', ['nome' => 'Adulterado']);
    }

    public function test_usuario_nao_exclui_desbravador_de_outro_clube(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        $this->actingAs($masterA)
            ->delete(route('desbravadores.destroy', $dbvB))
            ->assertNotFound();

        $this->assertDatabaseHas('desbravadores', ['id' => $dbvB->id]);
    }

    // -------------------------------------------------------------------------
    // ClubExportService — withoutGlobalScopes() reaplica club_id em TODAS as tabelas
    // -------------------------------------------------------------------------

    public function test_exportacao_isola_todas_as_tabelas_por_clube(): void
    {
        ['club' => $clubA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['club' => $clubB, 'unidade' => $unidadeB] = criarClubeComDados('Clube B');

        // Dados em ambos os clubes — só os de A devem sair na exportação de A.
        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'nome' => 'Dbv A']);
        Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'nome' => 'Dbv B']);

        Caixa::factory()->forClube($clubA->id)->create(['descricao' => 'Caixa A']);
        Caixa::factory()->forClube($clubB->id)->create(['descricao' => 'Caixa B']);

        Evento::factory()->forClube($clubA->id)->create(['nome' => 'Evento A']);
        Evento::factory()->forClube($clubB->id)->create(['nome' => 'Evento B']);

        Patrimonio::create(['club_id' => $clubA->id, 'item' => 'Patrim A', 'quantidade' => 1, 'valor_estimado' => 10, 'estado_conservacao' => 'bom']);
        Patrimonio::create(['club_id' => $clubB->id, 'item' => 'Patrim B', 'quantidade' => 1, 'valor_estimado' => 10, 'estado_conservacao' => 'bom']);

        Ata::factory()->forClube($clubA->id)->create(['titulo' => 'Ata A', 'hora_inicio' => '09:00', 'hora_fim' => '10:00', 'local' => 'X']);
        Ata::factory()->forClube($clubB->id)->create(['titulo' => 'Ata B', 'hora_inicio' => '09:00', 'hora_fim' => '10:00', 'local' => 'X']);

        Ato::factory()->forClube($clubA->id)->create(['descricao' => 'Ato A']);
        Ato::factory()->forClube($clubB->id)->create(['descricao' => 'Ato B']);

        Mensalidade::create(['desbravador_id' => $dbvA->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);

        AttendanceColumn::create(['club_id' => $clubA->id, 'key' => 'col_a', 'name' => 'Coluna A', 'points' => 5, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 9]);
        AttendanceColumn::create(['club_id' => $clubB->id, 'key' => 'col_b', 'name' => 'Coluna B', 'points' => 5, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 9]);

        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $clubA->id, 'entries' => [], 'generated_at' => now()]);
        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $clubB->id, 'entries' => [], 'generated_at' => now()]);

        $export = app(ClubExportService::class)->export($clubA);

        // Cada tabela exportada deve conter SÓ o club_id de A.
        $tabelasComClubId = ['unidades', 'desbravadores', 'caixas', 'mensalidades', 'eventos', 'atas', 'atos', 'patrimonios', 'attendance_columns', 'ranking_snapshots'];
        foreach ($tabelasComClubId as $tabela) {
            $clubIds = array_unique(array_column($export[$tabela], 'club_id'));
            $this->assertNotEmpty($export[$tabela], "Exportação de '{$tabela}' não deveria estar vazia");
            $this->assertSame([$clubA->id], array_values($clubIds), "Tabela '{$tabela}' vazou club_id de outro clube na exportação");
        }

        // Usuários (extraídos via DB direto) também só do clube A.
        $userClubIds = array_unique(array_column($export['users'], 'club_id'));
        $this->assertSame([$clubA->id], array_values($userClubIds), 'Usuários exportados vazaram outro clube');
    }

    // -------------------------------------------------------------------------
    // Mensalidade::resolveClubIdFromParent()
    // -------------------------------------------------------------------------

    public function test_mensalidade_resolve_club_id_pelo_desbravador(): void
    {
        ['club' => $clubA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['club' => $clubB, 'unidade' => $unidadeB] = criarClubeComDados('Clube B');

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        $mensA = Mensalidade::create(['desbravador_id' => $dbvA->id, 'mes' => 2, 'ano' => 2026, 'valor' => 20, 'status' => 'pendente']);
        $mensB = Mensalidade::create(['desbravador_id' => $dbvB->id, 'mes' => 2, 'ano' => 2026, 'valor' => 20, 'status' => 'pendente']);

        $this->assertEquals($clubA->id, $mensA->resolveClubIdFromParent());
        $this->assertEquals($clubB->id, $mensB->resolveClubIdFromParent());
        $this->assertNotEquals($clubA->id, $mensB->resolveClubIdFromParent());

        // E o club_id desnormalizado foi preenchido na criação a partir do pai.
        $this->assertEquals($clubA->id, $mensA->fresh()->club_id);
        $this->assertEquals($clubB->id, $mensB->fresh()->club_id);
    }

    // -------------------------------------------------------------------------
    // RelatorioGerado — isolado pelo global scope
    // -------------------------------------------------------------------------

    public function test_relatorio_gerado_isolado_por_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $relA = RelatorioGerado::create(['club_id' => $clubA->id, 'user_id' => $masterA->id, 'tipo' => 'financeiro', 'status' => 'pronto']);
        $relB = RelatorioGerado::create(['club_id' => $clubB->id, 'user_id' => $masterA->id, 'tipo' => 'financeiro', 'status' => 'pronto']);

        $this->actingAs($masterA);

        // Live (com scope): só enxerga o do próprio clube.
        $this->assertEquals([$relA->id], RelatorioGerado::pluck('id')->all());
        $this->assertNull(RelatorioGerado::find($relB->id));

        // O caminho do job (withoutGlobalScopes) ainda localiza por id específico,
        // sem possibilidade de vazamento em massa (é sempre um único id).
        $this->assertEquals($clubB->id, RelatorioGerado::withoutGlobalScopes()->find($relB->id)->club_id);
    }
}
