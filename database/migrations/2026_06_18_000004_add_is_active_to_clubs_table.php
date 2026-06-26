<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 6 — ciclo de vida do clube. `is_active` controla o acesso ao clube:
 * quando false, nenhum usuário vinculado consegue entrar (baixa/suspensão
 * comercial ou administrativa). Só o platform admin altera. Clubes existentes
 * nascem ativos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
