<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula opcionalmente a unidade a um usuário do sistema (conselheiro responsável).
     * Mantém a coluna `conselheiro` (texto livre) para exibição e compatibilidade —
     * nem todo conselheiro possui login no sistema.
     */
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->foreignId('conselheiro_user_id')
                ->nullable()
                ->after('conselheiro')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('conselheiro_user_id');
        });
    }
};
