<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Aluno\PerfilAlunoResource;
use App\Services\Aluno\PerfilAlunoService;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function __construct(private readonly PerfilAlunoService $perfis) {}

    public function show(Request $request): PerfilAlunoResource
    {
        $aluno = $request->user();
        $perfil = $this->perfis->garantir($aluno);
        $perfil->setRelation('aluno', $aluno);

        return new PerfilAlunoResource($perfil);
    }
}
