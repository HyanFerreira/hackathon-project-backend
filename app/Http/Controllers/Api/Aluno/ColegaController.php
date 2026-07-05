<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Aluno\AlunoResource;
use App\Models\Aluno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ColegaController extends Controller
{
    /**
     * Colegas de turma do aluno (para escolher quem desafiar).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $aluno = $request->user();
        $turmaIds = $aluno->turmas()->pluck('turmas.id');

        $colegas = Aluno::query()
            ->whereKeyNot($aluno->id)
            ->whereHas('turmas', fn (Builder $q) => $q->whereIn('turmas.id', $turmaIds))
            ->orderBy('nome')
            ->get();

        return AlunoResource::collection($colegas);
    }
}
