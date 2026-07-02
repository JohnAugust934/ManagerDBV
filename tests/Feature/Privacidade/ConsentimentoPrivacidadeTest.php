<?php

namespace Tests\Feature\Privacidade;

use App\Models\ConsentimentoPrivacidade;
use App\Models\Desbravador;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConsentimentoPrivacidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_aceitar_cria_registro_ativo(): void
    {
        ['master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'nome_responsavel' => 'Maria Mãe']);

        $this->actingAs($master)
            ->post(route('privacidade.aceitar', $dbv), ['versao_termo' => '2026.1'])
            ->assertSessionHas('success');

        $consentimento = ConsentimentoPrivacidade::first();
        $this->assertNotNull($consentimento);
        $this->assertTrue($consentimento->estaAtivo());
        $this->assertSame('Maria Mãe', $consentimento->responsavel_nome);
        $this->assertSame('2026.1', $consentimento->versao_termo);
        $this->assertNotEmpty($consentimento->termo_snapshot);
        $this->assertTrue($dbv->fresh()->consentimento_lgpd);
    }

    public function test_revogar_nao_apaga_registro_anterior(): void
    {
        ['master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $this->actingAs($master)->post(route('privacidade.aceitar', $dbv), ['versao_termo' => '2026.1']);
        $consentimento = ConsentimentoPrivacidade::first();
        $aceitoOriginal = $consentimento->aceito_em;

        $this->actingAs($master)
            ->post(route('privacidade.revogar', [$dbv, $consentimento]), ['motivo_revogacao' => 'Pedido do responsável'])
            ->assertSessionHas('success');

        $consentimento->refresh();
        // O histórico permanece: mesma linha, aceite intacto, apenas encerrada.
        $this->assertSame(1, ConsentimentoPrivacidade::count());
        $this->assertEquals($aceitoOriginal, $consentimento->aceito_em);
        $this->assertNotNull($consentimento->revogado_em);
        $this->assertSame('Pedido do responsável', $consentimento->motivo_revogacao);
        $this->assertFalse($consentimento->estaAtivo());
        $this->assertFalse($dbv->fresh()->consentimento_lgpd);
    }

    public function test_novo_aceite_apos_revogacao_gera_segundo_registro(): void
    {
        ['master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $this->actingAs($master)->post(route('privacidade.aceitar', $dbv), ['versao_termo' => '2026.1']);
        $primeiro = ConsentimentoPrivacidade::first();
        $this->actingAs($master)->post(route('privacidade.revogar', [$dbv, $primeiro]), ['motivo_revogacao' => 'x']);
        $this->actingAs($master)->post(route('privacidade.aceitar', $dbv), ['versao_termo' => '2026.1']);

        $this->assertSame(2, ConsentimentoPrivacidade::count());
        $this->assertNotNull($dbv->consentimentoPrivacidadeAtivo());
    }

    public function test_via_fisica_grava_em_disco_privado_e_download_autorizado_funciona(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        ['master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $this->actingAs($master)->post(route('privacidade.via-fisica', $dbv), [
            'arquivo' => UploadedFile::fake()->create('termo-assinado.pdf', 40, 'application/pdf'),
        ])->assertSessionHas('success');

        $consentimento = ConsentimentoPrivacidade::first();
        $this->assertNotNull($consentimento->via_fisica_caminho);

        // Documento sensível de menor: fica no disco PRIVADO, nunca no público.
        Storage::disk('local')->assertExists($consentimento->via_fisica_caminho);
        Storage::disk('public')->assertMissing($consentimento->via_fisica_caminho);

        // A única porta de saída é a rota autenticada, escopada por tenant.
        $this->actingAs($master)
            ->get(route('privacidade.via-fisica.download', [$dbv, $consentimento]))
            ->assertOk();
    }

    public function test_download_da_via_fisica_de_outro_clube_da_404(): void
    {
        Storage::fake('local');

        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB, 'master' => $masterB] = criarClubeComDados('Clube B');

        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);
        $this->actingAs($masterB)->post(route('privacidade.via-fisica', $dbvB), [
            'arquivo' => UploadedFile::fake()->create('termo.pdf', 20, 'application/pdf'),
        ]);
        $consentimentoB = ConsentimentoPrivacidade::withoutGlobalScopes()->first();

        // Route-model binding aplica o ClubScope → 404 para outro clube.
        $this->actingAs($masterA)
            ->get(route('privacidade.via-fisica.download', [$dbvB, $consentimentoB]))
            ->assertNotFound();
    }

    public function test_usuario_de_outro_clube_nao_ve_nem_revoga_consentimento(): void
    {
        ['master' => $masterA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB, 'master' => $masterB] = criarClubeComDados('Clube B');

        $dbvB = Desbravador::factory()->create(['unidade_id' => $unidadeB->id]);
        $this->actingAs($masterB)->post(route('privacidade.aceitar', $dbvB), ['versao_termo' => '2026.1']);
        $consentimentoB = ConsentimentoPrivacidade::withoutGlobalScopes()->first();

        // Route-model binding aplica o ClubScope → 404 para outro clube.
        $this->actingAs($masterA)->get(route('privacidade.index', $dbvB))->assertNotFound();
        $this->actingAs($masterA)
            ->post(route('privacidade.revogar', [$dbvB, $consentimentoB]), ['motivo_revogacao' => 'x'])
            ->assertNotFound();

        // Continua ativo — nada foi alterado por quem não é do clube.
        $this->assertTrue($consentimentoB->fresh()->estaAtivo());
    }
}
