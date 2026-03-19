<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranking_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('year');
            $table->string('scope', 50);
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('entries');
            $table->timestamp('generated_at');
            $table->timestamps();

            $table->unique(['year', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_snapshots');
    }
};
