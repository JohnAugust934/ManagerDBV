<?php

namespace Tests\Feature;

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
        $user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'João Desbravador',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'unidade_id' => $unidade->id,
            'classe_atual' => 'Amigo',
            // Novos Obrigatórios
            'email' => 'joao@teste.com',
            'endereco' => 'Rua A, 123',
            'nome_responsavel' => 'Pai do João',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
        ]);

        $response->assertRedirect(route('desbravadores.index'));
        $this->assertDatabaseHas('desbravadores', [
            'nome' => 'João Desbravador',
            'numero_sus' => '12345678900'
        ]);
    }

    public function test_nao_pode_criar_sem_sus_ou_responsavel()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'Incompleto',
            'unidade_id' => $unidade->id,
            // Faltando campos obrigatórios
        ]);

        $response->assertSessionHasErrors(['numero_sus', 'nome_responsavel', 'email']);
    }

    public function test_pode_editar_desbravador()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();

        // Factory precisa estar atualizada ou criamos manualmente com todos os dados
        $dbv = Desbravador::create([
            'nome' => 'Antigo',
            'ativo' => true,
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'unidade_id' => $unidade->id,
            'classe_atual' => 'Amigo',
            'email' => 'teste@teste.com',
            'endereco' => 'Rua Antiga',
            'nome_responsavel' => 'Responsavel',
            'telefone_responsavel' => '000',
            'numero_sus' => '000'
        ]);

        $response = $this->actingAs($user)->put(route('desbravadores.update', $dbv), [
            'nome' => 'João Editado',
            'data_nascimento' => '2010-01-01',
            'sexo' => 'M',
            'unidade_id' => $unidade->id,
            'classe_atual' => 'Companheiro',
            // Dados obrigatórios devem ser reenviados
            'email' => 'teste@teste.com',
            'endereco' => 'Rua Nova',
            'nome_responsavel' => 'Responsavel',
            'telefone_responsavel' => '000',
            'numero_sus' => '99999', // Mudando SUS
            // Ativo não enviado = inativar
        ]);

        $response->assertRedirect(route('desbravadores.show', $dbv));

        $this->assertDatabaseHas('desbravadores', [
            'id' => $dbv->id,
            'numero_sus' => '99999',
            'ativo' => false
        ]);
    }
}
