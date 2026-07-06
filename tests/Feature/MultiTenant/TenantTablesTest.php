<?php

namespace Tests\Feature\MultiTenant;

use App\Support\TenantTables;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Guarda do registro único de tabelas de tenant (Candidato B).
 *
 * Garante que TODA tabela com coluna club_id esteja classificada em algum grupo
 * de App\Support\TenantTables. Ao criar uma tabela de tenant nova e esquecer de
 * classificá-la, este teste falha — forçando a decisão consciente entre remover
 * com o clube (CLUB_ID_TABLES) ou reter/tratar à parte (EXCLUDED_TABLES).
 */
class TenantTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_toda_tabela_com_club_id_esta_classificada_no_registro(): void
    {
        $comClubId = collect(Schema::getTables())
            ->pluck('name')
            ->filter(fn (string $t) => Schema::hasColumn($t, 'club_id'))
            ->values();

        $classificadas = collect(TenantTables::allKnown());
        $naoClassificadas = $comClubId->diff($classificadas)->values()->all();

        $this->assertSame(
            [],
            $naoClassificadas,
            'Tabelas com club_id sem classificação em TenantTables: '.implode(', ', $naoClassificadas)
        );
    }

    public function test_grupos_do_registro_nao_se_sobrepoem(): void
    {
        $intersecao = array_intersect(TenantTables::CLUB_ID_TABLES, TenantTables::EXCLUDED_TABLES);

        $this->assertSame([], array_values($intersecao), 'Uma tabela não pode estar em CLUB_ID_TABLES e EXCLUDED_TABLES ao mesmo tempo.');
    }

    public function test_todas_as_tabelas_do_registro_existem_no_schema(): void
    {
        $tabelas = array_merge(
            TenantTables::clubIdTables(),
            array_keys(TenantTables::childTables()),
        );

        foreach ($tabelas as $tabela) {
            $this->assertTrue(Schema::hasTable($tabela), "Tabela {$tabela} do registro não existe no schema.");
        }
    }
}
