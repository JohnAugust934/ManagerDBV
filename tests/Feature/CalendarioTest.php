<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendario_mostra_evento_e_aniversariante_do_clube()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);

        Evento::create([
            'club_id' => $clube->id,
            'nome' => 'Acampamento Regional',
            'data_inicio' => '2026-07-10 08:00:00',
            'data_fim' => '2026-07-12 18:00:00',
            'local' => 'Campo',
            'valor' => 0,
        ]);

        Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'club_id' => $clube->id,
            'ativo' => true,
            'nome' => 'Aniversariante Julho',
            'data_nascimento' => '2012-07-15',
        ]);

        $response = $this->actingAs($user)->get(route('calendario.index', ['mes' => 7, 'ano' => 2026]));

        $response->assertOk();
        $response->assertSee('Acampamento Regional');
        $response->assertSee('Aniversariante'); // primeiro nome exibido
    }

    public function test_calendario_nao_vaza_evento_de_outro_clube()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $outro = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        Evento::create([
            'club_id' => $outro->id,
            'nome' => 'Evento Intruso',
            'data_inicio' => '2026-07-10 08:00:00',
            'data_fim' => '2026-07-10 18:00:00',
            'local' => 'X',
            'valor' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('calendario.index', ['mes' => 7, 'ano' => 2026]));

        $response->assertOk();
        $response->assertDontSee('Evento Intruso');
    }
}
