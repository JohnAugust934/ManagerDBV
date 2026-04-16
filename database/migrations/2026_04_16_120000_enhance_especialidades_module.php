<?php

use App\Support\EspecialidadesCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especialidades', function (Blueprint $table) {
            $table->string('codigo', 20)->nullable()->after('area');
            $table->string('nome_search')->nullable()->after('nome');
            $table->string('area_search')->nullable()->after('area');
            $table->text('url_oficial')->nullable()->after('codigo');
            $table->boolean('is_oficial')->default(false)->after('url_oficial');
            $table->boolean('is_avancada')->default(false)->after('is_oficial');
            $table->unsignedBigInteger('created_by')->nullable()->after('cor_fundo');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });

        DB::table('especialidades')
            ->select('id', 'nome', 'area', 'cor_fundo')
            ->orderBy('id')
            ->chunkById(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('especialidades')
                        ->where('id', $row->id)
                        ->update([
                            'nome_search' => EspecialidadesCatalog::normalizeForSearch($row->nome),
                            'area_search' => EspecialidadesCatalog::normalizeForSearch($row->area),
                            'is_oficial' => true,
                            'is_avancada' => EspecialidadesCatalog::isAdvanced($row->nome),
                            'cor_fundo' => $row->cor_fundo ?: EspecialidadesCatalog::colorByArea($row->area),
                        ]);
                }
            });

        Schema::table('especialidades', function (Blueprint $table) {
            $table->index('codigo');
            $table->index('nome_search');
            $table->index('area_search');
            $table->index('is_oficial');
            $table->index('is_avancada');
            $table->index('created_by');
            $table->index('updated_by');
            $table->unique(['nome', 'area'], 'especialidades_nome_area_unique');
        });

        Schema::create('especialidade_requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('especialidade_id')->constrained('especialidades')->cascadeOnDelete();
            $table->unsignedInteger('ordem')->default(1);
            $table->text('descricao');
            $table->timestamps();

            $table->unique(['especialidade_id', 'ordem'], 'especialidade_requisitos_unique_ordem');
        });

        Schema::create('especialidade_auditorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('especialidade_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('acao', 30);
            $table->json('dados')->nullable();
            $table->timestamps();

            $table->index('especialidade_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidade_auditorias');
        Schema::dropIfExists('especialidade_requisitos');

        Schema::table('especialidades', function (Blueprint $table) {
            $table->dropUnique('especialidades_nome_area_unique');
            $table->dropIndex(['codigo']);
            $table->dropIndex(['nome_search']);
            $table->dropIndex(['area_search']);
            $table->dropIndex(['is_oficial']);
            $table->dropIndex(['is_avancada']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);

            $table->dropColumn([
                'codigo',
                'nome_search',
                'area_search',
                'url_oficial',
                'is_oficial',
                'is_avancada',
                'created_by',
                'updated_by',
            ]);
        });
    }
};
