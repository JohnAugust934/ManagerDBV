<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformObservabilidadeTest extends TestCase
{
    use RefreshDatabase;

    private function platformAdmin(): User
    {
        return User::factory()->create([
            'club_id' => null,
            'is_platform_admin' => true,
            'role' => 'master',
        ]);
    }

    public function test_dashboard_plataforma_exibe_dados_operacionais(): void
    {
        Club::create(['nome' => 'Clube A', 'cidade' => 'SP', 'is_active' => true]);
        Club::create(['nome' => 'Clube B', 'cidade' => 'SP', 'is_active' => false]);

        $this->actingAs($this->platformAdmin())
            ->get(route('platform.index'))
            ->assertOk()
            ->assertSee('Observabilidade')
            ->assertSee('Clubes ativos')
            ->assertSee('Jobs na fila')
            ->assertSee('Jobs falhos (24h)');
    }

    public function test_dashboard_plataforma_exibe_versao_da_aplicacao(): void
    {
        $this->actingAs($this->platformAdmin())
            ->get(route('platform.index'))
            ->assertOk()
            ->assertSee('Versão');
    }

    public function test_dashboard_plataforma_exibe_link_para_health(): void
    {
        $this->actingAs($this->platformAdmin())
            ->get(route('platform.index'))
            ->assertOk()
            ->assertSee('/health');
    }

    public function test_dashboard_plataforma_contagem_clubes_ativos_correta(): void
    {
        Club::create(['nome' => 'Clube Ativo 1', 'cidade' => 'SP', 'is_active' => true]);
        Club::create(['nome' => 'Clube Ativo 2', 'cidade' => 'SP', 'is_active' => true]);
        Club::create(['nome' => 'Clube Inativo', 'cidade' => 'SP', 'is_active' => false]);

        $response = $this->actingAs($this->platformAdmin())
            ->get(route('platform.index'));

        $response->assertOk();
        // Verifica que os dados chegam à view via variável $operacional
        $response->assertViewHas('operacional');
        $operacional = $response->viewData('operacional');
        $this->assertSame(2, $operacional['clubesAtivos']);
        $this->assertSame(1, $operacional['clubesInativos']);
    }

    public function test_usuario_comum_nao_acessa_dashboard_plataforma(): void
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $this->actingAs($user)
            ->get(route('platform.index'))
            ->assertForbidden();
    }

    // -------------------------------------------------------------------------
    // Endpoint JSON de observabilidade
    // -------------------------------------------------------------------------

    public function test_endpoint_observabilidade_nega_acesso_a_nao_platform_admin(): void
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'master']);

        $this->actingAs($user)
            ->getJson(route('platform.observabilidade'))
            ->assertForbidden();
    }

    public function test_endpoint_observabilidade_retorna_contrato_de_dados(): void
    {
        $this->actingAs($this->platformAdmin())
            ->getJson(route('platform.observabilidade'))
            ->assertOk()
            ->assertJsonStructure([
                'queueSize',
                'falhasRecentes',
                'totalFalhas',
                'versao',
                'relatoriosPendentes',
                'clubesAtivos',
                'clubesInativos',
                'ultimoBackupPorClube',
                'queriesLentas',
            ]);
    }

    public function test_endpoint_observabilidade_traz_ultimo_backup_por_clube(): void
    {
        $clube = Club::create(['nome' => 'Clube Backup', 'cidade' => 'SP']);

        \App\Models\ClubBackupLog::create([
            'club_id' => $clube->id,
            'disk' => 'local',
            'path' => 'backups/clubes/clube-backup/antigo.zip',
            'filename' => 'antigo.zip',
            'status' => 'success',
            'size_bytes' => 100,
            'has_uploads' => false,
            'origin' => 'manual',
        ]);
        $recente = \App\Models\ClubBackupLog::create([
            'club_id' => $clube->id,
            'disk' => 'local',
            'path' => 'backups/clubes/clube-backup/recente.zip',
            'filename' => 'recente.zip',
            'status' => 'success',
            'size_bytes' => 200,
            'has_uploads' => true,
            'origin' => 'manual',
        ]);

        $dados = $this->actingAs($this->platformAdmin())
            ->getJson(route('platform.observabilidade'))
            ->assertOk()
            ->json('ultimoBackupPorClube');

        $this->assertCount(1, $dados);
        $this->assertSame($clube->id, $dados[0]['club_id']);
        $this->assertSame('recente.zip', $dados[0]['filename']);
        $this->assertSame('Clube Backup', $dados[0]['clube']);
    }

    public function test_endpoint_observabilidade_queries_lentas_null_quando_desabilitado(): void
    {
        // LOG_SLOW_QUERIES não habilitado no ambiente de teste → null.
        $this->actingAs($this->platformAdmin())
            ->getJson(route('platform.observabilidade'))
            ->assertOk()
            ->assertJson(['queriesLentas' => null]);
    }
}
