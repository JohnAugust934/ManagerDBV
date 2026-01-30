<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_apenas_usuarios_logados_podem_ver_unidades()
    {
        $response = $this->get('/unidades');
        $response->assertRedirect('/login');
    }

    public function test_usuario_logado_pode_ver_lista_de_unidades()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        // CORREÇÃO: Conselheiro pode ver, mas não criar
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'conselheiro']);

        $response = $this->actingAs($user)->get('/unidades');
        $response->assertStatus(200);
    }

    public function test_pode_criar_uma_nova_unidade()
    {
        $clube = Club::create(['nome' => 'Clube', 'cidade' => 'SP']);
        // CORREÇÃO: Diretor é necessário para criar
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $dados = [
            'nome' => 'Unidade Teste Águia',
            'grito_guerra' => 'Voar alto!',
            'conselheiro' => 'João da Silva'
        ];

        $response = $this->actingAs($user)->post('/unidades', $dados);

        $response->assertRedirect(route('unidades.index'));
        $this->assertDatabaseHas('unidades', [
            'nome' => 'Unidade Teste Águia'
        ]);
    }
}
