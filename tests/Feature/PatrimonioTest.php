<?php

namespace Tests\Feature;

use App\Models\Patrimonio;
use App\Models\User;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatrimonioTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_ver_lista_de_patrimonio()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        // CORREÇÃO: Tesoureiro
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        Patrimonio::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('patrimonio.index'));
        $response->assertStatus(200);
    }

    public function test_usuario_pode_cadastrar_novo_item()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        // CORREÇÃO: Tesoureiro
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        $dados = [
            'item' => 'Barraca Teste',
            'quantidade' => 2,
            'valor_estimado' => 500.00,
            'estado_conservacao' => 'Bom',
            'local_armazenamento' => 'Sede'
        ];

        $response = $this->actingAs($user)->post(route('patrimonio.store'), $dados);

        $response->assertRedirect(route('patrimonio.index'));
        $this->assertDatabaseHas('patrimonios', ['item' => 'Barraca Teste']);
    }
}
