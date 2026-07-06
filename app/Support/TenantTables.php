<?php

namespace App\Support;

/**
 * Registro ÚNICO das tabelas que compõem o dataset de um clube (tenant).
 *
 * Antes esta lista existia DUPLICADA e à mão em vários lugares (exclusão em
 * ClubLifecycleService, limpeza pré-restauração em ClubRestoreService, export em
 * ClubExportService), que divergiam entre si — p.ex. caixa_audit_logs faltava na
 * exclusão definitiva, orfanando linhas no SQLite (sem ON DELETE CASCADE).
 *
 * Aqui há uma classificação canônica de TODA tabela com club_id. O teste
 * TenantTablesTest garante que nenhuma tabela com club_id fique sem classificar —
 * ao criar uma tabela nova de tenant, o teste falha até ela ser incluída num dos
 * grupos, tornando a decisão consciente.
 */
class TenantTables
{
    /**
     * Tabelas com club_id direto que são removidas com o clube, em ordem SEGURA
     * de exclusão (filhas antes das mães). Fonte única consumida pela exclusão
     * definitiva (ClubLifecycleService) e pela limpeza pré-restauração
     * (ClubRestoreService).
     */
    public const CLUB_ID_TABLES = [
        'caixa_audit_logs', // referencia caixa_id (sem FK) — pode vir antes de caixas
        'frequencias',      // referencia desbravador_id → antes de desbravadores
        'mensalidades',     // referencia desbravador_id → antes de desbravadores
        'atos',             // referencia desbravador_id → antes de desbravadores
        'desbravadores',    // antes de unidades (unidade_id) e depois de seus filhos
        'eventos',
        'caixas',
        'patrimonios',
        'atas',
        'attendance_columns',
        'ranking_snapshots',
        'invitations',
        'unidades',
    ];

    /**
     * Pivôs/filhos SEM club_id próprio, removidos via os IDs do pai ANTES das
     * CLUB_ID_TABLES. Formato: tabela => [fk, via (tabela pai com club_id)].
     */
    public const CHILD_TABLES = [
        'frequencia_column_values' => ['fk' => 'frequencia_id', 'via' => 'frequencias'],
        'desbravador_evento' => ['fk' => 'desbravador_id', 'via' => 'desbravadores'],
        'desbravador_especialidade' => ['fk' => 'desbravador_id', 'via' => 'desbravadores'],
        'desbravador_requisito' => ['fk' => 'desbravador_id', 'via' => 'desbravadores'],
        'patrimonio_manutencoes' => ['fk' => 'patrimonio_id', 'via' => 'patrimonios'],
    ];

    /**
     * Tabelas com club_id deliberadamente FORA da exclusão/limpeza em cascata,
     * por serem tratadas à parte ou retidas por política. Listadas aqui só para o
     * teste de cobertura enxergar toda tabela com club_id classificada.
     */
    public const EXCLUDED_TABLES = [
        'clubs',                      // a própria raiz do tenant
        'users',                      // removidos à parte (platform admin tem club_id null)
        'club_backup_logs',           // trilha operacional de backup
        'comunicados',
        'consentimentos_privacidade', // retenção LGPD
        'lgpd_registros',             // ROPA / retenção LGPD
        'relatorio_gerados',
    ];

    /**
     * @return list<string>
     */
    public static function clubIdTables(): array
    {
        return self::CLUB_ID_TABLES;
    }

    /**
     * @return array<string, array{fk:string, via:string}>
     */
    public static function childTables(): array
    {
        return self::CHILD_TABLES;
    }

    /**
     * Todas as tabelas com club_id conhecidas e classificadas (para o teste de
     * cobertura confrontar com o schema real).
     *
     * @return list<string>
     */
    public static function allKnown(): array
    {
        return array_values(array_unique(array_merge(
            self::CLUB_ID_TABLES,
            self::EXCLUDED_TABLES,
        )));
    }
}
