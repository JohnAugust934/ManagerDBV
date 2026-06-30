<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_de_instalacao_presente_no_layout()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'secretario']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('pwaInstall()', false);
    }

    public function test_indicador_offline_na_tela_de_chamada()
    {
        $clube = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'instrutor']);

        $this->actingAs($user)->get(route('frequencia.create'))
            ->assertOk()
            ->assertSee('Modo offline');
    }
}
