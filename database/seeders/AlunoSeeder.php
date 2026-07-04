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
        $principal = Escola::query()->where('nome', EscolaSeeder::PRINCIPAL)->first();

        $aluno = Aluno::query()->updateOrCreate(
            ['codigo' => 'ALU001'],
            ['nome' => 'Davi Rocha', 'escola_id' => $principal?->id],
        );

        // Matricula o aluno no 6º Ano A da escola principal, como exemplo.
        $turma = $principal
            ? Turma::query()->where('escola_id', $principal->id)->where('nome', '6º Ano A')->first()
            : null;

        $turma?->alunos()->syncWithoutDetaching([$aluno->id]);
    }
}
