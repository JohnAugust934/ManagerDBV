<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CadastroRascunhoTest extends TestCase
{
    use RefreshDatabase;

    public function test_tela_de_cadastro_inclui_autosave_de_rascunho()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($user)->get(route('desbravadores.create'));

        $response->assertOk();
        $response->assertSee('cadastroDesbravador()', false);
        $response->assertSee('Rascunho recuperado automaticamente.');
    }
}
