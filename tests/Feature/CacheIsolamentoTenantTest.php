<?php

namespace Tests\Feature;

use App\Models\Caixa;
use App\Models\Club;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Verifica que o cache respeita o isolamento multi-tenant.
 *
 * Invariante: NUNCA cachear dados de tenant sem o club_id na chave.
 * Migrando de database → redis (Parte 5), esse contrato deve se manter.
 */
class CacheIsolamentoTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_chaves_de_cache_do_dashboard_incluem_club_id(): void
    {
        $clubeA = Club::create(['nome' => 'Clube A', 'cidade' => 'SP']);
        $clubeB = Club::create(['nome' => 'Clube B', 'cidade' => 'RJ']);

        $userA = User::factory()->create(['club_id' => $clubeA->id, 'role' => 'diretor']);
        $userB = User::factory()->create(['club_id' => $clubeB->id, 'role' => 'diretor']);

        Unidade::factory()->create(['club_id' => $clubeA->id]);
        Unidade::factory()->create(['club_id' => $clubeB->id]);

        Caixa::create(['descricao' => 'A', 'valor' => 100, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $clubeA->id]);
        Caixa::create(['descricao' => 'B', 'valor' => 500, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $clubeB->id]);

        $this->actingAs($userA)->get(route('dashboard'))->assertOk();
        $this->actingAs($userB)->get(route('dashboard'))->assertOk();

        $mes = now()->month;
        $ano = now()->year;

        // Cache do clube A NÃO contém dados do clube B
        $cacheA = Cache::get("dashboard_resumo_{$clubeA->id}_{$mes}_{$ano}");
        $cacheB = Cache::get("dashboard_resumo_{$clubeB->id}_{$mes}_{$ano}");

        $this->assertNotNull($cacheA, 'Cache do clube A deve existir');
        $this->assertNotNull($cacheB, 'Cache do clube B deve existir');

        // Valores isolados — cada clube enxerga apenas suas próprias movimentações
        $this->assertEquals(100.0, $cacheA['saldo_atual'], 'Clube A deve ver apenas seus próprios dados');
        $this->assertEquals(500.0, $cacheB['saldo_atual'], 'Clube B deve ver apenas seus próprios dados');
    }

    public function test_cache_do_clube_a_nao_e_servido_para_clube_b(): void
    {
        $clubeA = Club::create(['nome' => 'Cache A', 'cidade' => 'SP']);
        $clubeB = Club::create(['nome' => 'Cache B', 'cidade' => 'RJ']);

        $userA = User::factory()->create(['club_id' => $clubeA->id, 'role' => 'diretor']);
        $userB = User::factory()->create(['club_id' => $clubeB->id, 'role' => 'diretor']);

        Unidade::factory()->create(['club_id' => $clubeA->id]);
        Unidade::factory()->create(['club_id' => $clubeB->id]);

        Caixa::create(['descricao' => 'Rec A', 'valor' => 200, 'tipo' => 'entrada', 'data_movimentacao' => now(), 'club_id' => $clubeA->id]);

        // Popula cache com dados do clube A
        $this->actingAs($userA)->get(route('dashboard'))->assertOk();

        // Clube B acessa o dashboard — deve enxergar apenas seus dados (saldo zero)
        $this->actingAs($userB)->get(route('dashboard'))->assertOk();

        $mes = now()->month;
        $ano = now()->year;
        $cacheB = Cache::get("dashboard_resumo_{$clubeB->id}_{$mes}_{$ano}");

        $this->assertEquals(0.0, $cacheB['saldo_atual'], 'Clube B não deve receber cache do clube A');
    }

    public function test_chaves_de_frequencia_incluem_club_id(): void
    {
        $clubeA = Club::create(['nome' => 'Freq A', 'cidade' => 'SP']);
        $clubeB = Club::create(['nome' => 'Freq B', 'cidade' => 'RJ']);

        $userA = User::factory()->create(['club_id' => $clubeA->id, 'role' => 'diretor']);
        $userB = User::factory()->create(['club_id' => $clubeB->id, 'role' => 'diretor']);

        Unidade::factory()->create(['club_id' => $clubeA->id]);
        Unidade::factory()->create(['club_id' => $clubeB->id]);

        $this->actingAs($userA)->get(route('dashboard'))->assertOk();
        $this->actingAs($userB)->get(route('dashboard'))->assertOk();

        // Chaves de frequência são distintas por clube
        $this->assertTrue(Cache::has("dashboard_frequencias_{$clubeA->id}"), 'Chave de frequência do clube A deve existir');
        $this->assertTrue(Cache::has("dashboard_frequencias_{$clubeB->id}"), 'Chave de frequência do clube B deve existir');

        // As chaves são diferentes (não compartilhadas entre clubes)
        $this->assertNotEquals(
            "dashboard_frequencias_{$clubeA->id}",
            "dashboard_frequencias_{$clubeB->id}",
        );
    }
}
