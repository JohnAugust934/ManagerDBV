<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Valida o `tenant:upgrade-legacy` na sua responsabilidade ATUAL: resolver os
 * papéis de usuário (platform admin) e vincular usuários órfãos ao clube.
 *
 * Observação: a cura de linhas de tenant órfãs (club_id NULL) de um banco legado
 * single-tenant passou a ser feita pela própria migration de Fase 2
 * (`enforce_club_id_integrity`, via curarOuAbortar) — por isso, e porque o schema
 * de teste já é NOT NULL, não é possível (nem necessário) simular linhas de tenant
 * com club_id nulo aqui. users.club_id permanece nullable e é o que o comando trata.
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

        // Dado já vinculado ao clube (pós Fase 2 não há tenant órfão).
        $caixa = Caixa::factory()->forClube($club->id)->create(['descricao' => 'Mov Legada']);

        return compact('club', 'masterLegado', 'diretorOrfao', 'caixa');
    }

    public function test_upgrade_define_papeis_e_vincula_usuarios_orfaos(): void
    {
        ['club' => $club, 'masterLegado' => $master, 'diretorOrfao' => $diretor] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])
            ->assertExitCode(0);

        // Master legado vira platform admin (sem clube).
        $master->refresh();
        $this->assertTrue($master->is_platform_admin);
        $this->assertNull($master->club_id);

        // Diretor órfão é vinculado ao clube (não fica travado pelo fail-closed).
        $this->assertSame($club->id, $diretor->fresh()->club_id);
        $this->assertFalse($diretor->fresh()->is_platform_admin);

        // Nenhum usuário comum permanece órfão.
        $this->assertSame(0, User::whereNull('club_id')->where('is_platform_admin', false)->count());
    }

    public function test_dados_ficam_visiveis_para_o_diretor_apos_upgrade(): void
    {
        ['diretorOrfao' => $diretor] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])->assertExitCode(0);

        // O diretor (agora vinculado ao clube) enxerga a movimentação do clube.
        $this->actingAs($diretor->fresh());
        $this->assertSame(['Mov Legada'], Caixa::pluck('descricao')->all());
    }

    public function test_dry_run_nao_grava_nada(): void
    {
        ['masterLegado' => $master, 'diretorOrfao' => $diretor] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com'], '--dry-run' => true])
            ->assertExitCode(0);

        $this->assertFalse($master->fresh()->is_platform_admin);
        $this->assertNull($diretor->fresh()->club_id);
    }

    public function test_falha_quando_ha_multiplos_clubes_sem_especificar(): void
    {
        Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);

        $this->artisan('tenant:upgrade-legacy')->assertExitCode(1);
    }

    public function test_nao_vincula_platform_admin_existente_ao_clube(): void
    {
        Club::create(['nome' => 'Clube Legado', 'cidade' => 'SP']);

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
        ['club' => $club, 'diretorOrfao' => $diretor] = $this->cenarioLegado();

        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])->assertExitCode(0);
        $this->artisan('tenant:upgrade-legacy', ['--platform-admin' => ['master@legado.com']])->assertExitCode(0);

        $this->assertSame($club->id, $diretor->fresh()->club_id);
        $this->assertSame(1, User::where('is_platform_admin', true)->count());
        $this->assertSame(0, DB::table('users')->whereNull('club_id')->where('is_platform_admin', false)->count());
    }
}
