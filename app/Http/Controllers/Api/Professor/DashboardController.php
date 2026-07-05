<?php

namespace App\Http\Controllers\Api\Professor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Turma\TurmaResource;
use App\Models\Aluno;
use App\Models\Questao;
use App\Services\Professor\DesempenhoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DashboardController extends Controller
{
    public function turmas(Request $request): AnonymousResourceCollection
    {
        return TurmaResource::collection(
            $request->user()
                ->turmas()
                ->orderBy('nome')
                ->get()
        );
    }

    public function index(Request $request): JsonResponse
    {
        $professor = $request->user();
        $turmaIds = $professor->turmas()->pluck('turmas.id');

        return response()->json([
            'minhas_turmas' => $turmaIds->count(),
            'alunos' => Aluno::query()
                ->whereHas('turmas', fn (Builder $q) => $q->whereIn('turmas.id', $turmaIds))
                ->count(),
            'questoes' => Questao::query()->where('professor_id', $professor->id)->count(),
            'ultimas_questoes' => Questao::query()
                ->where('professor_id', $professor->id)
                ->latest('id')
                ->limit(5)
                ->get(['id', 'enunciado', 'dificuldade', 'created_at']),
        ]);
    }

    /**
     * Dashboard avançado: desempenho dos alunos do professor,
     * com destaque para as habilidades em que vão pior.
     */
    public function desempenho(Request $request, DesempenhoService $service): JsonResponse
    {
        return response()->json($service->paraProfessor($request->user()));
    }
}
