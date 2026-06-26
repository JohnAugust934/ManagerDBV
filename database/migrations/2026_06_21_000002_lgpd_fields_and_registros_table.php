<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Consentimento LGPD Art. 14 — dado por responsável no cadastro do menor
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->boolean('consentimento_lgpd')->default(false)->after('foto');
            $table->timestamp('consentimento_lgpd_em')->nullable()->after('consentimento_lgpd');
            $table->string('consentimento_lgpd_responsavel')->nullable()->after('consentimento_lgpd_em');
            $table->boolean('usa_imagem_autorizado')->default(false)->after('consentimento_lgpd_responsavel');
        });

        // Aceite dos Termos de Uso / Política de Privacidade no cadastro de usuários
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('termos_aceitos_em')->nullable()->after('updated_at');
        });

        // Registro de Operações de Tratamento (ROPA) — Art. 37
        Schema::create('lgpd_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('acao', 50); // acesso, exportacao, exclusao, consentimento, retificacao, oposicao_imagem
            $table->string('entidade', 50); // desbravador, frequencia, ficha_medica
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->string('ip_origem', 45)->nullable();
            $table->json('metadados')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'acao'], 'lgpd_club_acao_idx');
            $table->index(['entidade', 'entidade_id'], 'lgpd_entidade_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lgpd_registros');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('termos_aceitos_em');
        });

        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropColumn(['consentimento_lgpd', 'consentimento_lgpd_em', 'consentimento_lgpd_responsavel', 'usa_imagem_autorizado']);
        });
    }
};
