<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adiciona coluna de papel (padrão: conselheiro para novos, mas vamos migrar)
            $table->string('role')->default('conselheiro')->after('email');

            // Coluna JSON para permissões extras (ex: ['acessar_financeiro'])
            $table->json('extra_permissions')->nullable()->after('role');
        });

        // Migrar dados antigos: Quem era is_master = true vira 'master', o resto vira 'diretor' (para não quebrar acessos atuais)
        \DB::table('users')->where('is_master', true)->update(['role' => 'master']);
        \DB::table('users')->where('is_master', false)->update(['role' => 'diretor']);

        // Opcional: Remover is_master se não for mais usar, ou manter por compatibilidade
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropColumn('is_master');
        // });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'extra_permissions']);
        });
    }
};
