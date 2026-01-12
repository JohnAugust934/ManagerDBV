<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AtaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'data_reuniao' => $this->faker->date(),
            'tipo' => $this->faker->randomElement(['Regular', 'Diretoria']),
            'secretario_responsavel' => $this->faker->name(),
            'conteudo' => $this->faker->paragraphs(3, true),
        ];
    }
}
