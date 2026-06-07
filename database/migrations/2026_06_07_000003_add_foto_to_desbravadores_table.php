<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->string('foto', 500)->nullable()->after('plano_saude');
        });
    }

    public function down(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
