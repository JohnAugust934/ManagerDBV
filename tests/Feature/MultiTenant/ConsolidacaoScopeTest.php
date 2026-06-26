<?php

namespace Tests\Feature\MultiTenant;

use App\Models\AttendanceColumn;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use App\Models\User;
use App\Services\ClubContext;
use App\Services\ClubExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Fase 4 — consolidação do isolamento via trait BelongsToTenant. Unidade,
 * AttendanceColumn e RankingSnapshot passam a ter global scope próprio (antes
 * eram filtrados à mão). Pivôs sem club_id ganham verificação de integridade.
 */
class ConsolidacaoScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_unidade_e_isolada_por_scope(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        criarClubeComDados('Clube B');

        $this->actingAs($masterA);

        // Cada clube criou uma unidade; o scope mostra só a do clube ativo.
        $this->assertSame(1, Unidade::count());
    }

    public function test_attendance_column_e_isolada_por_scope(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        AttendanceColumn::create(['club_id' => $clubA->id, 'key' => 'k', 'name' => 'X', 'points' => 1, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 1]);
        AttendanceColumn::create(['club_id' => $clubB->id, 'key' => 'k', 'name' => 'X', 'points' => 1, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($masterA);
        $this->assertSame(1, AttendanceColumn::count());
    }

    public function test_ranking_snapshot_e_isolado_por_scope(): void
    {
        ['club' => $clubA, 'master' => $masterA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $clubA->id, 'entries' => [], 'generated_at' => now()]);
        RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => $clubB->id, 'entries' => [], 'generated_at' => now()]);

        $this->actingAs($masterA);
        $this->assertSame(1, RankingSnapshot::count());
    }

    public function test_export_traz_o_clube_alvo_mesmo_impersonando_outro(): void
    {
        ['club' => $clubA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'nome' => 'Fulano A']);

        $admin = User::factory()->platformAdmin()->create(['club_id' => null]);
        $this->actingAs($admin);
        session([ClubContext::SESSION_KEY => $clubB->id]); // impersonando o clube B

        // Exporta o clube A — deve trazer os dados do A (via club_id direto),
        // sem ser filtrado pelo scope do clube impersonado (B).
        $data = app(ClubExportService::class)->export($clubA);

        $this->assertCount(1, $data['desbravadores']);
        $this->assertSame('Fulano A', $data['desbravadores'][0]['nome']);
    }

    public function test_integridade_detecta_inscricao_cross_club(): void
    {
        ['unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['club' => $clubB] = criarClubeComDados('Clube B');

        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]); // clube A
        $eventoB = Evento::factory()->forClube($clubB->id)->create();           // clube B

        // Inscrição forjada ligando desbravador do A a evento do B (vazamento).
        DB::table('desbravador_evento')->insert([
            'desbravador_id' => $dbvA->id,
            'evento_id' => $eventoB->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('tenant:check-integrity')->assertExitCode(1);
    }
}
