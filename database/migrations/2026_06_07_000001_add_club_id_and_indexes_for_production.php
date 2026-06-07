<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Adiciona club_id às tabelas financeiras e de eventos
        Schema::table('caixas', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('categoria');
            $table->index('club_id', 'caixas_club_id_index');
        });

        Schema::table('patrimonios', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('observacoes');
            $table->index('club_id', 'patrimonios_club_id_index');
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('descricao');
            $table->index('club_id', 'eventos_club_id_index');
        });

        // 2. Índices de performance na tabela desbravadores
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->index('ativo', 'desbravadores_ativo_index');
            $table->index('unidade_id', 'desbravadores_unidade_id_index');
            $table->index('nome', 'desbravadores_nome_index');
        });

        // 3. Índice de performance na tabela frequencias (queries por data/ano/mês)
        Schema::table('frequencias', function (Blueprint $table) {
            $table->index('data', 'frequencias_data_index');
        });

        // 4. Índices de performance na tabela mensalidades
        Schema::table('mensalidades', function (Blueprint $table) {
            $table->index(['mes', 'ano'], 'mensalidades_mes_ano_index');
            $table->index('status', 'mensalidades_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('caixas', function (Blueprint $table) {
            $table->dropIndex('caixas_club_id_index');
            $table->dropColumn('club_id');
        });

        Schema::table('patrimonios', function (Blueprint $table) {
            $table->dropIndex('patrimonios_club_id_index');
            $table->dropColumn('club_id');
        });

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropIndex('eventos_club_id_index');
            $table->dropColumn('club_id');
        });

        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropIndex('desbravadores_ativo_index');
            $table->dropIndex('desbravadores_unidade_id_index');
            $table->dropIndex('desbravadores_nome_index');
        });

        Schema::table('frequencias', function (Blueprint $table) {
            $table->dropIndex('frequencias_data_index');
        });

        Schema::table('mensalidades', function (Blueprint $table) {
            $table->dropIndex('mensalidades_mes_ano_index');
            $table->dropIndex('mensalidades_status_index');
        });
    }
};
