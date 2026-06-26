<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca quando o usuário dispensou o convite (banner) para cadastrar uma passkey.
 * Persistir por usuário garante que o banner não reapareça após a dispensa,
 * independentemente do dispositivo/navegador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('passkey_banner_dispensado_em')->nullable()->after('termos_aceitos_em');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('passkey_banner_dispensado_em');
        });
    }
};
