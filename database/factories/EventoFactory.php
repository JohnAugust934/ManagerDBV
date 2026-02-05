<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    public function definition(): array
    {
        $inicio = $this->faker->dateTimeBetween('now', '+1 month');

        return [
            'nome' => $this->faker->sentence(3),
            'local' => $this->faker->city,
            'data_inicio' => $inicio,
            'data_fim' => (clone $inicio)->modify('+2 days'),
            'valor' => $this->faker->randomFloat(2, 0, 200),
            'descricao' => $this->faker->paragraph,
        ];
    }
}
