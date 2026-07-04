<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Services\Aluno\PerfilAlunoService;
use App\Services\Ranking\RankingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PerfilAlunoService $perfis,
        private readonly RankingService $ranking,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $aluno = $request->user();
        $perfil = $this->perfis->garantir($aluno);
        $turma = $aluno->turmas()->first();

        $posicaoTurma = null;
        if ($turma) {
            $posicaoTurma = $this->ranking->turma($turma->id)
                ->firstWhere('id', $aluno->id)?->posicao;
        }

        return response()->json([
            'aluno' => [
                'id' => $aluno->id,
                'nome' => $aluno->nome,
                'codigo' => $aluno->codigo,
            ],
            'turma' => $turma?->only(['id', 'nome']),
            'perfil' => [
                'pontos' => $perfil->pontos,
                'xp' => $perfil->xp,
                'nivel' => $perfil->nivel,
                'energia' => $perfil->energia,
                'energia_maxima' => $perfil->energia_maxima,
            ],
            'posicao_turma' => $posicaoTurma,
        ]);
    }
}
