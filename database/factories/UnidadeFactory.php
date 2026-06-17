<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => $this->faker->word().' '.$this->faker->city(),
            'conselheiro' => $this->faker->name(),
            'grito_guerra' => $this->faker->sentence(),
            'club_id' => Club::factory(),
        ];
    }

    public function forClube(int $clubId): static
    {
        return $this->state(fn () => ['club_id' => $clubId]);
    }
}
