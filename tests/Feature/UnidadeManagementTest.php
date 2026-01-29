<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnidadeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_unidade_com_campos_obrigatorios()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);

        $response = $this->actingAs($user)->post(route('unidades.store'), [
            'nome' => 'Unidade Teste',
            'conselheiro' => 'Conselheiro Teste', // Obrigatório
            'grito_guerra' => 'Força e Honra'     // Opcional
        ]);

        $response->assertRedirect(route('unidades.index'));
        $this->assertDatabaseHas('unidades', ['nome' => 'Unidade Teste', 'conselheiro' => 'Conselheiro Teste']);
    }

    public function test_nao_pode_criar_sem_conselheiro()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);

        $response = $this->actingAs($user)->post(route('unidades.store'), [
            'nome' => 'Unidade Falha',
            // Faltando conselheiro
        ]);

        $response->assertSessionHasErrors(['conselheiro']);
    }

    public function test_pode_editar_unidade()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);

        $unidade = Unidade::factory()->create([
            'nome' => 'Unidade Velha',
            'conselheiro' => 'José'
        ]);

        $response = $this->actingAs($user)->put(route('unidades.update', $unidade->id), [
            'nome' => 'Unidade Nova',
            'conselheiro' => 'Maria',
            'grito_guerra' => 'Novo Grito'
        ]);

        $response->assertRedirect(route('unidades.show', $unidade)); // Redireciona para o painel

        $this->assertDatabaseHas('unidades', [
            'id' => $unidade->id,
            'nome' => 'Unidade Nova',
            'conselheiro' => 'Maria'
        ]);
    }
}
