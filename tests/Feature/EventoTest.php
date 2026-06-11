<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Unidade;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_ver_lista_de_eventos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        // Eventos devem ter club_id para o GlobalScope retorná-los.
        Evento::factory()->count(3)->create(['club_id' => $clube->id]);

        $response = $this->actingAs($user)->get(route('eventos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('eventos');
    }

    public function test_apenas_secretaria_pode_criar_evento()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);

        // 1. Instrutor tenta criar (Deve falhar com 403)
        $instrutor = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);
        $this->actingAs($instrutor)->get(route('eventos.create'))->assertForbidden();

        // 2. Secretária tenta criar (Deve conseguir)
        $secretaria = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($secretaria)->post(route('eventos.store'), [
            'nome' => 'Acampamento de Verão',
            'local' => 'Sítio',
            'data_inicio' => now()->addDays(10),
            'data_fim' => now()->addDays(12),
            'valor' => 150.00,
            'descricao' => 'Teste',
        ]);

        $response->assertRedirect(route('eventos.index'));
        $this->assertDatabaseHas('eventos', ['nome' => 'Acampamento de Verão', 'club_id' => $clube->id]);
    }

    public function test_apenas_secretaria_pode_editar_evento()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        // Evento deve ter club_id para o GlobalScope encontrá-lo.
        $evento = Evento::factory()->create(['nome' => 'Original', 'club_id' => $clube->id]);

        // 1. Instrutor tenta editar (Deve falhar)
        $instrutor = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);
        $this->actingAs($instrutor)->get(route('eventos.edit', $evento->id))->assertForbidden();

        // 2. Secretária edita
        $secretaria = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($secretaria)->put(route('eventos.update', $evento->id), [
            'nome' => 'Nome Editado',
            'local' => $evento->local,
            'data_inicio' => $evento->data_inicio,
            'data_fim' => $evento->data_fim,
            'valor' => $evento->valor,
        ]);

        $response->assertRedirect(route('eventos.show', $evento->id));
        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'nome' => 'Nome Editado']);
    }

    public function test_diretor_pode_inscrever_um_desbravador()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento = Evento::factory()->create(['club_id' => $clube->id]);

        $this->actingAs($user)->post(route('eventos.inscrever', $evento->id), [
            'desbravador_id' => $dbv->id,
        ]);

        $this->assertDatabaseHas('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
            'pago' => false,
        ]);
    }

    public function test_pode_inscrever_em_lote()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        $classe = Classe::factory()->create();

        $dbvs = Desbravador::factory()->count(3)->create([
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
        ]);

        $evento = Evento::factory()->create(['valor' => 50, 'club_id' => $clube->id]);

        $response = $this->actingAs($user)->post(route('eventos.inscrever-lote', $evento->id), [
            'desbravadores' => $dbvs->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $this->assertEquals(3, $evento->desbravadores()->count());
    }

    public function test_pagamento_ajax_atualiza_status_e_lanca_no_caixa()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento = Evento::factory()->create(['valor' => 100.00, 'club_id' => $clube->id]);
        $evento->desbravadores()->attach($dbv->id, ['pago' => false]);

        $response = $this->actingAs($user)->patchJson(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '1',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status_alterado' => true,
                'movimentacao_registrada' => true,
            ]);

        $this->assertDatabaseHas('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
            'pago' => true,
        ]);

        $this->assertDatabaseHas('caixas', [
            'tipo' => 'entrada',
            'valor' => 100.00,
            'descricao' => "Evento: {$evento->nome} - {$dbv->nome}",
        ]);
    }

    public function test_repetir_pagamento_nao_duplica_lancamento_no_caixa()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento = Evento::factory()->create(['valor' => 100.00, 'club_id' => $clube->id]);
        $evento->desbravadores()->attach($dbv->id, ['pago' => false]);

        $this->actingAs($user)->patchJson(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '1',
        ])->assertOk();

        $response = $this->actingAs($user)->patchJson(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '1',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'status_alterado' => false,
                'movimentacao_registrada' => false,
            ]);

        $this->assertDatabaseCount('caixas', 1);
    }

    public function test_estorno_gera_saida_apenas_uma_vez_por_transicao_real()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento = Evento::factory()->create(['valor' => 80.00, 'club_id' => $clube->id]);
        $evento->desbravadores()->attach($dbv->id, ['pago' => true]);

        $this->actingAs($user)->patchJson(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '0',
        ])->assertOk()->assertJson([
            'status_alterado' => true,
            'movimentacao_registrada' => true,
        ]);

        $response = $this->actingAs($user)->patchJson(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '0',
        ]);

        $response->assertOk()->assertJson([
            'status_alterado' => false,
            'movimentacao_registrada' => false,
        ]);

        $this->assertDatabaseHas('caixas', [
            'tipo' => 'saida',
            'valor' => 80.00,
            'descricao' => "Estorno Evento: {$evento->nome} - {$dbv->nome}",
            'categoria' => 'Evento',
        ]);
        $this->assertDatabaseCount('caixas', 1);
    }

    public function test_nao_permite_atualizar_status_financeiro_sem_inscricao()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento = Evento::factory()->create(['valor' => 65.00, 'club_id' => $clube->id]);

        $response = $this->actingAs($user)->patchJson(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '1',
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Inscrição não encontrada para este desbravador.']);

        $this->assertDatabaseCount('caixas', 0);
    }

    public function test_pode_gerar_autorizacao_de_evento_com_dados_do_desbravador()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $unidade = Unidade::factory()->create(['nome' => 'Lobos', 'club_id' => $clube->id]);
        $classe = Classe::factory()->create(['nome' => 'Companheiro']);

        $dbv = Desbravador::factory()->create([
            'nome' => 'Daniel Silva',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'nome_responsavel' => 'Maria Silva',
            'telefone_responsavel' => '11999999999',
            'cpf' => '123.456.789-00',
            'numero_sus' => '123456789',
            'alergias' => 'Nenhuma',
            'plano_saude' => 'Plano Teste',
        ]);

        $evento = Evento::factory()->create([
            'nome' => 'Acampamento de Outono',
            'local' => 'Sitio Esperanca',
            'club_id' => $clube->id,
        ]);

        $pdfWrapper = \Mockery::mock(DomPdfWrapper::class);
        $pdfWrapper->shouldReceive('stream')
            ->once()
            ->with('autorizacao.pdf')
            ->andReturn(response('pdf', 200, ['content-type' => 'application/pdf']));

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) use ($dbv, $evento) {
                $this->assertSame('relatorios.autorizacao', $view);
                $this->assertSame($dbv->id, $data['desbravador']->id);
                $this->assertSame($evento->id, $data['evento']->id);

                return true;
            })
            ->andReturn($pdfWrapper);

        $response = $this->actingAs($user)->get(route('eventos.autorizacao', [$evento, $dbv]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pode_ver_detalhes_do_evento()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $evento = Evento::factory()->create(['nome' => 'Acampamento Anual', 'club_id' => $clube->id]);

        $response = $this->actingAs($user)->get(route('eventos.show', $evento));

        $response->assertStatus(200);
        $response->assertSee('Acampamento Anual');
    }

    public function test_pode_acessar_formulario_de_edicao_do_evento()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $evento = Evento::factory()->create(['nome' => 'Rally', 'club_id' => $clube->id]);

        $response = $this->actingAs($user)->get(route('eventos.edit', $evento));

        $response->assertStatus(200);
        $response->assertSee('Rally');
    }

    public function test_nao_pode_excluir_evento_com_inscritos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $evento = Evento::factory()->create(['club_id' => $clube->id]);
        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento->desbravadores()->attach($dbv->id, ['pago' => false, 'autorizacao_entregue' => false]);

        $response = $this->actingAs($user)->delete(route('eventos.destroy', $evento));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('eventos', ['id' => $evento->id]);
    }

    public function test_pode_excluir_evento_sem_inscritos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $evento = Evento::factory()->create(['club_id' => $clube->id]);

        $this->actingAs($user)->delete(route('eventos.destroy', $evento));

        $this->assertDatabaseMissing('eventos', ['id' => $evento->id]);
    }

    public function test_pode_remover_inscricao()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $evento = Evento::factory()->create(['club_id' => $clube->id]);
        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento->desbravadores()->attach($dbv->id, ['pago' => false, 'autorizacao_entregue' => false]);

        $response = $this->actingAs($user)->delete(route('eventos.remover-inscricao', [$evento, $dbv]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
        ]);
    }

    public function test_remover_inscricao_paga_gera_estorno_no_caixa()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $evento = Evento::factory()->create(['valor' => 120.00, 'club_id' => $clube->id]);
        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento->desbravadores()->attach($dbv->id, ['pago' => true, 'autorizacao_entregue' => false]);

        $response = $this->actingAs($user)->delete(route('eventos.remover-inscricao', [$evento, $dbv]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
        ]);
        $this->assertDatabaseHas('caixas', [
            'tipo' => 'saida',
            'valor' => 120.00,
            'categoria' => 'Evento',
            'descricao' => "Estorno Evento: {$evento->nome} - {$dbv->nome}",
        ]);
    }

    public function test_remover_inscricao_nao_paga_nao_mexe_no_caixa()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $evento = Evento::factory()->create(['valor' => 100.00, 'club_id' => $clube->id]);
        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento->desbravadores()->attach($dbv->id, ['pago' => false, 'autorizacao_entregue' => false]);

        $this->actingAs($user)->delete(route('eventos.remover-inscricao', [$evento, $dbv]))->assertRedirect();

        $this->assertDatabaseMissing('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
        ]);
        $this->assertDatabaseCount('caixas', 0);
    }

    public function test_usuario_sem_financeiro_nao_remove_inscricao_paga()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // Secretário tem permissão de eventos, mas não de financeiro.
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $evento = Evento::factory()->create(['valor' => 100.00, 'club_id' => $clube->id]);
        $dbv = Desbravador::factory()->forClube($clube->id)->create();
        $evento->desbravadores()->attach($dbv->id, ['pago' => true, 'autorizacao_entregue' => false]);

        $response = $this->actingAs($user)->delete(route('eventos.remover-inscricao', [$evento, $dbv]));

        $response->assertForbidden();
        // A transação deve ter sido revertida: inscrição permanece e nenhum estorno foi lançado.
        $this->assertDatabaseHas('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
        ]);
        $this->assertDatabaseCount('caixas', 0);
    }
}
