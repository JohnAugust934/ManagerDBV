<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Classes (Amigo, Companheiro...)
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cor')->default('#000000');
            $table->integer('ordem');
            $table->timestamps();
        });

        // 2. Tabela de Requisitos (As tarefas)
        Schema::create('requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade');
            $table->string('codigo')->nullable();
            $table->text('descricao');
            $table->string('categoria')->default('Gerais');
            $table->timestamps();
        });

        // 3. Tabela de Progresso (Quem fez o quê)
        Schema::create('desbravador_requisito', function (Blueprint $table) {
            $table->id();

            // CORREÇÃO AQUI: Especificamos 'desbravadores' explicitamente
            $table->foreignId('desbravador_id')->constrained('desbravadores')->onDelete('cascade');

            $table->foreignId('requisito_id')->constrained('requisitos')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users'); // Quem assinou
            $table->date('data_conclusao')->default(now());
            $table->timestamps();

            $table->unique(['desbravador_id', 'requisito_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desbravador_requisito');
        Schema::dropIfExists('requisitos');
        Schema::dropIfExists('classes');
    }
};
