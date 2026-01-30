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
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        // CORREÇÃO: Diretor (para criar unidades)
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->post(route('unidades.store'), [
            'nome' => 'Unidade Teste',
            'conselheiro' => 'Conselheiro Teste',
            'grito_guerra' => 'Força e Honra'
        ]);

        $response->assertRedirect(route('unidades.index'));
        $this->assertDatabaseHas('unidades', ['nome' => 'Unidade Teste', 'conselheiro' => 'Conselheiro Teste']);
    }

    public function test_nao_pode_criar_sem_conselheiro()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $response = $this->actingAs($user)->post(route('unidades.store'), [
            'nome' => 'Unidade Falha',
            // Faltando conselheiro
        ]);

        $response->assertSessionHasErrors(['conselheiro']);
    }

    public function test_pode_editar_unidade()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $unidade = Unidade::factory()->create();

        $response = $this->actingAs($user)->put(route('unidades.update', $unidade->id), [
            'nome' => 'Unidade Nova',
            'conselheiro' => 'Maria',
            'grito_guerra' => 'Novo Grito'
        ]);

        $response->assertRedirect(route('unidades.show', $unidade));

        $this->assertDatabaseHas('unidades', [
            'id' => $unidade->id,
            'nome' => 'Unidade Nova',
        ]);
    }
}
