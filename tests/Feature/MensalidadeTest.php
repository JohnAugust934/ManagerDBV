<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MensalidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_gerar_mensalidades_para_todos_os_ativos()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        $unidade = Unidade::create(['nome' => 'Unidade 1', 'club_id' => $clube->id]);

        // Cria 3 desbravadores manualmente
        Desbravador::create(['nome' => 'João', 'unidade_id' => $unidade->id, 'ativo' => true, 'data_nascimento' => '2010-01-01', 'sexo' => 'M']);
        Desbravador::create(['nome' => 'Maria', 'unidade_id' => $unidade->id, 'ativo' => true, 'data_nascimento' => '2010-01-01', 'sexo' => 'F']);
        Desbravador::create(['nome' => 'Pedro', 'unidade_id' => $unidade->id, 'ativo' => true, 'data_nascimento' => '2010-01-01', 'sexo' => 'M']);

        $response = $this->actingAs($user)->post(route('mensalidades.gerar'), [
            'mes' => 10,
            'ano' => 2026,
            'valor' => 15.00,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('mensalidades', 3);

        // Geração em massa (insert) preenche a autoria manualmente, já que o
        // insert() não dispara o trait RegistraAutoria.
        $this->assertSame(3, Mensalidade::where('created_by', $user->id)->where('updated_by', $user->id)->count());
    }

    public function test_gerar_mensalidades_persiste_valor_com_precisao_decimal()
    {
        $clube = Club::create(['nome' => 'Clube Decimal', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        $unidade = Unidade::create(['nome' => 'Unidade 1', 'club_id' => $clube->id]);
        Desbravador::create(['nome' => 'João', 'unidade_id' => $unidade->id, 'ativo' => true, 'data_nascimento' => '2010-01-01', 'sexo' => 'M']);

        // Valor que exporia imprecisao de float; o insert em massa nao passa pelo
        // cast decimal:2, entao deve ser gravado como "19.99" exato.
        $this->actingAs($user)->post(route('mensalidades.gerar'), [
            'mes' => 11,
            'ano' => 2026,
            'valor' => 19.99,
        ])->assertRedirect();

        $this->assertDatabaseHas('mensalidades', [
            'club_id' => $clube->id,
            'mes' => 11,
            'ano' => 2026,
            'valor' => '19.99',
        ]);
    }

    public function test_gerar_mensalidades_bloqueado_sem_clube_ativo()
    {
        // Platform admin sem impersonação: club_id null → sem clube ativo. O
        // insert espalharia mensalidades órfãs por todos os clubes; deve dar 403.
        Club::create(['nome' => 'Clube Alheio', 'cidade' => 'SP']);
        $admin = User::factory()->platformAdmin()->create();

        $response = $this->actingAs($admin)->post(route('mensalidades.gerar'), [
            'mes' => 10,
            'ano' => 2026,
            'valor' => 15.00,
        ]);

        // Sem clube ativo a geração é barrada — pelo middleware de contexto (302)
        // ou pelo abort_unless do controller (403). O essencial: nada é criado.
        $this->assertContains($response->status(), [403, 302]);
        $this->assertDatabaseCount('mensalidades', 0);
    }

    public function test_pagar_mensalidade_muda_status_e_lanca_no_caixa()
    {
        $clube = Club::create(['nome' => 'Clube Financeiro', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        $unidade = Unidade::create(['nome' => 'Unidade Alpha', 'club_id' => $clube->id]);

        $dbv = Desbravador::create([
            'nome' => 'Lucas',
            'unidade_id' => $unidade->id,
            'ativo' => true,
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
        ]);

        $mensalidade = Mensalidade::create([
            'desbravador_id' => $dbv->id,
            'mes' => 10,
            'ano' => 2026,
            'valor' => 20.00,
            'status' => 'pendente',
        ]);

        $response = $this->actingAs($user)->post(route('mensalidades.pagar', $mensalidade->id));

        $response->assertRedirect();

        $mensalidade->refresh();
        $this->assertEquals('pago', $mensalidade->status);
        $this->assertNotNull($mensalidade->data_pagamento);

        $this->assertDatabaseHas('caixas', [
            'tipo' => 'entrada',
            'valor' => 20.00,
            'categoria' => 'Mensalidade', // Verifica a categoria automática
        ]);
    }

    public function test_scope_inadimplentes_filtra_atrasados()
    {
        $clube = Club::create(['nome' => 'Clube Scope', 'cidade' => 'RJ']);
        $unidade = Unidade::create(['nome' => 'Unidade X', 'club_id' => $clube->id]);

        $dbv1 = Desbravador::create(['nome' => 'Bom Pagador', 'unidade_id' => $unidade->id, 'ativo' => true, 'data_nascimento' => '2010-01-01', 'sexo' => 'M']);
        $dbv2 = Desbravador::create(['nome' => 'Devedor', 'unidade_id' => $unidade->id, 'ativo' => true, 'data_nascimento' => '2010-01-01', 'sexo' => 'M']);

        // Caso 1: Pago (OK)
        Mensalidade::create([
            'desbravador_id' => $dbv1->id,
            'status' => 'pago',
            'valor' => 10,
            'mes' => now()->month,
            'ano' => now()->year,
        ]);

        // Caso 2: Pendente (ATRASADO)
        Mensalidade::create([
            'desbravador_id' => $dbv2->id,
            'status' => 'pendente',
            'valor' => 10,
            'mes' => 1,
            'ano' => now()->subYear()->year,
        ]);

        $inadimplentes = Mensalidade::inadimplentes()->get();

        $this->assertCount(1, $inadimplentes);
        $this->assertEquals($dbv2->id, $inadimplentes->first()->desbravador_id);
    }

    public function test_visualizacao_da_tela_de_mensalidades()
    {
        $clube = Club::create(['nome' => 'Clube View', 'cidade' => 'MG']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->get(route('mensalidades.index'));

        $response->assertStatus(200);
        $response->assertSee('Controle de Mensalidades');
        $response->assertSee('Recebido (Mês)');
        $response->assertSee('Gerar Carnê do Mês');
    }
}
