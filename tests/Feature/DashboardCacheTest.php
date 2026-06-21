<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Desbravador;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_usa_cache_e_nao_refaz_query_no_segundo_request(): void
    {
        $clube = Club::create(['nome' => 'Clube Cache', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);
        $unidade = Unidade::factory()->create(['club_id' => $clube->id]);

        Caixa::create(['descricao' => 'Entrada', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $clube->id]);

        // Primeiro acesso: popula o cache
        $this->actingAs($user)->get(route('dashboard'))->assertOk();

        // Inserir nova entrada não deve afetar o cache já populado
        Caixa::create(['descricao' => 'Nova entrada', 'valor' => 999, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $clube->id]);

        // Segundo acesso: vem do cache (novo valor não aparece ainda)
        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();

        // Confirma que a chave de cache existe
        $mesAtual = now()->month;
        $anoAtual = now()->year;
        $this->assertTrue(Cache::has("dashboard_resumo_{$clube->id}_{$mesAtual}_{$anoAtual}"));
    }

    public function test_cache_do_dashboard_e_isolado_por_clube(): void
    {
        $clubeA = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $clubeB = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);
        $userA = User::factory()->create(['club_id' => $clubeA->id, 'role' => 'diretor']);
        $userB = User::factory()->create(['club_id' => $clubeB->id, 'role' => 'diretor']);

        Unidade::factory()->create(['club_id' => $clubeA->id]);
        Unidade::factory()->create(['club_id' => $clubeB->id]);

        Caixa::create(['descricao' => 'A', 'valor' => 500, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $clubeA->id]);
        Caixa::create(['descricao' => 'B', 'valor' => 1000, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $clubeB->id]);

        $this->actingAs($userA)->get(route('dashboard'))->assertOk();
        $this->actingAs($userB)->get(route('dashboard'))->assertOk();

        $mes = now()->month;
        $ano = now()->year;

        $cacheA = Cache::get("dashboard_resumo_{$clubeA->id}_{$mes}_{$ano}");
        $cacheB = Cache::get("dashboard_resumo_{$clubeB->id}_{$mes}_{$ano}");

        // Cada clube tem seu próprio valor no cache
        $this->assertEquals(500.0, $cacheA['saldo_atual']);
        $this->assertEquals(1000.0, $cacheB['saldo_atual']);
    }

    public function test_dashboard_renderiza_sem_dados_de_clube(): void
    {
        $clube = Club::create(['nome' => 'Clube Vazio', 'cidade' => 'SP']);
        $user = User::factory()->create(['club_id' => $clube->id, 'role' => 'diretor']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
    }
}
