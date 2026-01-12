<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atas', function (Blueprint $table) {
            $table->id();
            $table->date('data_reuniao');
            $table->string('tipo'); // Diretoria, Regular, Campori
            $table->string('secretario_responsavel')->nullable(); // Quem escreveu
            $table->text('participantes')->nullable(); // Lista de nomes
            $table->text('conteudo'); // O texto da ata
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atas');
    }
};
