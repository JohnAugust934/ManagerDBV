<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Da aos admins de plataforma um cargo proprio ('platform_admin'), distinto
     * do 'master' dono de clube. Antes ambos compartilhavam role='master', o que
     * tornava a "tag" ambigua na gestao de usuarios.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('is_platform_admin', true)
            ->update([
                'role' => 'platform_admin',
                'is_master' => false,
            ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('is_platform_admin', true)
            ->update([
                'role' => 'master',
                'is_master' => true,
            ]);
    }
};
