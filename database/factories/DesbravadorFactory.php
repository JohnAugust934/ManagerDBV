<?php

namespace Database\Factories;

use App\Models\Classe;
use App\Models\Unidade;
use Illuminate\Database\Eloquent\Factories\Factory;

class DesbravadorFactory extends Factory
{
    public function definition(): array
    {
        $classe = Classe::inRandomOrder()->first() ?? Classe::factory()->create();
        $unidade = Unidade::inRandomOrder()->first() ?? Unidade::factory()->create();

        return [
            'nome' => $this->faker->name(),
            'data_nascimento' => $this->faker->dateTimeBetween('-15 years', '-10 years')->format('Y-m-d'),
            'sexo' => $this->faker->randomElement(['M', 'F']),
            'ativo' => true,
            'unidade_id' => $unidade->id,
            'classe_atual' => $classe->id,
            'email' => $this->faker->unique()->safeEmail(),
            'telefone' => $this->faker->phoneNumber(),
            'endereco' => $this->faker->address(),
            'nome_responsavel' => $this->faker->name(),
            'telefone_responsavel' => $this->faker->phoneNumber(),
            'numero_sus' => $this->faker->numerify('###########'),
            'tipo_sanguineo' => $this->faker->randomElement(['A+', 'O+']),
            'alergias' => null,
            'medicamentos_continuos' => null,
            'plano_saude' => null,
            'cpf' => $this->faker->unique()->numerify('###.###.###-##'),
            'rg' => $this->faker->numerify('##.###.###-X'),
        ];
    }
}
