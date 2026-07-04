<?php

namespace Database\Factories;

use App\Models\Escola;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Escola>
 */
class EscolaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => 'Escola '.fake()->unique()->lastName(),
            'cnpj' => fake()->numerify('##############'),
            'cidade' => fake('pt_BR')->city(),
            'estado' => fake()->randomElement(['SP', 'RJ', 'MG', 'BA', 'RS']),
            'status' => 'ativa',
        ];
    }
}
