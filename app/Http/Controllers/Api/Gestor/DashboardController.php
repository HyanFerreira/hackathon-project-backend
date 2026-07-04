<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Concerns\EscopoEscola;
use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use EscopoEscola;

    public function index(Request $request): JsonResponse
    {
        $escolaId = $this->escolaDoUsuario($request);

        return response()->json([
            'turmas' => Turma::query()->where('escola_id', $escolaId)->count(),
            'professores' => User::query()->role('professor')->where('escola_id', $escolaId)->count(),
            'alunos' => Aluno::query()->where('escola_id', $escolaId)->count(),
        ]);
    }
}
