<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atas', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('id');
            $table->index('club_id', 'atas_club_id_index');
        });

        Schema::table('atos', function (Blueprint $table) {
            $table->unsignedBigInteger('club_id')->nullable()->after('id');
            $table->index('club_id', 'atos_club_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('atas', function (Blueprint $table) {
            $table->dropIndex('atas_club_id_index');
            $table->dropColumn('club_id');
        });

        Schema::table('atos', function (Blueprint $table) {
            $table->dropIndex('atos_club_id_index');
            $table->dropColumn('club_id');
        });
    }
};
