<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ranking_snapshots', function (Blueprint $table) {
            // Sem constraint FK explícita (compatibilidade com SQLite, igual às demais
            // colunas club_id do projeto). O vínculo lógico é com clubs.id.
            $table->unsignedBigInteger('club_id')->nullable()->after('scope');
            $table->index('club_id', 'ranking_snapshots_club_id_index');

            // A unique antiga (year, scope) impede dois clubes terem snapshot do mesmo ano/escopo.
            $table->dropUnique('ranking_snapshots_year_scope_unique');
            $table->unique(['year', 'scope', 'club_id'], 'ranking_snapshots_year_scope_club_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ranking_snapshots', function (Blueprint $table) {
            $table->dropUnique('ranking_snapshots_year_scope_club_unique');
            $table->dropIndex('ranking_snapshots_club_id_index');
            $table->dropColumn('club_id');
            $table->unique(['year', 'scope'], 'ranking_snapshots_year_scope_unique');
        });
    }
};
