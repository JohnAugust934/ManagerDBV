<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Classe;
use App\Models\Requisito;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_ver_tela_de_progresso()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        // CORREÇÃO: Conselheiro
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'conselheiro']);
        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'classe_atual' => 'Amigo'
        ]);

        $classe = Classe::create(['nome' => 'Amigo', 'cor' => 'blue', 'ordem' => 1]);
        Requisito::create(['classe_id' => $classe->id, 'codigo' => 'G1', 'descricao' => 'Teste Req', 'categoria' => 'Gerais']);

        $response = $this->actingAs($user)->get(route('progresso.index', $dbv->id));

        $response->assertStatus(200);
        $response->assertSee('Teste Req');
    }

    public function test_pode_marcar_e_desmarcar_requisito()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        // CORREÇÃO: Conselheiro
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'conselheiro']);
        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);
        $classe = Classe::create(['nome' => 'Amigo', 'cor' => 'blue', 'ordem' => 1]);
        $req = Requisito::create(['classe_id' => $classe->id, 'codigo' => 'G1', 'descricao' => 'X', 'categoria' => 'Y']);

        $response = $this->actingAs($user)->post(route('progresso.toggle', $dbv->id), [
            'requisito_id' => $req->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('desbravador_requisito', [
            'desbravador_id' => $dbv->id,
            'requisito_id' => $req->id
        ]);
    }
}
