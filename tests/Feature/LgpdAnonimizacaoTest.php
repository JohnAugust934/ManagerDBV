<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Frequencia;
use App\Models\Unidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Execução REAL do comando lgpd:anonimizar-desligados (LgpdTest cobre o dry-run).
 * Prova que o comando: anonimiza apenas desligados além do prazo, preserva os
 * dados estatísticos (frequência/pontuação) sem vínculo identificável, registra
 * ROPA e respeita o prazo (não toca ativos nem desligados recentes).
 */
class LgpdAnonimizacaoTest extends TestCase
{
    use RefreshDatabase;

    private Club $clube;

    private Unidade $unidade;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clube = Club::create(['nome' => 'Clube LGPD', 'cidade' => 'SP']);
        $this->unidade = Unidade::factory()->create(['club_id' => $this->clube->id]);
    }

    private function desligadoAntigo(string $nome): Desbravador
    {
        $dbv = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'nome' => $nome,
            'cpf' => '111.222.333-44',
            'email' => 'titular@teste.com',
            'ativo' => false,
            'consentimento_lgpd_responsavel' => null, // candidato (ainda não anonimizado)
        ]);
        $this->tocarUpdatedAt($dbv, now()->subYears(6));

        return $dbv->refresh();
    }

    /** Define updated_at via query builder — o update() do Eloquent reescreve o timestamp. */
    private function tocarUpdatedAt(Desbravador $dbv, \Carbon\Carbon $quando): void
    {
        Desbravador::withoutGlobalScopes()->where('id', $dbv->id)->update(['updated_at' => $quando]);
    }

    public function test_anonimizacao_remove_pii_preserva_estatistica_e_registra_ropa(): void
    {
        $dbv = $this->desligadoAntigo('Maria Histórica');

        // Pontuação histórica que deve sobreviver à anonimização.
        Frequencia::create(['desbravador_id' => $dbv->id, 'data' => now()->subYears(6), 'presente' => true, 'pontual' => true, 'biblia' => true, 'uniforme' => true]);
        Frequencia::create(['desbravador_id' => $dbv->id, 'data' => now()->subYears(6)->addDay(), 'presente' => true]);

        $freqAntes = Frequencia::where('desbravador_id', $dbv->id)->count();
        $pontosAntes = Frequencia::where('desbravador_id', $dbv->id)->get()->sum('pontos');

        $this->artisan('lgpd:anonimizar-desligados', ['--anos' => 5])
            ->expectsConfirmation('Anonimizar 1 desbravador(es)? Esta operação é IRREVERSÍVEL.', 'yes')
            ->assertExitCode(0);

        $dbv->refresh();

        // PII removida.
        $this->assertStringStartsWith('Membro #', $dbv->nome);
        $this->assertNull($dbv->cpf);
        $this->assertNull($dbv->email);
        $this->assertFalse((bool) $dbv->consentimento_lgpd);

        // Estatística preservada e ainda vinculada ao mesmo id (sem identificação).
        $this->assertSame($freqAntes, Frequencia::where('desbravador_id', $dbv->id)->count());
        $this->assertSame($pontosAntes, Frequencia::where('desbravador_id', $dbv->id)->get()->sum('pontos'));

        // ROPA da anonimização registrado. (O comando carrega o desbravador só com
        // id/nome/updated_at, então club_id da ROPA fica nulo — ver Important Findings.)
        $this->assertDatabaseHas('lgpd_registros', [
            'acao' => 'anonimizacao',
            'entidade' => 'desbravador',
            'entidade_id' => $dbv->id,
        ]);
    }

    public function test_anonimizacao_respeita_prazo_e_status(): void
    {
        $antigo = $this->desligadoAntigo('Desligado Antigo');

        // Desligado recente (dentro do prazo) — NÃO deve ser anonimizado.
        $recente = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'nome' => 'Desligado Recente',
            'ativo' => false,
            'consentimento_lgpd_responsavel' => null,
        ]);
        $this->tocarUpdatedAt($recente, now()->subMonths(2));

        // Ativo antigo — NÃO deve ser anonimizado (ainda é membro).
        $ativo = Desbravador::factory()->create([
            'unidade_id' => $this->unidade->id,
            'nome' => 'Ativo Antigo',
            'ativo' => true,
            'consentimento_lgpd_responsavel' => null,
        ]);
        $this->tocarUpdatedAt($ativo, now()->subYears(6));

        $this->artisan('lgpd:anonimizar-desligados', ['--anos' => 5])
            ->expectsConfirmation('Anonimizar 1 desbravador(es)? Esta operação é IRREVERSÍVEL.', 'yes')
            ->assertExitCode(0);

        $this->assertStringStartsWith('Membro #', $antigo->refresh()->nome);
        $this->assertSame('Desligado Recente', $recente->refresh()->nome);
        $this->assertSame('Ativo Antigo', $ativo->refresh()->nome);
    }

    public function test_anonimizacao_e_idempotente(): void
    {
        $dbv = $this->desligadoAntigo('Para Anonimizar');

        $this->artisan('lgpd:anonimizar-desligados', ['--anos' => 5])
            ->expectsConfirmation('Anonimizar 1 desbravador(es)? Esta operação é IRREVERSÍVEL.', 'yes')
            ->assertExitCode(0);

        $nomeAnonimizado = $dbv->refresh()->nome;

        // Segunda execução: o registro já foi tocado (updated_at recente) → fora do prazo.
        $this->artisan('lgpd:anonimizar-desligados', ['--anos' => 5])
            ->assertExitCode(0);

        $this->assertSame($nomeAnonimizado, $dbv->refresh()->nome);
        // Apenas um registro ROPA de anonimização para este desbravador.
        $this->assertSame(1, \App\Models\LgpdRegistro::where('acao', 'anonimizacao')->where('entidade_id', $dbv->id)->count());
    }
}
