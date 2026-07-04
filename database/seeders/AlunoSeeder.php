<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Turma;
use Illuminate\Database\Seeder;

class AlunoSeeder extends Seeder
{
    public function run(): void
    {
        $central = Escola::query()->where('nome', 'Escola Municipal Central')->first();
        $escolaId = $central?->id;

        $aluno = Aluno::query()->updateOrCreate(
            ['codigo' => 'ALU001'],
            ['nome' => 'Davi Rocha', 'escola_id' => $escolaId],
        );

        // Matricula o aluno na turma 6º Ano A como exemplo.
        $turma = $central
            ? Turma::query()->where('escola_id', $central->id)->where('nome', '6º Ano A')->first()
            : null;

        if ($turma) {
            $turma->alunos()->syncWithoutDetaching([$aluno->id]);
        }
    }
}
