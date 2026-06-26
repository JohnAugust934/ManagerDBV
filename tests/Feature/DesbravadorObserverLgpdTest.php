<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesbravadorObserverLgpdTest extends TestCase
{
    use RefreshDatabase;

    protected Club $clube;

    protected Unidade $unidade;

    protected User $secretaria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clube = Club::create(['nome' => 'Clube Orion', 'cidade' => 'SP']);
        $this->unidade = Unidade::factory()->create(['club_id' => $this->clube->id]);
        $this->secretaria = User::factory()->create([
            'role' => 'secretario',
            'club_id' => $this->clube->id,
        ]);
    }

    public function test_criar_desbravador_com_consentimento_registra_ropa_automaticamente(): void
    {
        $this->actingAs($this->secretaria);

        $desbravador = Desbravador::create([
            'nome' => 'Ana',
            'data_nascimento' => '2013-01-01',
            'sexo' => 'F',
            'unidade_id' => $this->unidade->id,
            'consentimento_lgpd' => true,
            'consentimento_lgpd_em' => now(),
            'consentimento_lgpd_responsavel' => 'Maria',
        ]);

        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'consentimento',
            'entidade' => 'desbravador',
            'entidade_id' => $desbravador->id,
        ]);
    }

    public function test_criar_desbravador_sem_consentimento_nao_registra_ropa(): void
    {
        $this->actingAs($this->secretaria);

        $desbravador = Desbravador::create([
            'nome' => 'Sem Consent',
            'data_nascimento' => '2013-01-01',
            'sexo' => 'M',
            'unidade_id' => $this->unidade->id,
            'consentimento_lgpd' => false,
        ]);

        $this->assertDatabaseMissing('lgpd_registros', [
            'acao' => 'consentimento',
            'entidade_id' => $desbravador->id,
        ]);
    }

    public function test_excluir_desbravador_registra_ropa_automaticamente(): void
    {
        $this->actingAs($this->secretaria);

        $desbravador = Desbravador::factory()->create(['unidade_id' => $this->unidade->id]);
        $id = $desbravador->id;

        $desbravador->delete();

        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'exclusao',
            'entidade' => 'desbravador',
            'entidade_id' => $id,
        ]);
    }

    public function test_revogar_consentimento_registra_ropa_automaticamente(): void
    {
        $this->actingAs($this->secretaria);

        $desbravador = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'consentimento_lgpd' => true,
            'consentimento_lgpd_responsavel' => 'Resp.',
            'consentimento_lgpd_em' => now(),
        ]);

        $desbravador->update(['consentimento_lgpd' => false]);

        $registro = \App\Models\LgpdRegistro::where('acao', 'consentimento')
            ->where('entidade_id', $desbravador->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($registro);
        $this->assertSame('revogacao', $registro->metadados['operacao'] ?? null);
    }

    public function test_contexto_sem_autenticacao_nao_quebra(): void
    {
        // Sem auth(): club_id/user_id ficam nulos, mas o registro não pode lançar exceção.
        $desbravador = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'consentimento_lgpd' => true,
            'consentimento_lgpd_responsavel' => 'Resp.',
            'consentimento_lgpd_em' => now(),
        ]);

        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'consentimento',
            'entidade_id' => $desbravador->id,
            'user_id' => null,
        ]);
    }
}
