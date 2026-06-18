<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2 da reestruturação multi-tenant: integridade referencial.
 *
 *  1. Cura/aborta ANTES de qualquer DDL — linhas com club_id nulo num banco de
 *     clube único são preenchidas com o único clube (legado single-tenant);
 *     se houver ambiguidade (vários clubes com nulos), aborta com orientação,
 *     sem ter tocado no schema ainda.
 *  2. Torna club_id NOT NULL nas tabelas puramente de tenant.
 *  3. Adiciona FK club_id → clubs com ON DELETE CASCADE onde ainda não existia.
 *
 * Portabilidade: o NOT NULL roda nos três bancos. A FK roda em MySQL/Postgres
 * (dev/produção); no SQLite (testes) ela é PULADA — o SQLite não adiciona FK a
 * tabela existente via ALTER, e a integridade ali é garantida pelo
 * `tenant:check-integrity`. users.club_id permanece nullable (platform admin).
 */
return new class extends Migration
{
    /** Tabelas que ganham FK nova de club_id (cascade). */
    private const COM_FK_NOVA = [
        'desbravadores',
        'frequencias',
        'mensalidades',
        'caixas',
        'patrimonios',
        'eventos',
        'atas',
        'atos',
        'ranking_snapshots',
    ];

    /**
     * Tabelas que já têm FK de club_id e precisam só do NOT NULL.
     *
     * `invitations` NÃO entra: o convite de bootstrap (primeiro diretor de um
     * sistema novo) é criado SEM clube — o usuário cria o clube depois. club_id
     * nulo ali é estado legítimo, então a coluna permanece nullable.
     */
    private const FK_JA_EXISTE = [
        'unidades',
    ];

    public function up(): void
    {
        $todas = array_merge(self::COM_FK_NOVA, self::FK_JA_EXISTE);

        // 1. Cura ou aborta — antes de mexer no schema.
        foreach ($todas as $tabela) {
            $this->curarOuAbortar($tabela);
        }

        // 2. NOT NULL.
        foreach ($todas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->unsignedBigInteger('club_id')->nullable(false)->change();
            });
        }

        // 3. FK com cascade (MySQL/Postgres; SQLite não suporta ADD via ALTER).
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (self::COM_FK_NOVA as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->foreign('club_id')->references('id')->on('clubs')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            foreach (self::COM_FK_NOVA as $tabela) {
                Schema::table($tabela, function (Blueprint $table) {
                    $table->dropForeign(['club_id']);
                });
            }
        }

        foreach (array_merge(self::COM_FK_NOVA, self::FK_JA_EXISTE) as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->unsignedBigInteger('club_id')->nullable()->change();
            });
        }
    }

    /**
     * Preenche club_id nulo quando há exatamente um clube (legado single-tenant);
     * aborta se houver ambiguidade, para não escolher clube errado.
     */
    private function curarOuAbortar(string $tabela): void
    {
        $nulos = DB::table($tabela)->whereNull('club_id')->count();

        if ($nulos === 0) {
            return;
        }

        $clubes = DB::table('clubs')->count();

        if ($clubes === 1) {
            DB::table($tabela)->whereNull('club_id')->update([
                'club_id' => DB::table('clubs')->value('id'),
            ]);

            return;
        }

        throw new RuntimeException(
            "Não é possível aplicar NOT NULL: a tabela '{$tabela}' tem {$nulos} linha(s) ".
            "com club_id nulo e há {$clubes} clube(s) no banco. ".
            "Rode 'php artisan tenant:upgrade-legacy' (ou corrija via tenant:check-integrity) antes de migrar."
        );
    }
};
