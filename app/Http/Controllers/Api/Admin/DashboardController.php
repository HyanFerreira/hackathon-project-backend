<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aluno;
use App\Models\Escola;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'escolas' => Escola::query()->count(),
            'gestores' => User::query()->role('gestor')->count(),
            'professores' => User::query()->role('professor')->count(),
            'alunos' => Aluno::query()->count(),
        ]);
    }
}
