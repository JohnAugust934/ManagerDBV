<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatrimonioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'item' => $this->faker->words(3, true),
            'quantidade' => $this->faker->numberBetween(1, 10),
            'valor_estimado' => $this->faker->randomFloat(2, 50, 2000),
            'estado_conservacao' => $this->faker->randomElement(['Novo', 'Bom', 'Ruim']),
            'local_armazenamento' => 'Sede',
        ];
    }
}
