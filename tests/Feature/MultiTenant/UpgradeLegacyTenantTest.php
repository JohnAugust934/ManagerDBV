<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\RankingSnapshot;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Simula um banco single-tenant (v4.0.0-beta) com linhas órfãs (club_id NULL)
 * e valida a migração in-place para multi-tenant sem perda de dados.
 */
class UpgradeLegacyTenantTest extends TestCase
{
    use RefreshDatabase;

    private function cenarioLegado(): array
    {
        $club = Club::create(['nome' => 'Clube Legado', 'cidade' => 'SP']);

        // Master legado: club_id NULL (via fail-open antigo, enxergava tudo).
        $masterLegado = User::factory()->create([
            'email' => 'master@legado.com',
            'role' => 'master',
            'is_master' => true,
            'club_id' => null,
        ]);

        // Usuário órfão comum (não será platform admin).
        $diretorOrfao = User::factory()->create([
            'email' => 'diretor@legado.com',
            'role' => 'diretor',
            'club_id' => null,
        ]);

        // Dados órfãos (club_id NULL) que ficariam invisíveis sob fail-closed.
        $unidade = Unidade::factory()->create(['club_id' => null]);
        $desbravador = Desbravador::factory()->create(['unidade_id' => $unidade->id]);
        $caixa = Caixa::factory()->create(['club_id' => null, 'descricao' => 'Mov Legada']);
        $snapshot = RankingSnapshot::create(['year' => 2025, 'scope' => 'unidades', 'club_id' => null, 'entries' => [], 'generated_at' => now()]);

        return compact('club', 'masterLegado', 'diretorOrfao', 'unidade', 'caixa', 'snapshot');
    }

    public function test_upgrade_faz_backfill_e_define_papeis(): void
    {
        ['club' => $club, 'masterLegado' => $master, 'diretorOrfao' => $diretor, 'unidade' => $unidade, 'caixa' => $caixa, 'snapshot' => $snapshot] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])
            ->assertExitCode(0);

        // Backfill: nada mais órfão.
        $this->assertSame(0, DB::table('caixas')->whereNull('club_id')->count());
        $this->assertSame(0, DB::table('unidades')->whereNull('club_id')->count());
        $this->assertSame(0, DB::table('ranking_snapshots')->whereNull('club_id')->count());

        $this->assertSame($club->id, $caixa->fresh()->club_id);
        $this->assertSame($club->id, $unidade->fresh()->club_id);
        $this->assertSame($club->id, $snapshot->fresh()->club_id);

        // Master legado vira platform admin (sem clube).
        $master->refresh();
        $this->assertTrue($master->is_platform_admin);
        $this->assertNull($master->club_id);

        // Diretor órfão é vinculado ao clube (não fica travado).
        $this->assertSame($club->id, $diretor->fresh()->club_id);
        $this->assertFalse($diretor->fresh()->is_platform_admin);
    }

    public function test_dados_ficam_visiveis_para_o_master_apos_upgrade(): void
    {
        ['club' => $club, 'diretorOrfao' => $diretor] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])->assertExitCode(0);

        // O diretor (agora vinculado ao clube) enxerga a movimentação legada.
        $this->actingAs($diretor->fresh());
        $this->assertSame(['Mov Legada'], Caixa::pluck('descricao')->all());
    }

    public function test_dry_run_nao_grava_nada(): void
    {
        ['caixa' => $caixa, 'masterLegado' => $master] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com'], '--dry-run' => true])
            ->assertExitCode(0);

        $this->assertNull($caixa->fresh()->club_id);
        $this->assertFalse($master->fresh()->is_platform_admin);
    }

    public function test_falha_quando_ha_multiplos_clubes_sem_especificar(): void
    {
        Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);

        $this->artisan('tenant:upgrade-legacy')->assertExitCode(1);
    }

    public function test_nao_vincula_platform_admin_existente_ao_clube(): void
    {
        $club = Club::create(['nome' => 'Clube Legado', 'cidade' => 'SP']);

        // Já existe um platform admin (cenário de re-execução ou pós-seed).
        $admin = User::factory()->platformAdmin()->create(['email' => 'super@plataforma.com']);

        $this->artisan('tenant:upgrade-legacy')->assertExitCode(0);

        // Platform admin permanece sem clube (cross-tenant).
        $admin->refresh();
        $this->assertTrue($admin->is_platform_admin);
        $this->assertNull($admin->club_id);
    }

    public function test_idempotente_segunda_execucao_nao_altera(): void
    {
        ['club' => $club] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])->assertExitCode(0);
        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])->assertExitCode(0);

        $this->assertSame(0, DB::table('caixas')->whereNull('club_id')->count());
        $this->assertSame(1, User::where('is_platform_admin', true)->count());
    }
}
