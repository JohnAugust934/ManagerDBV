<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices de performance em club_id nas tabelas multi-tenant que ainda não os
 * tinham. As tabelas financeiras/eventos/atas/atos já receberam índices em
 * migrations anteriores; aqui completamos attendance_columns e invitations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_columns', function (Blueprint $table) {
            $table->index('club_id', 'attendance_columns_club_id_index');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->index('club_id', 'invitations_club_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_columns', function (Blueprint $table) {
            $table->dropIndex('attendance_columns_club_id_index');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex('invitations_club_id_index');
        });
    }
};
