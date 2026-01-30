<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Club;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcessoTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_acessa_tudo()
    {
        $user = User::factory()->create(['role' => 'master']);

        $this->actingAs($user)->get(route('usuarios.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('caixa.index'))->assertStatus(200);
    }

    public function test_tesoureiro_acessa_caixa_mas_nao_atas()
    {
        $user = User::factory()->create(['role' => 'tesoureiro']);

        $this->actingAs($user)->get(route('caixa.index'))->assertStatus(200);
        $this->actingAs($user)->get(route('atas.index'))->assertStatus(403); // Forbidden
    }

    public function test_conselheiro_com_permissao_extra_acessa_caixa()
    {
        $user = User::factory()->create([
            'role' => 'conselheiro',
            'extra_permissions' => ['financeiro']
        ]);

        $this->actingAs($user)->get(route('caixa.index'))->assertStatus(200);
    }

    public function test_conselheiro_sem_permissao_nao_acessa_caixa()
    {
        $user = User::factory()->create(['role' => 'conselheiro']);

        $this->actingAs($user)->get(route('caixa.index'))->assertStatus(403);
    }
}
