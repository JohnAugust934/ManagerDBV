<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AtoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero' => $this->faker->numerify('###/'.date('Y')), // Ex: 015/2026
            'data' => $this->faker->date(),
            'tipo' => $this->faker->randomElement(['Nomeação', 'Disciplina', 'Voto']),
            'descricao' => $this->faker->paragraph(), // Mudou de descricao_resumida para descricao
        ];
    }
}
