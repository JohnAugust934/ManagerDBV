<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_criar_evento()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);

        $response = $this->actingAs($user)->post(route('eventos.store'), [
            'nome' => 'Acampamento de Verão',
            'local' => 'Sítio',
            'data_inicio' => now()->addDays(10),
            'valor' => 150.00
        ]);

        $response->assertRedirect(route('eventos.index'));
        $this->assertDatabaseHas('eventos', ['nome' => 'Acampamento de Verão']);
    }

    public function test_pode_inscrever_desbravador_e_marcar_pago()
    {
        $clube = Club::create(['nome' => 'Teste', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id]);
        $unidade = Unidade::factory()->create();
        $dbv = Desbravador::factory()->create(['unidade_id' => $unidade->id]);
        $evento = Evento::create([
            'nome' => 'Camp',
            'local' => 'X',
            'data_inicio' => now(),
            'valor' => 100
        ]);

        // 1. Inscrever
        $this->actingAs($user)->post(route('eventos.inscrever', $evento->id), [
            'desbravador_id' => $dbv->id
        ]);

        $this->assertDatabaseHas('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
            'pago' => false
        ]);

        // 2. Marcar como Pago
        $this->actingAs($user)->patch(route('eventos.status', [$evento->id, $dbv->id]), [
            'campo' => 'pago',
            'valor' => '1'
        ]);

        $this->assertDatabaseHas('desbravador_evento', [
            'evento_id' => $evento->id,
            'desbravador_id' => $dbv->id,
            'pago' => true
        ]);
    }
}
