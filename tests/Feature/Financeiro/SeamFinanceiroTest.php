<?php

namespace Tests\Feature\Financeiro;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Mensalidade;
use App\Services\ClubContext;
use App\Services\InscricaoEventoService;
use App\Services\MensalidadeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seam financeiro Evento/Mensalidade → Caixa (Candidato C).
 *
 * Cobre os invariantes que antes viviam inline nos controllers: paridade
 * preview × geração de mensalidades e a reconciliação de caixa ao marcar/estornar
 * pagamento de inscrição em evento.
 */
class SeamFinanceiroTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_e_gerar_produzem_a_mesma_contagem(): void
    {
        ['club' => $club, 'unidade' => $unidade] = criarClubeComDados('Clube A');

        Desbravador::factory()->count(4)->create(['unidade_id' => $unidade->id, 'ativo' => true]);
        // Um inativo não deve entrar.
        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => false]);

        ClubContext::actAs($club->id, function () use ($club) {
            $service = app(MensalidadeService::class);

            $previstas = $service->preview(7, 2026)['serao_criadas'];
            $criadas = $service->gerar($club->id, 7, 2026, 20.00, null);

            $this->assertSame($previstas, $criadas, 'preview e gerar divergem');

            // Rodar de novo não recria (idempotente na competência).
            $this->assertSame(0, $service->preview(7, 2026)['serao_criadas']);
            $this->assertSame(0, $service->gerar($club->id, 7, 2026, 20.00, null));
        });

        $this->assertSame(4, Mensalidade::withoutGlobalScopes()->where('club_id', $club->id)->count());
    }

    public function test_marcar_e_estornar_pagamento_de_evento_concilia_o_caixa(): void
    {
        ['club' => $club, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);
        $evento = Evento::factory()->forClube($club->id)->create(['valor' => 50, 'nome' => 'Acampamento']);
        $evento->desbravadores()->attach($dbv->id, ['pago' => false]);

        ClubContext::actAs($club->id, function () use ($evento, $dbv, $club) {
            $service = app(InscricaoEventoService::class);

            // Marcar pago → entrada de 50.
            $r = $service->definirPago($evento, $dbv, true);
            $this->assertTrue($r['status_alterado']);
            $this->assertTrue($r['movimentacao_registrada']);
            $this->assertSame(1, Caixa::withoutGlobalScopes()->where('club_id', $club->id)->where('tipo', 'entrada')->count());

            // Repetir sem mudança → nada acontece.
            $r = $service->definirPago($evento, $dbv, true);
            $this->assertFalse($r['status_alterado']);

            // Remover inscrição paga → estorno (saída de 50).
            $this->assertTrue($service->removerExigeFinanceiro($evento, $dbv));
            $service->remover($evento, $dbv);
            $this->assertSame(1, Caixa::withoutGlobalScopes()->where('club_id', $club->id)->where('tipo', 'saida')->count());
            $this->assertDatabaseMissing('desbravador_evento', ['evento_id' => $evento->id, 'desbravador_id' => $dbv->id]);
        });
    }

    public function test_pagamento_de_mensalidade_gera_trilha_de_auditoria_no_caixa(): void
    {
        ['club' => $club, 'unidade' => $unidade, 'master' => $master] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);
        $mensalidade = Mensalidade::create([
            'desbravador_id' => $dbv->id, 'club_id' => $club->id,
            'mes' => 7, 'ano' => 2026, 'valor' => 30.00, 'status' => 'pendente',
        ]);

        // Antes o lançamento de caixa por pagamento não era auditado; com o
        // CaixaObserver, toda movimentação passa a ter trilha.
        $this->actingAs($master)->post(route('mensalidades.pagar', $mensalidade->id))->assertRedirect();

        $this->assertDatabaseHas('caixa_audit_logs', [
            'club_id' => $club->id,
            'acao' => 'criado',
        ]);
    }

    public function test_estorno_nao_exigido_para_inscricao_nao_paga(): void
    {
        ['club' => $club, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'ativo' => true]);
        $evento = Evento::factory()->forClube($club->id)->create(['valor' => 50]);
        $evento->desbravadores()->attach($dbv->id, ['pago' => false]);

        ClubContext::actAs($club->id, function () use ($evento, $dbv, $club) {
            $service = app(InscricaoEventoService::class);
            $this->assertFalse($service->removerExigeFinanceiro($evento, $dbv));
            $service->remover($evento, $dbv);
            $this->assertSame(0, Caixa::withoutGlobalScopes()->where('club_id', $club->id)->count());
        });
    }
}
