<?php

namespace App\Http\Controllers\Api\Professor;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Questao;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
}
