<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

class AtoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero' => $this->faker->numerify('###/'.date('Y')), // Ex: 015/2026
            'data' => $this->faker->date(),
            'tipo' => $this->faker->randomElement(['Nomeação', 'Disciplina', 'Voto']),
            'descricao' => $this->faker->paragraph(),
            'club_id' => Club::factory(),
        ];
    }

    public function forClube(int $clubId): static
    {
        return $this->state(fn () => ['club_id' => $clubId]);
    }
}
