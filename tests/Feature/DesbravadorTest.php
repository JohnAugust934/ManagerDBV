<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesbravadorTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_um_desbravador_com_campos_obrigatorios()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $unidade = Unidade::factory()->create();
        $classe = Classe::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'João Desbravador',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'cpf' => '123.456.789-00',
            'rg' => '12.345.678-9',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'email' => 'joao@teste.com',
            'nome_responsavel' => 'Mãe do João',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
            'endereco' => 'Rua Teste, 123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('desbravadores.index'));

        $this->assertDatabaseHas('desbravadores', [
            'nome' => 'João Desbravador',
            'classe_atual' => $classe->id,
            'cpf' => '123.456.789-00',
        ]);
    }

    public function test_nao_pode_criar_sem_sus_ou_responsavel_ou_cpf()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create();
        $classe = Classe::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'João Sem Dados',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            // Faltando SUS, Responsável e CPF propositalmente
        ]);

        $response->assertSessionHasErrors(['numero_sus', 'nome_responsavel', 'cpf']);
    }

    public function test_pode_editar_desbravador()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $desbravador = Desbravador::factory()->create();
        $novaClasse = Classe::factory()->create();

        $response = $this->actingAs($user)->put(route('desbravadores.update', $desbravador), [
            'nome' => 'João Editado',
            'data_nascimento' => $desbravador->data_nascimento->format('Y-m-d'),
            'sexo' => 'M',
            'cpf' => $desbravador->cpf,
            'rg' => '99.999.999-X',
            'unidade_id' => $desbravador->unidade_id,
            'classe_atual' => $novaClasse->id,
            'ativo' => true,
            'email' => $desbravador->email,
            'nome_responsavel' => $desbravador->nome_responsavel,
            'telefone_responsavel' => $desbravador->telefone_responsavel,
            'numero_sus' => $desbravador->numero_sus,
            'endereco' => $desbravador->endereco,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('desbravadores.show', $desbravador));

        $this->assertDatabaseHas('desbravadores', [
            'id' => $desbravador->id,
            'nome' => 'João Editado',
            'classe_atual' => $novaClasse->id,
            'rg' => '99.999.999-X',
        ]);
    }
}
