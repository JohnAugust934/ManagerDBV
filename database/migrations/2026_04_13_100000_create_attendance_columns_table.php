<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
            $table->string('key')->nullable();
            $table->string('name');
            $table->unsignedTinyInteger('points')->default(1);
            $table->boolean('is_fixed')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();

            $table->unique(['club_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_columns');
    }
};

