<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comunicados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id')->index();
            $table->unsignedBigInteger('criado_por')->nullable();
            $table->string('titulo');
            $table->text('corpo');
            $table->string('destinatarios')->default('todos'); // 'todos', 'ativos', 'unidade'
            $table->unsignedBigInteger('unidade_id')->nullable();
            $table->integer('total_enviados')->default(0);
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
            $table->foreign('criado_por')->references('id')->on('users')->nullOnDelete();
            $table->foreign('unidade_id')->references('id')->on('unidades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comunicados');
    }
};
