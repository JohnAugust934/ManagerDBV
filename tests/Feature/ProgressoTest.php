<?php

namespace Tests\Feature;

use App\Models\Classe;
use App\Models\Club;
use App\Models\Desbravador;
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
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id, 'classe_atual' => 'Amigo']);

        // Cria dados base
        $classe = Classe::create(['nome' => 'Amigo', 'cor' => '#000', 'ordem' => 1]);
        Requisito::create(['classe_id' => $classe->id, 'descricao' => 'Teste Req']);

        $response = $this->actingAs($user)->get(route('progresso.index', $dbv->id));

        $response->assertStatus(200);
        $response->assertSee('Teste Req');
    }

    public function test_pode_marcar_e_desmarcar_requisito()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);

        $classe = Classe::create(['nome' => 'Amigo', 'cor' => '#000', 'ordem' => 1]);
        $req = Requisito::create(['classe_id' => $classe->id, 'descricao' => 'Req 1']);

        // 1. Marcar (Toggle ON)
        $response = $this->actingAs($user)->post(route('progresso.toggle', $dbv->id), [
            'requisito_id' => $req->id
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('desbravador_requisito', [
            'desbravador_id' => $dbv->id,
            'requisito_id' => $req->id
        ]);

        // 2. Desmarcar (Toggle OFF)
        $this->actingAs($user)->post(route('progresso.toggle', $dbv->id), [
            'requisito_id' => $req->id
        ]);

        $this->assertDatabaseMissing('desbravador_requisito', [
            'desbravador_id' => $dbv->id,
            'requisito_id' => $req->id
        ]);
    }
}
