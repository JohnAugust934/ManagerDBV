<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1 da reestruturação multi-tenant: desnormaliza `club_id` nas três tabelas
 * de tenant mais consultadas e antes isoladas indiretamente (subquery por relação):
 *  - desbravadores  (antes: via unidade.club_id)
 *  - frequencias    (antes: SEM scope — vazava entre clubes)
 *  - mensalidades   (antes: via desbravador.unidade.club_id)
 *
 * club_id entra como NULLABLE + índice. A FK e o NOT NULL ficam para a Fase 2
 * (não podem rodar aqui por causa da ordem de backfill do upgrade do legado).
 *
 * Backfill via UPDATE correlacionado — SQL padrão, portável em SQLite/MySQL/Postgres.
 * Ordem importa: desbravadores primeiro (frequencias/mensalidades leem o club_id dele).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('unidade_id');
            $table->index('club_id', 'desbravadores_club_id_index');
        });

        Schema::table('frequencias', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('desbravador_id');
            $table->index('club_id', 'frequencias_club_id_index');
        });

        Schema::table('mensalidades', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('desbravador_id');
            $table->index('club_id', 'mensalidades_club_id_index');
        });

        // Backfill — desbravadores herdam o clube da sua unidade...
        DB::statement('UPDATE desbravadores SET club_id = (
            SELECT u.club_id FROM unidades u WHERE u.id = desbravadores.unidade_id
        ) WHERE unidade_id IS NOT NULL');

        // ...e frequências/mensalidades herdam do desbravador.
        DB::statement('UPDATE frequencias SET club_id = (
            SELECT d.club_id FROM desbravadores d WHERE d.id = frequencias.desbravador_id
        ) WHERE desbravador_id IS NOT NULL');

        DB::statement('UPDATE mensalidades SET club_id = (
            SELECT d.club_id FROM desbravadores d WHERE d.id = mensalidades.desbravador_id
        ) WHERE desbravador_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropIndex('desbravadores_club_id_index');
            $table->dropColumn('club_id');
        });

        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropIndex('frequencias_club_id_index');
            $table->dropColumn('club_id');
        });

        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropIndex('mensalidades_club_id_index');
            $table->dropColumn('club_id');
        });
    }
};
