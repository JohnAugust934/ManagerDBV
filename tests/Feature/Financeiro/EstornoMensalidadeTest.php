<?php

namespace Tests\Feature\Financeiro;

use App\Models\Caixa;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pagamento e estorno de mensalidade:
 * - pagar gera entrada de caixa COM trilha de auditoria (via CaixaObserver),
 *   vinculada à mensalidade (mensalidade_id);
 * - estornar volta o status para pendente, remove a entrada e registra `excluido`;
 * - isolamento por club_id no estorno;
 * - só é possível estornar mensalidade paga.
 */
class EstornoMensalidadeTest extends TestCase
{
    use RefreshDatabase;

    private function novaMensalidade($club, $unidade): Mensalidade
    {
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        return Mensalidade::create([
            'desbravador_id' => $dbv->id,
            'mes' => 1,
            'ano' => 2026,
            'valor' => 35.50,
            'status' => 'pendente',
            'club_id' => $club->id,
        ]);
    }

    public function test_pagar_gera_entrada_de_caixa_auditada_e_vinculada(): void
    {
        ['club' => $club, 'master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $this->actingAs($master);

        $mens = $this->novaMensalidade($club, $unidade);

        $this->postJson(route('mensalidades.pagar', $mens->id))->assertOk();

        $mens->refresh();
        $this->assertSame('pago', $mens->status);

        $entrada = Caixa::where('mensalidade_id', $mens->id)->first();
        $this->assertNotNull($entrada);
        $this->assertSame('entrada', $entrada->tipo);
        $this->assertSame('35.50', (string) $entrada->valor);

        // CaixaObserver gravou a auditoria do `criado`.
        $this->assertDatabaseHas('caixa_audit_logs', [
            'caixa_id' => $entrada->id,
            'acao' => 'criado',
            'user_id' => $master->id,
        ]);
    }

    public function test_estornar_reverte_status_remove_entrada_e_audita(): void
    {
        ['club' => $club, 'master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $this->actingAs($master);

        $mens = $this->novaMensalidade($club, $unidade);
        $this->postJson(route('mensalidades.pagar', $mens->id))->assertOk();
        $entradaId = Caixa::where('mensalidade_id', $mens->id)->value('id');

        $this->postJson(route('mensalidades.estornar', $mens->id))->assertOk();

        $mens->refresh();
        $this->assertSame('pendente', $mens->status);
        $this->assertNull($mens->data_pagamento);
        $this->assertDatabaseMissing('caixas', ['id' => $entradaId]);
        $this->assertDatabaseHas('caixa_audit_logs', [
            'caixa_id' => $entradaId,
            'acao' => 'excluido',
            'user_id' => $master->id,
        ]);
    }

    public function test_nao_estorna_mensalidade_pendente(): void
    {
        ['club' => $club, 'master' => $master, 'unidade' => $unidade] = criarClubeComDados('Clube A');
        $this->actingAs($master);

        $mens = $this->novaMensalidade($club, $unidade);

        $this->postJson(route('mensalidades.estornar', $mens->id))->assertStatus(422);
        $this->assertSame('pendente', $mens->fresh()->status);
    }

    public function test_estorno_isolado_por_clube(): void
    {
        ['club' => $clubA, 'master' => $masterA, 'unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['master' => $masterB] = criarClubeComDados('Clube B');

        $this->actingAs($masterA);
        $mens = $this->novaMensalidade($clubA, $unidadeA);
        $this->postJson(route('mensalidades.pagar', $mens->id))->assertOk();

        // Master do clube B não enxerga a mensalidade do clube A → 404.
        $this->actingAs($masterB);
        $this->postJson(route('mensalidades.estornar', $mens->id))->assertNotFound();

        $this->assertSame('pago', $mens->fresh()->status);
    }
}
