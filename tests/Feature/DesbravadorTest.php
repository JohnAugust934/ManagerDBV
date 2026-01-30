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
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'João Desbravador',
            'data_nascimento' => '2010-05-10',
            'sexo' => 'M',
            'unidade_id' => $unidade->id,
            'classe_atual' => 'Amigo',
            'email' => 'joao@teste.com',
            'nome_responsavel' => 'Maria Mãe',
            'telefone_responsavel' => '11999999999',
            'numero_sus' => '12345678900',
            'endereco' => 'Rua Teste, 123' // Adicionado campo obrigatório
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
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create();

        $response = $this->actingAs($user)->post(route('desbravadores.store'), [
            'nome' => 'Incompleto',
            'data_nascimento' => '2010-01-01',
            'unidade_id' => $unidade->id,
            // Faltando campos
        ]);

        $response->assertSessionHasErrors(['numero_sus', 'nome_responsavel']);
    }

    public function test_pode_editar_desbravador()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create();

        $dbv = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'numero_sus' => '111',
            'nome_responsavel' => 'Pai',
            'endereco' => 'Rua Antiga'
        ]);

        $response = $this->actingAs($user)->put(route('desbravadores.update', $dbv->id), [
            'nome' => $dbv->nome,
            'data_nascimento' => $dbv->data_nascimento->format('Y-m-d'),
            'sexo' => $dbv->sexo,
            'unidade_id' => $unidade->id,
            'classe_atual' => $dbv->classe_atual,
            'email' => 'novo@email.com',
            'nome_responsavel' => 'Pai',
            'telefone_responsavel' => '00000000',
            'numero_sus' => '99999',
            'endereco' => 'Rua Nova, 100' // Adicionado campo obrigatório
        ]);

        $response->assertRedirect(route('desbravadores.show', $dbv));

        $this->assertDatabaseHas('desbravadores', [
            'id' => $dbv->id,
            'numero_sus' => '99999',
        ]);
    }
}
