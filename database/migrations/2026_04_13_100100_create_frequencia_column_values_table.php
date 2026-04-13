<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frequencia_column_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frequencia_id')->constrained('frequencias')->onDelete('cascade');
            $table->foreignId('attendance_column_id')->constrained('attendance_columns')->onDelete('cascade');
            $table->boolean('checked')->default(false);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamps();

            $table->unique(['frequencia_id', 'attendance_column_id'], 'frequencia_column_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frequencia_column_values');
    }
};

