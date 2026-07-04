<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Aluno\PerfilAlunoResource;
use App\Http\Resources\Personagem\AlunoPersonagemResource;
use App\Http\Resources\Personagem\PersonagemLojaResource;
use App\Models\Personagem;
use App\Services\Aluno\PerfilAlunoService;
use App\Services\Personagem\PersonagemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonagemController extends Controller
{
    public function __construct(
        private readonly PersonagemService $service,
        private readonly PerfilAlunoService $perfis,
    ) {}

    public function loja(Request $request): AnonymousResourceCollection
    {
        return PersonagemLojaResource::collection($this->service->catalogo($request->user()));
    }

    public function inventario(Request $request): AnonymousResourceCollection
    {
        return AlunoPersonagemResource::collection($this->service->inventario($request->user()));
    }

    public function comprar(Request $request, Personagem $personagem): JsonResponse
    {
        $aluno = $request->user();
        $this->service->comprar($aluno, $personagem);

        return response()->json([
            'message' => "Você comprou o personagem {$personagem->nome}!",
            'perfil' => new PerfilAlunoResource($this->perfis->garantir($aluno)),
            'inventario' => AlunoPersonagemResource::collection($this->service->inventario($aluno)),
        ], 201);
    }

    public function equipar(Request $request, Personagem $personagem): JsonResponse
    {
        $aluno = $request->user();
        $this->service->equipar($aluno, $personagem);

        return response()->json([
            'message' => "Personagem {$personagem->nome} equipado!",
            'inventario' => AlunoPersonagemResource::collection($this->service->inventario($aluno)),
        ]);
    }
}
