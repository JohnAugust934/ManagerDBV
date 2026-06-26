<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupExportTest extends TestCase
{
    use RefreshDatabase;

    private Club $clubA;

    private Club $clubB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubA = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $this->clubB = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
    }

    public function test_platform_admin_pode_acessar_backup_completo(): void
    {
        Storage::fake('local');
        Storage::fake('r2');

        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)->get(route('backups.index'))->assertOk();
    }

    public function test_master_de_clube_nao_pode_acessar_backup_completo(): void
    {
        $master = User::factory()->create(['role' => 'master', 'club_id' => $this->clubA->id]);

        $this->actingAs($master)->get(route('backups.index'))->assertForbidden();
    }

    public function test_exportacao_de_clube_contem_apenas_dados_daquele_clube(): void
    {
        $unidadeA = Unidade::factory()->create(['club_id' => $this->clubA->id, 'nome' => 'Unidade A']);
        $unidadeB = Unidade::factory()->create(['club_id' => $this->clubB->id, 'nome' => 'Unidade B']);

        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'nome' => 'Dbv A']);
        Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'nome' => 'Dbv B']);

        Caixa::create(['descricao' => 'Caixa A', 'valor' => 10, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubA->id]);
        Caixa::create(['descricao' => 'Caixa B', 'valor' => 20, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubB->id]);

        $admin = User::factory()->platformAdmin()->create();

        $response = $this->actingAs($admin)->get(route('platform.export', $this->clubA));
        $response->assertOk();

        $payload = json_decode($response->streamedContent(), true);

        $this->assertSame($this->clubA->id, $payload['meta']['club_id']);
        $this->assertSame(['Unidade A'], array_column($payload['unidades'], 'nome'));
        $this->assertSame(['Dbv A'], array_column($payload['desbravadores'], 'nome'));
        $this->assertSame(['Caixa A'], array_column($payload['caixas'], 'descricao'));

        // Nenhum dado do clube B vazou.
        $this->assertStringNotContainsString('Caixa B', $response->streamedContent());
        $this->assertStringNotContainsString('Unidade B', $response->streamedContent());
    }

    public function test_master_pode_exportar_os_proprios_dados(): void
    {
        $master = User::factory()->create(['role' => 'master', 'club_id' => $this->clubA->id]);

        Caixa::create(['descricao' => 'Caixa A', 'valor' => 10, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'categoria' => 'x', 'club_id' => $this->clubA->id]);

        $response = $this->actingAs($master)->get(route('club.export'));
        $response->assertOk();

        $payload = json_decode($response->streamedContent(), true);
        $this->assertSame($this->clubA->id, $payload['meta']['club_id']);
        $this->assertSame(['Caixa A'], array_column($payload['caixas'], 'descricao'));
    }

    public function test_diretor_nao_pode_exportar_dados_do_clube(): void
    {
        $diretor = User::factory()->create(['role' => 'diretor', 'club_id' => $this->clubA->id]);

        $this->actingAs($diretor)->get(route('club.export'))->assertForbidden();
    }
}
