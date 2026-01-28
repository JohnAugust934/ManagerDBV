<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela para os dados do Clube
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cidade');
            $table->string('associacao')->nullable();
            $table->timestamps();
        });

        // 2. Tabela de Convites
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token', 32)->unique();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        // 3. Alterar Users (CORRIGIDO: Adicionado club_id)
        Schema::table('users', function (Blueprint $table) {
            // Adiciona a coluna de vínculo com o clube
            $table->foreignId('club_id')->nullable()->after('id')->constrained('clubs')->nullOnDelete();

            // Adiciona a flag de Master Admin
            $table->boolean('is_master')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['club_id']);
            $table->dropColumn(['club_id', 'is_master']);
        });
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('clubs');
    }
};
