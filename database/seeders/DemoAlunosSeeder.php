<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Turma;
use Illuminate\Database\Seeder;

class DemoAlunosSeeder extends Seeder
{
    /** Turma usada na apresentação (ranking ao vivo da plateia). */
    public const TURMA = 'Turma Demonstração';

    /** Alfabeto sem caracteres ambíguos (0/O, 1/I/L) para facilitar a digitação. */
    private const ALFABETO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const QUANTIDADE = 100;

    public function run(): void
    {
        $escola = Escola::query()->where('nome', EscolaSeeder::PRINCIPAL)->first();

        if (! $escola) {
            return;
        }

        $turma = Turma::query()->updateOrCreate(
            ['escola_id' => $escola->id, 'nome' => self::TURMA],
            ['ano' => '2026', 'turno' => 'manha', 'status' => 'ativa'],
        );

        $existentes = $turma->alunos()->count();

        for ($i = $existentes; $i < self::QUANTIDADE; $i++) {
            $aluno = Aluno::query()->create([
                'escola_id' => $escola->id,
                'nome' => fake('pt_BR')->firstName().' '.fake('pt_BR')->lastName(),
                'codigo' => $this->codigoUnico(),
            ]);

            $turma->alunos()->attach($aluno->id);
        }
    }

    private function codigoUnico(): string
    {
        do {
            $codigo = '';
            for ($k = 0; $k < 5; $k++) {
                $codigo .= self::ALFABETO[random_int(0, strlen(self::ALFABETO) - 1)];
            }
        } while (Aluno::query()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
