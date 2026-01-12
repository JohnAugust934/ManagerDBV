<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AtoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'data' => $this->faker->date(),
            'tipo' => $this->faker->randomElement(['Nomeação', 'Disciplina']),
            'descricao_resumida' => $this->faker->sentence(),
        ];
    }
}
