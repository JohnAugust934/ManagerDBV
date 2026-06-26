<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Ata;
use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IsolamentoBasicoTest extends TestCase
{
    use RefreshDatabase;

    private Club $clubA;

    private Club $clubB;

    private User $userA;

    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubA = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $this->clubB = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);

        $this->userA = User::factory()->create(['club_id' => $this->clubA->id, 'role' => 'diretor']);
        $this->userB = User::factory()->create(['club_id' => $this->clubB->id, 'role' => 'diretor']);
    }

    public function test_caixa_e_isolado_por_clube(): void
    {
        Caixa::create(['descricao' => 'Caixa A', 'valor' => 10, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubA->id]);
        Caixa::create(['descricao' => 'Caixa B', 'valor' => 20, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubB->id]);

        $this->actingAs($this->userA);
        $this->assertSame(['Caixa A'], Caixa::pluck('descricao')->all());

        $this->actingAs($this->userB);
        $this->assertSame(['Caixa B'], Caixa::pluck('descricao')->all());
    }

    public function test_ata_e_isolada_por_clube(): void
    {
        $base = ['data_reuniao' => now(), 'tipo' => 'Regular', 'hora_inicio' => '09:00', 'hora_fim' => '11:00', 'local' => 'Sede', 'conteudo' => 'Conteúdo da ata.'];
        Ata::create([...$base, 'titulo' => 'Ata A', 'club_id' => $this->clubA->id]);
        Ata::create([...$base, 'titulo' => 'Ata B', 'club_id' => $this->clubB->id]);

        $this->actingAs($this->userA);
        $this->assertSame(['Ata A'], Ata::pluck('titulo')->all());
    }

    public function test_evento_e_isolado_por_clube(): void
    {
        Evento::create(['nome' => 'Evento A', 'data_inicio' => now(), 'data_fim' => now(), 'local' => 'x', 'valor' => 0, 'club_id' => $this->clubA->id]);
        Evento::create(['nome' => 'Evento B', 'data_inicio' => now(), 'data_fim' => now(), 'local' => 'x', 'valor' => 0, 'club_id' => $this->clubB->id]);

        $this->actingAs($this->userA);
        $this->assertSame(['Evento A'], Evento::pluck('nome')->all());
    }

    public function test_patrimonio_e_isolado_por_clube(): void
    {
        Patrimonio::create(['item' => 'Item A', 'quantidade' => 1, 'valor_estimado' => 1, 'estado_conservacao' => 'Bom', 'local_armazenamento' => 'x', 'club_id' => $this->clubA->id]);
        Patrimonio::create(['item' => 'Item B', 'quantidade' => 1, 'valor_estimado' => 1, 'estado_conservacao' => 'Bom', 'local_armazenamento' => 'x', 'club_id' => $this->clubB->id]);

        $this->actingAs($this->userA);
        $this->assertSame(['Item A'], Patrimonio::pluck('item')->all());
    }

    public function test_mensalidade_e_isolada_por_clube_via_global_scope(): void
    {
        $unidadeA = Unidade::factory()->create(['club_id' => $this->clubA->id]);
        $unidadeB = Unidade::factory()->create(['club_id' => $this->clubB->id]);

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        Mensalidade::create(['desbravador_id' => $dbvA->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);
        Mensalidade::create(['desbravador_id' => $dbvB->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);

        $this->actingAs($this->userA);
        $this->assertEquals([$dbvA->id], Mensalidade::pluck('desbravador_id')->all());

        $this->actingAs($this->userB);
        $this->assertEquals([$dbvB->id], Mensalidade::pluck('desbravador_id')->all());
    }

    public function test_ranking_snapshot_do_mesmo_ano_nao_conflita_entre_clubes(): void
    {
        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $this->clubA->id, 'entries' => [], 'generated_at' => now()]);
        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $this->clubB->id, 'entries' => [], 'generated_at' => now()]);

        $this->assertDatabaseCount('ranking_snapshots', 2);
    }
}
