<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use App\Services\ClubBackupService;
use App\Services\ClubContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClubBackupTest extends TestCase
{
    use RefreshDatabase;

    private Club $clubA;

    private Club $clubB;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('r2');

        $this->clubA = Club::create(['nome' => 'Clube Alpha', 'cidade' => 'SP']);
        $this->clubB = Club::create(['nome' => 'Clube Beta', 'cidade' => 'RJ']);
    }

    // ── Autorização ──────────────────────────────────────────────────────────

    public function test_master_pode_acessar_backups_do_proprio_clube(): void
    {
        $master = User::factory()->create(['role' => 'master', 'club_id' => $this->clubA->id]);

        $this->actingAs($master)->get(route('club-backups.index'))->assertOk();
    }

    public function test_platform_admin_impersonando_e_redirecionado_para_backup_de_clube(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        // Ao acessar /backups (backup do sistema) enquanto impersonando, deve redirecionar
        $this->actingAs($admin)
            ->withSession([ClubContext::SESSION_KEY => $this->clubA->id])
            ->get(route('backups.index'))
            ->assertRedirect(route('club-backups.index'));
    }

    public function test_diretor_nao_pode_acessar_backup_do_clube(): void
    {
        $diretor = User::factory()->create(['role' => 'diretor', 'club_id' => $this->clubA->id]);

        $this->actingAs($diretor)->get(route('club-backups.index'))->assertForbidden();
    }

    public function test_platform_admin_sem_clube_nao_pode_acessar_backup_de_clube(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)->get(route('club-backups.index'))->assertForbidden();
    }

    public function test_platform_admin_impersonando_clube_pode_acessar_backups(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->withSession([ClubContext::SESSION_KEY => $this->clubA->id])
            ->get(route('club-backups.index'))
            ->assertOk();
    }

    // ── Geração de backup ────────────────────────────────────────────────────

    public function test_master_pode_gerar_backup_do_proprio_clube(): void
    {
        $master = User::factory()->create(['role' => 'master', 'club_id' => $this->clubA->id]);

        $unidade = Unidade::factory()->create(['club_id' => $this->clubA->id]);
        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'club_id' => $this->clubA->id, 'nome' => 'João A']);
        Caixa::create(['descricao' => 'Entrada A', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'mensalidade', 'club_id' => $this->clubA->id]);

        $this->actingAs($master)
            ->post(route('club-backups.store'))
            ->assertRedirect()
            ->assertSessionHas('success');

        // Verifica que o ZIP foi criado no disco local
        $files = Storage::disk('local')->allFiles("backups/clubes/clube-alpha");
        $this->assertNotEmpty($files);
        $this->assertTrue(str_ends_with($files[0], '.zip'));
    }

    // ── Isolamento: backup de clube A não vaza dados de B ────────────────────

    public function test_backup_gerado_contem_apenas_dados_do_clube(): void
    {
        $unidadeA = Unidade::factory()->create(['club_id' => $this->clubA->id]);
        $unidadeB = Unidade::factory()->create(['club_id' => $this->clubB->id]);
        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'club_id' => $this->clubA->id, 'nome' => 'Dbv Alpha']);
        Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'club_id' => $this->clubB->id, 'nome' => 'Dbv Beta']);

        $service = app(ClubBackupService::class);
        $result = $service->backup($this->clubA);

        $this->assertSame('success', $result['status']);

        // Extrai o ZIP e verifica o data.json
        $zipPath = Storage::disk('local')->path($result['path']);
        $zip = new \ZipArchive;
        $this->assertTrue($zip->open($zipPath) === true);
        $json = $zip->getFromName('data.json');
        $zip->close();

        $data = json_decode($json, true);

        // Dados do clube A estão presentes
        $this->assertSame($this->clubA->id, $data['meta']['club_id']);
        $this->assertContains('Dbv Alpha', array_column($data['desbravadores'], 'nome'));

        // Nenhum dado do clube B
        $this->assertNotContains('Dbv Beta', array_column($data['desbravadores'], 'nome'));
        $this->assertStringNotContainsString('Dbv Beta', $json);
    }

    // ── Segurança de path ─────────────────────────────────────────────────────

    public function test_master_nao_pode_baixar_backup_de_outro_clube(): void
    {
        $masterA = User::factory()->create(['role' => 'master', 'club_id' => $this->clubA->id]);

        // Arquivo que pertenceria ao clube B
        $pathB = 'backups/clubes/clube-beta/club-clube-beta-2026-01-01-00-00-00.zip';
        Storage::disk('local')->put($pathB, 'fake');

        $this->actingAs($masterA)
            ->get(route('club-backups.download', ['disk' => 'local', 'path' => $pathB]))
            ->assertStatus(500); // assertBelongsToClub lança RuntimeException → 500
    }

    public function test_path_traversal_e_bloqueado(): void
    {
        $master = User::factory()->create(['role' => 'master', 'club_id' => $this->clubA->id]);

        $this->actingAs($master)
            ->get(route('club-backups.download', ['disk' => 'local', 'path' => '../../../etc/passwd']))
            ->assertStatus(500);
    }

    // ── Log de backup ─────────────────────────────────────────────────────────

    public function test_backup_cria_registro_em_club_backup_logs(): void
    {
        $service = app(ClubBackupService::class);
        $service->backup($this->clubA, origin: 'manual', createdBy: null);

        $this->assertDatabaseHas('club_backup_logs', [
            'club_id' => $this->clubA->id,
            'status' => 'success',
            'origin' => 'manual',
            'disk' => 'local',
        ]);
    }
}
