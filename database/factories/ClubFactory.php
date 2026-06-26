<?php

namespace Database\Factories;

use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Club>
 */
class ClubFactory extends Factory
{
    protected $model = Club::class;

    public function definition(): array
    {
        return [
            'nome' => 'Clube '.$this->faker->unique()->company(),
            'cidade' => $this->faker->city(),
            'associacao' => $this->faker->randomElement(['APaC', 'APL', 'AMC', 'APR']),
            'logo' => null,
        ];
    }
}
