<?php

namespace Database\Seeders;

use App\Support\EspecialidadesCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecialidadesSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = EspecialidadesCatalog::all();

        foreach ($especialidades as $esp) {
            DB::table('especialidades')->updateOrInsert(
                ['nome' => $esp['nome'], 'area' => $esp['area']],
                [
                    'codigo' => $esp['codigo'],
                    'url_oficial' => $esp['url_oficial'],
                    'is_oficial' => true,
                    'is_avancada' => $esp['is_avancada'],
                    'cor_fundo' => EspecialidadesCatalog::colorByArea($esp['area']),
                    'nome_search' => EspecialidadesCatalog::normalizeForSearch($esp['nome']),
                    'area_search' => EspecialidadesCatalog::normalizeForSearch($esp['area']),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
