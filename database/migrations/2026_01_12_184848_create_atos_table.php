<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atos', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->string('tipo'); // Nomeação, Exoneração, Admissão, Disciplina, Outro
            $table->string('descricao_resumida'); // Ex: Nomeação de Capitão
            $table->text('texto_completo')->nullable(); // O texto oficial do ato
            // Opcional: Vincular a um desbravador específico
            $table->foreignId('desbravador_id')->nullable()->constrained('desbravadores')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atos');
    }
};
