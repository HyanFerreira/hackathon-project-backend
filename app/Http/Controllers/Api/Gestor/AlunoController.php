<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Concerns\EscopoEscola;
use App\Http\Controllers\Controller;
use App\Http\Requests\Aluno\StoreAlunoRequest;
use App\Http\Requests\Aluno\UpdateAlunoRequest;
use App\Http\Resources\Aluno\AlunoResource;
use App\Models\Aluno;
use App\Services\Aluno\AlunoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AlunoController extends Controller
{
    use EscopoEscola;

    public function __construct(private readonly AlunoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AlunoResource::collection(
            $this->service->allForEscola($this->escolaDoGestor($request)),
        );
    }

    public function store(StoreAlunoRequest $request): JsonResponse
    {
        $aluno = $this->service->create($this->escolaDoGestor($request), $request->validated());

        return (new AlunoResource($aluno))->response()->setStatusCode(201);
    }

    public function show(Request $request, Aluno $aluno): AlunoResource
    {
        $this->garantirMesmaEscola($request, $aluno->escola_id);

        return new AlunoResource($aluno);
    }

    public function update(UpdateAlunoRequest $request, Aluno $aluno): AlunoResource
    {
        $this->garantirMesmaEscola($request, $aluno->escola_id);

        return new AlunoResource($this->service->update($aluno, $request->validated()));
    }

    public function destroy(Request $request, Aluno $aluno): Response
    {
        $this->garantirMesmaEscola($request, $aluno->escola_id);

        $this->service->delete($aluno);

        return response()->noContent();
    }
}
