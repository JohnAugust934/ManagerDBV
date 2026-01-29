<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            // Adiciona o SUS após o telefone do responsável
            $table->string('numero_sus')->nullable()->after('telefone_responsavel');
        });
    }

    public function down(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropColumn('numero_sus');
        });
    }
};
