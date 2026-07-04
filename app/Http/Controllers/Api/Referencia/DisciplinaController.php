<?php

namespace App\Http\Controllers\Api\Referencia;

use App\Http\Controllers\Controller;
use App\Http\Resources\Disciplina\DisciplinaResource;
use App\Models\Disciplina;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DisciplinaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DisciplinaResource::collection(
            Disciplina::query()->orderBy('nome')->get(),
        );
    }

    public function show(Disciplina $disciplina): DisciplinaResource
    {
        return new DisciplinaResource($disciplina);
    }
}
