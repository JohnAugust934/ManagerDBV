<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrimonios', function (Blueprint $table) {
            $table->id();
            $table->string('item'); // Ex: Barraca Iglu 4 Pessoas
            $table->integer('quantidade')->default(1);
            $table->decimal('valor_estimado', 10, 2)->nullable(); // Quanto vale hoje?
            $table->date('data_aquisicao')->nullable();
            $table->string('estado_conservacao'); // Novo, Bom, Regular, Ruim, Inservível
            $table->string('local_armazenamento')->nullable(); // Ex: Armário A, Sede da Igreja
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrimonios');
    }
};
