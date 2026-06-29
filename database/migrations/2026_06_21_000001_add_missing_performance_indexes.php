<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Consultas de chamada/frequência filtram unidade_id em desbravadores com frecuência;
        // o índice composto (club_id, unidade_id) cobre o filtro mais comum do ClubScope (BelongsToTenant)
        // + filtro de unidade simultâneo.
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->index(['club_id', 'unidade_id'], 'desbravadores_club_unidade_idx');
        });
    }

    public function down(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropIndex('desbravadores_club_unidade_idx');
        });
    }
};
