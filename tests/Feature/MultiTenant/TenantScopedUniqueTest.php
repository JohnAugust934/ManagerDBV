<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Desbravador;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 3 — unicidade escopada por tenant. CPF é único POR CLUBE: a mesma pessoa
 * pode estar cadastrada em clubes diferentes, mas não duas vezes no mesmo clube.
 */
class TenantScopedUniqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_cpf_pode_repetir_entre_clubes(): void
    {
        ['unidade' => $unidadeA] = criarClubeComDados('Clube A');
        ['unidade' => $unidadeB] = criarClubeComDados('Clube B');

        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'cpf' => '111.111.111-11']);
        Desbravador::factory()->create(['unidade_id' => $unidadeB->id, 'cpf' => '111.111.111-11']);

        $this->assertSame(2, Desbravador::withoutGlobalScopes()->where('cpf', '111.111.111-11')->count());
    }

    public function test_cpf_duplicado_no_mesmo_clube_e_rejeitado(): void
    {
        ['unidade' => $unidadeA] = criarClubeComDados('Clube A');
        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'cpf' => '111.111.111-11']);

        $this->expectException(QueryException::class);
        Desbravador::factory()->create(['unidade_id' => $unidadeA->id, 'cpf' => '111.111.111-11']);
    }

    public function test_cpf_nulo_pode_repetir_no_mesmo_clube(): void
    {
        ['unidade' => $unidade] = criarClubeComDados('Clube A');

        // NULLs são distintos no índice unique — vários desbravadores sem CPF coexistem.
        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'cpf' => null]);
        Desbravador::factory()->create(['unidade_id' => $unidade->id, 'cpf' => null]);

        $this->assertSame(2, Desbravador::withoutGlobalScopes()->whereNull('cpf')->count());
    }
}
