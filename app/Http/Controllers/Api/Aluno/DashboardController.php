<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Services\Aluno\LoginStreakService;
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
                'pontuacao_total' => $perfil->pontuacao_total,
                'xp' => $perfil->xp,
                'nivel' => $perfil->nivel,
                'energia' => $perfil->energia,
                'energia_maxima' => $perfil->energia_maxima,
                'streak' => [
                    'dias_seguidos' => $perfil->dias_seguidos_login,
                    'maior_dias_seguidos' => $perfil->maior_dias_seguidos_login,
                    'ultimo_login_em' => $perfil->ultimo_login_em?->toIso8601String(),
                    'proximo_bonus_em_dias' => LoginStreakService::proximoBonusEm((int) $perfil->dias_seguidos_login),
                ],
            ],
            'posicao_turma' => $posicaoTurma,
        ]);
    }
}
