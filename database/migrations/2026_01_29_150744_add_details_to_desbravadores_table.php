<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            // Verifica campo por campo antes de criar para evitar erro de duplicidade

            if (!Schema::hasColumn('desbravadores', 'ativo')) {
                $table->boolean('ativo')->default(true);
            }

            if (!Schema::hasColumn('desbravadores', 'email')) {
                $table->string('email')->nullable();
                $table->string('telefone')->nullable();
                $table->string('endereco')->nullable();

                $table->string('nome_responsavel')->nullable();
                $table->string('telefone_responsavel')->nullable();

                $table->string('tipo_sanguineo', 3)->nullable();
                $table->text('alergias')->nullable();
                $table->text('medicamentos_continuos')->nullable();
                $table->text('plano_saude')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('desbravadores', function (Blueprint $table) {
            $columns = [
                'ativo',
                'email',
                'telefone',
                'endereco',
                'nome_responsavel',
                'telefone_responsavel',
                'tipo_sanguineo',
                'alergias',
                'medicamentos_continuos',
                'plano_saude'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('desbravadores', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
