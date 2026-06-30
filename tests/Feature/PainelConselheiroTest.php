<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelConselheiroTest extends TestCase
{
    use RefreshDatabase;

    public function test_conselheiro_ve_apenas_membros_da_sua_unidade()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'conselheiro']);

        $minha = Unidade::factory()->create(['club_id' => $clube->id, 'conselheiro_user_id' => $user->id, 'nome' => 'Falcões']);
        $outra = Unidade::factory()->create(['club_id' => $clube->id, 'nome' => 'Águias']);

        Desbravador::factory()->create(['unidade_id' => $minha->id, 'club_id' => $clube->id, 'ativo' => true, 'nome' => 'Membro Falcao']);
        Desbravador::factory()->create(['unidade_id' => $outra->id, 'club_id' => $clube->id, 'ativo' => true, 'nome' => 'Membro Aguia']);

        $response = $this->actingAs($user)->get(route('conselheiro.painel'));

        $response->assertOk();
        $response->assertSee('Membro Falcao');
        $response->assertDontSee('Membro Aguia');
    }

    public function test_usuario_sem_unidade_e_redirecionado()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);

        $response = $this->actingAs($user)->get(route('conselheiro.painel'));

        $response->assertRedirect(route('dashboard'));
    }
}
