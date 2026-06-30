<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAlertasTest extends TestCase
{
    use RefreshDatabase;

    private function mensalidadeAtrasada(Club $clube): void
    {
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);
        $desb = Desbravador::factory()->create([
            'unidade_id' => $unidade->id,
            'club_id' => $clube->id,
            'ativo' => true,
        ]);

        $mesPassado = now()->subMonthNoOverflow();
        Mensalidade::create([
            'desbravador_id' => $desb->id,
            'club_id' => $clube->id,
            'mes' => $mesPassado->month,
            'ano' => $mesPassado->year,
            'valor' => 15.00,
            'status' => 'pendente',
        ]);
    }

    public function test_tesoureiro_ve_alerta_de_mensalidades_atrasadas()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $this->mensalidadeAtrasada($clube);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Mensalidades atrasadas');
    }

    public function test_secretario_sem_financeiro_nao_ve_alerta_financeiro()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $this->mensalidadeAtrasada($clube);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Mensalidades atrasadas');
    }
}
