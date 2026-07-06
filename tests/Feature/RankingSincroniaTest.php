<?php

namespace Tests\Feature;

use App\Models\AttendanceColumn;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use App\Providers\AppServiceProvider;
use App\Services\RankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Paridade entre as DUAS implementações de pontuação do ranking:
 * - snapshot de console (AppServiceProvider::snapshotRankingYear, itera por clube);
 * - ranking ao vivo (RankingController::unidades/desbravadores).
 *
 * Para os mesmos dados, ambas devem produzir a MESMA ordenação e a MESMA pontuação.
 * Cobre também a exclusão de unidades fora do ranking (no_ranking = false) e o
 * isolamento por club_id.
 *
 * Cenário sem empates de pontuação para que a ordenação seja determinística entre
 * as duas implementações (a do controller não aplica orderBy('nome') prévio).
 */
class RankingSincroniaTest extends TestCase
{
    use RefreshDatabase;

    private function freq(int $dbvId, bool $p, bool $pt, bool $b, bool $u): void
    {
        Frequencia::create([
            'desbravador_id' => $dbvId,
            'data' => now(),
            'presente' => $p,
            'pontual' => $pt,
            'biblia' => $b,
            'uniforme' => $u,
        ]);
    }

    public function test_snapshot_e_controller_produzem_ranking_identico(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');

        // Unidades participantes (no_ranking = true participa).
        $alfa = Unidade::factory()->create(['nome' => 'Alfa', 'club_id' => $clubA->id, 'no_ranking' => true]);
        $beta = Unidade::factory()->create(['nome' => 'Beta', 'club_id' => $clubA->id, 'no_ranking' => true]);
        $gama = Unidade::factory()->create(['nome' => 'Gama', 'club_id' => $clubA->id, 'no_ranking' => true]);
        // Unidade fora do ranking — não deve aparecer em nenhuma das saídas.
        $fora = Unidade::factory()->create(['nome' => 'Fora', 'club_id' => $clubA->id, 'no_ranking' => false]);

        // Pontuações distintas: 30, 25, 20, 15, 10 (sem empates).
        $a1 = Desbravador::factory()->create(['unidade_id' => $alfa->id, 'nome' => 'A1', 'ativo' => true]);
        $a2 = Desbravador::factory()->create(['unidade_id' => $alfa->id, 'nome' => 'A2', 'ativo' => true]);
        $g1 = Desbravador::factory()->create(['unidade_id' => $gama->id, 'nome' => 'G1', 'ativo' => true]);
        $g2 = Desbravador::factory()->create(['unidade_id' => $gama->id, 'nome' => 'G2', 'ativo' => true]);
        $b1 = Desbravador::factory()->create(['unidade_id' => $beta->id, 'nome' => 'B1', 'ativo' => true]);
        $foraDbv = Desbravador::factory()->create(['unidade_id' => $fora->id, 'nome' => 'X', 'ativo' => true]);

        $this->freq($a1->id, true, true, true, true);    // 30
        $this->freq($g1->id, true, true, false, true);   // 25
        $this->freq($b1->id, true, false, false, true);  // 20
        $this->freq($a2->id, true, true, false, false);  // 15
        $this->freq($g2->id, true, false, false, false); // 10
        $this->freq($foraDbv->id, true, true, true, true); // excluído

        // Totais por unidade: Alfa 45, Gama 35, Beta 20.

        // --- Caminho de snapshot (console) ---
        AppServiceProvider::snapshotRankingYear(now()->year, $clubA->id);

        $snapUnidades = RankingSnapshot::where('club_id', $clubA->id)->where('scope', 'unidades')->firstOrFail()->entries;
        $snapMembros = RankingSnapshot::where('club_id', $clubA->id)->where('scope', 'desbravadores')->firstOrFail()->entries;

        $snapUnidadesPontos = collect($snapUnidades)->mapWithKeys(fn ($e) => [$e['id'] => $e['pontos']]);
        $snapUnidadesOrdem = array_column($snapUnidades, 'id');
        $snapMembrosPontos = collect($snapMembros)->mapWithKeys(fn ($e) => [$e['id'] => $e['pontos']]);
        $snapMembrosOrdem = array_column($snapMembros, 'id');

        // --- Caminho ao vivo (controller) ---
        $this->actingAs($masterA);

        $dataUnidades = $this->get(route('ranking.unidades'))->assertOk()->viewData('data');
        $dataMembros = $this->get(route('ranking.desbravadores'))->assertOk()->viewData('data');

        $ctrlUnidadesOrdem = $dataUnidades->pluck('id')->all();
        $ctrlUnidadesPontos = $dataUnidades->mapWithKeys(fn ($u) => [$u->id => $u->pontos]);
        $ctrlMembrosOrdem = $dataMembros->pluck('id')->all();
        $ctrlMembrosPontos = $dataMembros->mapWithKeys(fn ($d) => [$d->id => $d->pontos]);

        // --- Paridade: mesma ordenação ---
        $this->assertSame($snapUnidadesOrdem, $ctrlUnidadesOrdem, 'Ordem das unidades diverge entre snapshot e controller');
        $this->assertSame($snapMembrosOrdem, $ctrlMembrosOrdem, 'Ordem dos desbravadores diverge entre snapshot e controller');

        // --- Paridade: mesma pontuação por entrada ---
        foreach ($snapUnidadesPontos as $id => $pts) {
            $this->assertEquals($pts, $ctrlUnidadesPontos[$id], "Pontuação da unidade {$id} diverge");
        }
        foreach ($snapMembrosPontos as $id => $pts) {
            $this->assertEquals($pts, $ctrlMembrosPontos[$id], "Pontuação do desbravador {$id} diverge");
        }

        // Valores absolutos esperados (sanidade). A unidade vazia criada pelo helper
        // (0 pontos) entra em ambas as saídas; verificamos a ordem relativa das
        // unidades pontuadas e a pontuação absoluta.
        $ordemPontuadas = array_values(array_intersect($ctrlUnidadesOrdem, [$alfa->id, $beta->id, $gama->id]));
        $this->assertSame([$alfa->id, $gama->id, $beta->id], $ordemPontuadas);
        $this->assertSame(45, (int) $ctrlUnidadesPontos[$alfa->id]);
        $this->assertSame([$a1->id, $g1->id, $b1->id, $a2->id, $g2->id], $ctrlMembrosOrdem);

        // --- Exclusão de no_ranking em ambos ---
        $this->assertNotContains($fora->id, $snapUnidadesOrdem);
        $this->assertNotContains($fora->id, $ctrlUnidadesOrdem);
        $this->assertNotContains($foraDbv->id, $snapMembrosOrdem);
        $this->assertNotContains($foraDbv->id, $ctrlMembrosOrdem);
    }

    /**
     * Cobre o caminho do modo NOVO (attendance_columns + frequencia_column_values),
     * incluindo o gatilho da divergência histórica: uma coluna com
     * points_awarded > 0 porém checked = false NÃO deve pontuar. Todas as fontes
     * (Frequencia::pontos, RankingService, snapshot e telas ao vivo) precisam
     * concordar que só o que está marcado conta.
     */
    public function test_column_values_nao_marcados_nao_pontuam_em_nenhuma_fonte(): void
    {
        ['club' => $club, 'master' => $master] = criarClubeComDados('Clube A');

        $unidade = Unidade::factory()->create(['nome' => 'Alfa', 'club_id' => $club->id, 'no_ranking' => true]);
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'nome' => 'A1', 'ativo' => true]);

        $presente = AttendanceColumn::create([
            'club_id' => $club->id, 'key' => 'presente', 'name' => 'Presente',
            'points' => 10, 'is_fixed' => true, 'is_active' => true, 'sort_order' => 10,
        ]);
        $biblia = AttendanceColumn::create([
            'club_id' => $club->id, 'key' => 'biblia', 'name' => 'Biblia',
            'points' => 5, 'is_fixed' => true, 'is_active' => true, 'sort_order' => 30,
        ]);

        $frequencia = Frequencia::create([
            'desbravador_id' => $dbv->id, 'data' => now(),
            'presente' => true, 'pontual' => false, 'biblia' => false, 'uniforme' => false,
        ]);

        // Presente marcado (conta 10).
        $frequencia->columnValues()->create([
            'attendance_column_id' => $presente->id, 'checked' => true, 'points_awarded' => 10,
        ]);
        // Biblia NÃO marcada, mas com points_awarded = 5 (dado inconsistente): não deve contar.
        $frequencia->columnValues()->create([
            'attendance_column_id' => $biblia->id, 'checked' => false, 'points_awarded' => 5,
        ]);

        // 1) Acessor do model, com columnValues.column carregado (caminho do serviço).
        $frequencia->load('columnValues.column');
        $this->assertSame(10, $frequencia->pontos, 'Frequencia::pontos deve ignorar column_values não marcados');
        $this->assertSame(10, $frequencia->detalhePontos()['total']);
        $this->assertSame(10, $frequencia->detalhePontos()['presente']);
        $this->assertSame(0, $frequencia->detalhePontos()['biblia']);

        // 2) RankingService (fonte da verdade única).
        $service = app(RankingService::class);
        $this->assertSame(10, $service->unidades($club->id, now()->year)->firstWhere('id', $unidade->id)['pontos']);
        $this->assertSame(10, $service->desbravadores($club->id, now()->year)->firstWhere('id', $dbv->id)['pontos']);

        // 3) Snapshot de console.
        AppServiceProvider::snapshotRankingYear(now()->year, $club->id);
        $snapUnidades = RankingSnapshot::where('club_id', $club->id)->where('scope', 'unidades')->firstOrFail()->entries;
        $this->assertSame(10, collect($snapUnidades)->firstWhere('id', $unidade->id)['pontos']);

        // 4) Telas ao vivo.
        $this->actingAs($master);
        $dataUnidades = $this->get(route('ranking.unidades'))->assertOk()->viewData('data');
        $this->assertSame(10, (int) $dataUnidades->firstWhere('id', $unidade->id)->pontos);
    }

    public function test_paridade_respeita_isolamento_por_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $unidadeA = Unidade::factory()->create(['nome' => 'UA', 'club_id' => $clubA->id, 'no_ranking' => true]);
        $unidadeB = Unidade::factory()->create(['nome' => 'UB', 'club_id' => $clubB->id, 'no_ranking' => true]);

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'nome' => 'DA', 'ativo' => true]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'nome' => 'DB', 'ativo' => true]);

        $this->freq($dbvA->id, true, true, true, true);
        $this->freq($dbvB->id, true, true, true, true);

        AppServiceProvider::snapshotRankingYear(now()->year, $clubA->id);
        $snapMembros = RankingSnapshot::where('club_id', $clubA->id)->where('scope', 'desbravadores')->firstOrFail()->entries;
        $snapIds = array_column($snapMembros, 'id');

        $this->actingAs($masterA);
        $ctrlIds = $this->get(route('ranking.desbravadores'))->assertOk()->viewData('data')->pluck('id')->all();

        // Snapshot e controller do clube A só enxergam o desbravador de A.
        $this->assertSame([$dbvA->id], $snapIds);
        $this->assertSame([$dbvA->id], $ctrlIds);
        $this->assertNotContains($dbvB->id, $snapIds);
        $this->assertNotContains($dbvB->id, $ctrlIds);
    }
}
