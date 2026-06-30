<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('caixas', function (Blueprint $table) {
            // Vincula a entrada de caixa à mensalidade que a originou, permitindo
            // estorno confiável (sem casar por descrição/valor). Nullable: lançamentos
            // manuais não têm mensalidade. nullOnDelete preserva o registro financeiro
            // caso a mensalidade seja excluída.
            $table->foreignId('mensalidade_id')
                ->nullable()
                ->after('club_id')
                ->constrained('mensalidades')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('caixas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mensalidade_id');
        });
    }
};
