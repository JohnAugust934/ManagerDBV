<?php

namespace Tests\Feature\MultiTenant;

use App\Models\Ata;
use App\Models\Ato;
use App\Models\AttendanceColumn;
use App\Models\Caixa;
use App\Models\CaixaAuditLog;
use App\Models\Comunicado;
use App\Models\ConsentimentoPrivacidade;
use App\Models\Desbravador;
use App\Models\Evento;
use App\Models\Frequencia;
use App\Models\Mensalidade;
use App\Models\Patrimonio;
use App\Models\RankingSnapshot;
use App\Models\RelatorioGerado;
use App\Models\Scopes\ClubScope;
use App\Models\Unidade;
use Tests\TestCase;

/**
 * Guarda de regressão do isolamento multi-tenant.
 *
 * Toda a segurança de tenant repousa no global scope ClubScope. Se alguém remover
 * o trait BelongsToTenant (ou o addGlobalScope) de um model de dados de clube, o
 * vazamento cross-tenant é SILENCIOSO — nenhuma outra camada (não há Policies) o
 * pegaria. Este teste falha imediatamente nesse caso.
 *
 * Ao adicionar um novo model com dados de clube, inclua-o na lista abaixo. O
 * TenantTablesTest, de forma complementar, já força a classificação de toda tabela
 * nova com club_id — juntos, tornam a decisão consciente.
 */
class GlobalScopeRegressaoTest extends TestCase
{
    /**
     * Models que DEVEM aplicar o ClubScope automaticamente.
     *
     * @var array<int, class-string>
     */
    private const MODELS_DE_TENANT = [
        Ata::class,
        Ato::class,
        AttendanceColumn::class,
        Caixa::class,
        CaixaAuditLog::class,
        Comunicado::class,
        ConsentimentoPrivacidade::class,
        Desbravador::class,
        Evento::class,
        Frequencia::class,
        Mensalidade::class,
        Patrimonio::class,
        RankingSnapshot::class,
        RelatorioGerado::class,
        Unidade::class,
    ];

    public function test_models_de_tenant_registram_o_club_scope(): void
    {
        foreach (self::MODELS_DE_TENANT as $modelClass) {
            $scopes = (new $modelClass)->getGlobalScopes();

            $this->assertArrayHasKey(
                ClubScope::class,
                $scopes,
                "{$modelClass} deve registrar o ClubScope (via trait BelongsToTenant ou addGlobalScope). ".
                'Sem ele, esse model vaza dados entre clubes silenciosamente.'
            );
        }
    }
}
