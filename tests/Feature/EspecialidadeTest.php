<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Especialidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspecialidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_uma_especialidade()
    {
        $clube = Club::create(['nome' => 'Clube Teste', 'cidade' => 'SP']);
        // CORREÇÃO: Papel instrutor (acesso pedagógico)
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);

        $response = $this->actingAs($user)->post(route('especialidades.store'), [
            'nome' => 'Fogueiras',
            'area' => 'Estudos da Natureza'
        ]);

        $response->assertRedirect(route('especialidades.index'));
        $this->assertDatabaseHas('especialidades', ['nome' => 'Fogueiras']);
    }
}
