<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Mensalidade;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MensalidadePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_conta_novas_e_existentes()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'tesoureiro']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);

        $desbs = Desbravador::factory()->count(3)->create([
            'unidade_id' => $unidade->id,
            'club_id' => $clube->id,
            'ativo' => true,
        ]);

        // Um já possui mensalidade para a competência.
        Mensalidade::create([
            'desbravador_id' => $desbs->first()->id,
            'club_id' => $clube->id,
            'mes' => 7,
            'ano' => 2026,
            'valor' => 15.00,
            'status' => 'pendente',
        ]);

        $response = $this->actingAs($user)->postJson(route('mensalidades.preview'), [
            'mes' => 7,
            'ano' => 2026,
            'valor' => 20,
        ]);

        $response->assertOk()->assertJson([
            'total_ativos' => 3,
            'ja_existem' => 1,
            'serao_criadas' => 2,
            'competencia' => '07/2026',
        ]);
    }

    public function test_preview_exige_permissao_financeiro()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $this->actingAs($user)
            ->postJson(route('mensalidades.preview'), ['mes' => 7, 'ano' => 2026, 'valor' => 20])
            ->assertForbidden();
    }
}
