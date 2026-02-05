<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_ver_lista_de_eventos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);

        Evento::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('eventos.index'));

        $response->assertStatus(200);
        $response->assertViewHas('eventos');
    }

    public function test_apenas_secretaria_pode_criar_evento()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);

        // 1. Instrutor tenta criar (Deve falhar)
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
        $this->assertDatabaseHas('eventos', ['nome' => 'Acampamento de Verão']);
    }

    public function test_apenas_secretaria_pode_editar_evento()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $evento = Evento::factory()->create(['nome' => 'Original']);

        // 1. Instrutor tenta editar
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

        // CORREÇÃO: O teste agora espera redirecionar para o SHOW, não Index
        $response->assertRedirect(route('eventos.show', $evento->id));

        $this->assertDatabaseHas('eventos', ['id' => $evento->id, 'nome' => 'Nome Editado']);
    }

    public function test_diretor_pode_inscrever_um_desbravador()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $unidade = Unidade::factory()->create();
        $classe = Classe::factory()->create();

        $dbv = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
        ]);

        $evento = Evento::factory()->create();

        // Inscrição Individual
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

        $unidade = Unidade::factory()->create();
        $classe = Classe::factory()->create();

        $dbvs = Desbravador::factory()->count(3)->create([
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
        ]);

        $evento = Evento::factory()->create(['valor' => 50]);

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

        $unidade = Unidade::factory()->create();
        $classe = Classe::factory()->create();

        $dbv = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
        ]);

        $evento = Evento::factory()->create(['valor' => 100.00]);
        $evento->desbravadores()->attach($dbv->id, ['pago' => false]);

        $response = $this->actingAs($user)->patchJson(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '1',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

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
}
