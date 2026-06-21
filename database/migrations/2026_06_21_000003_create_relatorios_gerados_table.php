<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatorio_gerados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo', 50);
            $table->string('status', 20)->default('pendente'); // pendente, processando, pronto, erro
            $table->string('arquivo')->nullable();             // path relativo em storage/app/private
            $table->json('filtros')->nullable();
            $table->text('erro')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status'], 'relatorio_gerados_club_status_idx');
            $table->index('expires_at', 'relatorio_gerados_expires_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorio_gerados');
    }
};
