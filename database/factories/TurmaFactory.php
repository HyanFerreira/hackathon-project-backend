<?php

namespace Database\Factories;

use App\Models\Escola;
use App\Models\Turma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Turma>
 */
class TurmaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'escola_id' => Escola::factory(),
            'nome' => fake()->numberBetween(1, 9).'º Ano '.fake()->randomElement(['A', 'B', 'C']),
            'ano' => (string) fake()->numberBetween(2025, 2026),
            'turno' => fake()->randomElement(['manha', 'tarde', 'noite', 'integral']),
            'status' => 'ativa',
        ];
    }
}
