<?php

namespace App\Services\Vinculo;

use App\Models\Aluno;
use App\Models\Turma;
use App\Models\User;

class VinculoService
{
    public function vincularProfessor(Turma $turma, User $professor): void
    {
        $turma->professores()->syncWithoutDetaching([$professor->id]);
    }

    public function desvincularProfessor(Turma $turma, User $professor): void
    {
        $turma->professores()->detach($professor->id);
    }

    public function vincularAluno(Turma $turma, Aluno $aluno): void
    {
        $turma->alunos()->syncWithoutDetaching([$aluno->id]);
    }

    public function desvincularAluno(Turma $turma, Aluno $aluno): void
    {
        $turma->alunos()->detach($aluno->id);
    }
}
