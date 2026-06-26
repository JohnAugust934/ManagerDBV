<?php

namespace Tests\Feature\MultiTenant;

use App\Models\AttendanceColumn;
use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use App\Models\User;
use App\Services\ClubLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Fase 6 — exclusão definitiva de clube (cascade portável em nível de aplicação).
 * Garante que apagar um clube remove TODOS os seus dados (sem órfãos) e não toca
 * em outro clube. A exclusão exige que o clube esteja desativado.
 */
class ClubDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_exclui_clube_e_todos_os_dados_sem_orfaos(): void
    {
        ['club' => $clubA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['club' => $clubB, 'unidade' => $unidadeB] = criarClubeComDados('Clube B');

        // Dados ricos no clube A (incl. pivôs e filhos sem club_id próprio).
        $dbvA = Desbravador::factory()->create(['unidade_id' => $unidadeA->id]);
        $col = AttendanceColumn::create(['club_id' => $clubA->id, 'key' => 'k', 'name' => 'X', 'points' => 1, 'is_fixed' => false, 'is_active' => true, 'sort_order' => 1]);
        $freqA = Frequencia::create(['desbravador_id' => $dbvA->id, 'data' => now()]);
        $freqA->columnValues()->create(['attendance_column_id' => $col->id, 'checked' => true, 'points_awarded' => 1]);
        Mensalidade::create(['desbravador_id' => $dbvA->id, 'mes' => 1, 'ano' => 2026, 'valor' => 15, 'status' => 'pendente']);
        Caixa::factory()->forClube($clubA->id)->create();
        $eventoA = Evento::factory()->forClube($clubA->id)->create();
        $dbvA->eventos()->attach($eventoA->id);

        // Dado no clube B que deve permanecer intacto.
        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);

        $clubA->update(['is_active' => false]);
        app(ClubLifecycleService::class)->delete($clubA);

        // Clube A e tudo dele sumiu.
        $this->assertDatabaseMissing('clubs', ['id' => $clubA->id]);
        foreach (['desbravadores', 'frequencias', 'mensalidades', 'caixas', 'eventos', 'unidades', 'attendance_columns'] as $tabela) {
            $this->assertSame(0, DB::table($tabela)->where('club_id', $clubA->id)->count(), "Sobrou linha em {$tabela}");
        }
        $this->assertSame(0, DB::table('desbravador_evento')->where('desbravador_id', $dbvA->id)->count());
        $this->assertSame(0, DB::table('frequencia_column_values')->where('frequencia_id', $freqA->id)->count());

        // Clube B intacto.
        $this->assertDatabaseHas('clubs', ['id' => $clubB->id]);
        $this->assertSame(1, DB::table('desbravadores')->where('club_id', $clubB->id)->count());

        // Nenhum órfão deixado para trás.
        $this->artisan('tenant:check-integrity')->assertExitCode(0);
    }

    public function test_nao_exclui_clube_ainda_ativo(): void
    {
        $admin = User::factory()->platformAdmin()->create(['club_id' => null]);
        ['club' => $club] = criarClubeComDados('Clube A'); // ativo

        $this->actingAs($admin)->delete(route('platform.clubs.destroy', $club))
            ->assertRedirect(route('platform.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('clubs', ['id' => $club->id]);
    }

    public function test_usuario_comum_nao_pode_excluir_clube(): void
    {
        ['club' => $clubA] = criarClubeComDados('Clube A');
        $clubA->update(['is_active' => false]);

        // Master de OUTRO clube (ativo) — passa pelo EnsureClubIsActive, barra no gate.
        ['master' => $masterB] = criarClubeComDados('Clube B');

        $this->actingAs($masterB)->delete(route('platform.clubs.destroy', $clubA))->assertForbidden();
        $this->assertDatabaseHas('clubs', ['id' => $clubA->id]);
    }
}
