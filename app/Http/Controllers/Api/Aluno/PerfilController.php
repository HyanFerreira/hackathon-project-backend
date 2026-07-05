<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Aluno\PerfilAlunoResource;
use App\Services\Aluno\PerfilAlunoService;
use App\Services\Conquista\ConquistaService;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function __construct(
        private readonly PerfilAlunoService $perfis,
        private readonly ConquistaService $conquistas,
    ) {}

    public function show(Request $request): PerfilAlunoResource
    {
        $aluno = $request->user();
        $perfil = $this->perfis->garantir($aluno);
        $this->conquistas->sincronizar($aluno);
        $perfil = $perfil->fresh() ?? $perfil;
        $perfil->setRelation('aluno', $aluno);

        return new PerfilAlunoResource($perfil);
    }
}
