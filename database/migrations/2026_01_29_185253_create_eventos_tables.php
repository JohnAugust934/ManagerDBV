<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Eventos
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim')->nullable();
            $table->string('local');
            $table->decimal('valor', 10, 2)->default(0); // Custo para o desbravador
            $table->text('descricao')->nullable();
            $table->timestamps();
        });

        // 2. Tabela de Inscrições (Pivot)
        Schema::create('desbravador_evento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('desbravador_id')->constrained('desbravadores')->onDelete('cascade');

            $table->boolean('pago')->default(false); // Financeiro vinculado
            $table->boolean('autorizacao_entregue')->default(false); // Controle de secretaria

            $table->timestamps();

            $table->unique(['evento_id', 'desbravador_id']); // Não pode se inscrever 2x
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desbravador_evento');
        Schema::dropIfExists('eventos');
    }
};
