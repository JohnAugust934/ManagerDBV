<?php

namespace Tests\Feature;

use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Patrimonio;
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

    public function test_tesoureiro_nao_pode_acessar_acoes_destrutivas_da_secretaria()
    {
        $tesoureiro = User::factory()->create(['role' => 'tesoureiro']);
        $desbravador = Desbravador::factory()->create();
        $evento = Evento::factory()->create();

        $this->actingAs($tesoureiro)->delete(route('desbravadores.destroy', $desbravador))->assertStatus(403);
        $this->actingAs($tesoureiro)->delete(route('eventos.destroy', $evento))->assertStatus(403);
    }

    public function test_secretario_nao_pode_executar_acoes_financeiras_sensiveis()
    {
        $secretario = User::factory()->create(['role' => 'secretario']);
        $patrimonio = Patrimonio::factory()->create();

        $this->actingAs($secretario)->post(route('caixa.store'), [
            'descricao' => 'Teste',
            'valor' => 10,
            'tipo' => 'entrada',
            'data_movimentacao' => now()->toDateString(),
        ])->assertStatus(403);

        $this->actingAs($secretario)->delete(route('patrimonio.destroy', $patrimonio))->assertStatus(403);
    }
}
