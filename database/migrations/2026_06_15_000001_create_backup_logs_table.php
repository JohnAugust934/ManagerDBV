<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_logs', function (Blueprint $table) {
            $table->id();
            // Identificacao do backup verificado.
            $table->string('disk', 50);
            $table->string('backup_name')->nullable();
            $table->string('filename')->nullable();
            $table->string('path')->nullable();
            // Resultado da verificacao de integridade.
            $table->string('status', 20)->default('success'); // success | failed
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedInteger('files_count')->default(0);
            $table->string('checksum', 64)->nullable(); // SHA-256 do zip
            $table->boolean('has_database')->default(false);
            $table->boolean('has_uploads')->default(false);
            // Detalhes: problemas (quando falha) e manifest completo com checksums por arquivo.
            $table->text('problems')->nullable();
            $table->json('manifest')->nullable();
            $table->timestamps();

            $table->index(['disk', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_logs');
    }
};
