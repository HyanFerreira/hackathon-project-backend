<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Concerns\EscopoEscola;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vinculo\VincularAlunoRequest;
use App\Http\Requests\Vinculo\VincularProfessorRequest;
use App\Http\Resources\Turma\TurmaResource;
use App\Models\Aluno;
use App\Models\Turma;
use App\Models\User;
use App\Services\Vinculo\VinculoService;
use Illuminate\Http\Request;

class VinculoController extends Controller
{
    use EscopoEscola;

    public function __construct(private readonly VinculoService $service) {}

    public function vincularProfessor(VincularProfessorRequest $request, Turma $turma): TurmaResource
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);

        $professor = User::query()->findOrFail($request->integer('professor_id'));

        $this->garantirMesmaEscola($request, $professor->escola_id);
        abort_unless($professor->hasRole('professor'), 422, 'O usuário informado não é um professor.');

        $this->service->vincularProfessor($turma, $professor);

        return new TurmaResource($turma->load(['professores', 'alunos']));
    }

    public function desvincularProfessor(Request $request, Turma $turma, User $professor): TurmaResource
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);
        $this->garantirMesmaEscola($request, $professor->escola_id);

        $this->service->desvincularProfessor($turma, $professor);

        return new TurmaResource($turma->load(['professores', 'alunos']));
    }

    public function vincularAluno(VincularAlunoRequest $request, Turma $turma): TurmaResource
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);

        $aluno = Aluno::query()->findOrFail($request->integer('aluno_id'));

        $this->garantirMesmaEscola($request, $aluno->escola_id);

        $this->service->vincularAluno($turma, $aluno);

        return new TurmaResource($turma->load(['professores', 'alunos']));
    }

    public function desvincularAluno(Request $request, Turma $turma, Aluno $aluno): TurmaResource
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);
        $this->garantirMesmaEscola($request, $aluno->escola_id);

        $this->service->desvincularAluno($turma, $aluno);

        return new TurmaResource($turma->load(['professores', 'alunos']));
    }
}
