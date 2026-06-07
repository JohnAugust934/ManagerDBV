<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CaixaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'descricao' => $this->faker->sentence(3),
            'valor' => $this->faker->randomFloat(2, 10, 500),
            'tipo' => $this->faker->randomElement(['entrada', 'saida']),
            'data_movimentacao' => $this->faker->date(),
            'categoria' => $this->faker->word(),
            'club_id' => null,
        ];
    }
}
