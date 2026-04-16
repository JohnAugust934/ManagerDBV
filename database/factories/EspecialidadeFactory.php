<?php

namespace Database\Factories;

use App\Support\EspecialidadesCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;

class EspecialidadeFactory extends Factory
{
    public function definition(): array
    {
        $nome = $this->faker->words(3, true);
        $area = $this->faker->randomElement([
            'Estudos da Natureza',
            'Artes e Habilidades Manuais',
            'Ciência e Saúde',
        ]);

        return [
            'nome' => $nome,
            'area' => $area,
            'nome_search' => EspecialidadesCatalog::normalizeForSearch($nome),
            'area_search' => EspecialidadesCatalog::normalizeForSearch($area),
            'is_oficial' => false,
            'is_avancada' => EspecialidadesCatalog::isAdvanced($nome),
            'cor_fundo' => EspecialidadesCatalog::colorByArea($area),
        ];
    }
}
