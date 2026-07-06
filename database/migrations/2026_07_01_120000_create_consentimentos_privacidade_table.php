<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico auditável de consentimento LGPD para tratamento de dados do
 * desbravador (menor de idade). Cada linha é imutável em termos de auditoria:
 * a revogação é um NOVO estado (revogado_em), nunca um DELETE/UPDATE do aceite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consentimentos_privacidade', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('desbravador_id')->constrained('desbravadores')->cascadeOnDelete();

            // Snapshot do nome do responsável no momento do aceite (não há model
            // Guardian; o responsável é campo inline do desbravador).
            $table->string('responsavel_nome')->nullable();

            $table->string('versao_termo'); // ex.: "2026.1" — permite reconsentimento quando o termo muda
            $table->text('termo_snapshot');  // texto do termo aceito, para auditoria

            $table->timestamp('aceito_em')->nullable();
            $table->string('aceito_ip')->nullable();

            $table->timestamp('revogado_em')->nullable();
            $table->string('revogado_por')->nullable(); // nome do usuário que formalizou a revogação
            $table->text('motivo_revogacao')->nullable();

            $table->timestamp('via_fisica_recebida_em')->nullable();
            $table->string('via_fisica_caminho')->nullable();

            $table->timestamps();

            $table->index(['club_id', 'desbravador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimentos_privacidade');
    }
};
