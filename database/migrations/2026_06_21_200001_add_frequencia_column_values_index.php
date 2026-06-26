<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índice explícito em frequencia_column_values(frequencia_id).
 *
 * A unique constraint (frequencia_id, attendance_column_id) cobre prefixo
 * frequencia_id, mas planejadores de query de alguns SGBDs preferem um índice
 * dedicado para operações de agregação (SUM de pontos por frequência no ranking).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frequencia_column_values', function (Blueprint $table) {
            $table->index('frequencia_id', 'fcv_frequencia_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('frequencia_column_values', function (Blueprint $table) {
            $table->dropIndex('fcv_frequencia_id_index');
        });
    }
};
