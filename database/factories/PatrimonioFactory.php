<?php

namespace Database\Factories;

use App\Models\Club;
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
            'club_id' => Club::factory(),
        ];
    }

    public function forClube(int $clubId): static
    {
        return $this->state(fn () => ['club_id' => $clubId]);
    }
}
