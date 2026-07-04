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

        $alunos = [
            ['nome' => 'Davi Rocha', 'codigo' => 'ALU001'],
            ['nome' => 'Enzo Martins', 'codigo' => 'ALU002'],
            ['nome' => 'Sofia Almeida', 'codigo' => 'ALU003'],
        ];

        foreach ($alunos as $aluno) {
            Aluno::query()->updateOrCreate(
                ['codigo' => $aluno['codigo']],
                ['nome' => $aluno['nome'], 'escola_id' => $escolaId],
            );
        }

        Aluno::factory()->count(10)->create(['escola_id' => $escolaId]);

        // Matricula os três alunos fixos na turma 6º Ano A como exemplo.
        $turma = $central
            ? Turma::query()->where('escola_id', $central->id)->where('nome', '6º Ano A')->first()
            : null;

        if ($turma) {
            $ids = Aluno::query()->whereIn('codigo', ['ALU001', 'ALU002', 'ALU003'])->pluck('id');
            $turma->alunos()->syncWithoutDetaching($ids->all());
        }
    }
}
