<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Trilha de auditoria (created_by/updated_by) preenchida pelo trait RegistraAutoria
 * nas tabelas críticas: desbravadores (pessoas) e caixas (financeiro).
 */
class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_desbravador_registra_autor_na_criacao_e_atualizacao()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $autor = User::factory()->create(['club_id' => $club->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create(['club_id' => $club->id]);
        $classe = Classe::factory()->create();

        $payload = [
            'nome' => 'João Desbravador',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'cpf' => '123.456.789-00',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'email' => 'joao@teste.com',
            'nome_responsavel' => 'Mãe do João',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
            'endereco' => 'Rua Teste, 123',
        ];

        $this->actingAs($autor)->post(route('desbravadores.store'), $payload)->assertSessionHasNoErrors();

        $desbravador = Desbravador::where('cpf', '123.456.789-00')->firstOrFail();
        $this->assertSame($autor->id, $desbravador->created_by);
        $this->assertSame($autor->id, $desbravador->updated_by);

        // Outro usuário edita: created_by permanece, updated_by muda.
        $editor = User::factory()->create(['club_id' => $club->id, 'role' => 'secretario']);
        $this->actingAs($editor)->put(route('desbravadores.update', $desbravador), array_merge($payload, [
            'nome' => 'João Editado',
        ]))->assertSessionHasNoErrors();

        $desbravador->refresh();
        $this->assertSame($autor->id, $desbravador->created_by, 'created_by não deve mudar na edição.');
        $this->assertSame($editor->id, $desbravador->updated_by);
    }

    public function test_pagina_do_desbravador_exibe_autoria()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $autor = User::factory()->create(['club_id' => $club->id, 'role' => 'secretario', 'name' => 'Secretaria Ana']);
        $unidade = Unidade::factory()->create(['club_id' => $club->id]);
        $classe = Classe::factory()->create();

        $this->actingAs($autor)->post(route('desbravadores.store'), [
            'nome' => 'João Desbravador',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'cpf' => '123.456.789-00',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'email' => 'joao@teste.com',
            'nome_responsavel' => 'Mãe do João',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
            'endereco' => 'Rua Teste, 123',
        ])->assertSessionHasNoErrors();

        $desbravador = Desbravador::where('cpf', '123.456.789-00')->firstOrFail();

        $response = $this->actingAs($autor)->get(route('desbravadores.show', $desbravador));

        $response->assertOk();
        $response->assertSeeText('Cadastrado por');
        $response->assertSeeText('Secretaria Ana');
    }

    public function test_caixa_registra_autor_na_criacao()
    {
        $club = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $autor = User::factory()->create(['club_id' => $club->id, 'role' => 'tesoureiro']);

        $this->actingAs($autor);

        $caixa = Caixa::create([
            'descricao' => 'Doação',
            'valor' => 100.00,
            'tipo' => 'entrada',
            'categoria' => 'Doações',
            'data_movimentacao' => now(),
            'club_id' => $club->id,
        ]);

        $this->assertSame($autor->id, $caixa->fresh()->created_by);
    }

    public function test_registro_sem_usuario_autenticado_nao_quebra()
    {
        // Seeders/factories rodam sem auth: autoria fica nula, sem erro.
        $unidade = Unidade::factory()->create();
        $desbravador = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $this->assertNull($desbravador->created_by);
        $this->assertNull($desbravador->updated_by);
    }
}
