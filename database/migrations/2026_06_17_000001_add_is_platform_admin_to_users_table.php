<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Super admin de plataforma (cross-tenant). Distinto do "master" (dono do clube).
            // Platform admins têm club_id = null e enxergam todos os clubes.
            $table->boolean('is_platform_admin')->default(false)->after('is_master');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_platform_admin');
        });
    }
};
