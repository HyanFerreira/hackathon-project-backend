<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Concerns\EscopoEscola;
use App\Http\Controllers\Controller;
use App\Http\Requests\Turma\StoreTurmaRequest;
use App\Http\Requests\Turma\UpdateTurmaRequest;
use App\Http\Resources\Turma\TurmaResource;
use App\Models\Turma;
use App\Services\Turma\TurmaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TurmaController extends Controller
{
    use EscopoEscola;

    public function __construct(private readonly TurmaService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return TurmaResource::collection(
            $this->service->allForEscola($this->escolaDoGestor($request)),
        );
    }

    public function store(StoreTurmaRequest $request): JsonResponse
    {
        $turma = $this->service->create($this->escolaDoGestor($request), $request->validated());

        return (new TurmaResource($turma))->response()->setStatusCode(201);
    }

    public function show(Request $request, Turma $turma): TurmaResource
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);

        return new TurmaResource($turma->load(['professores', 'alunos']));
    }

    public function update(UpdateTurmaRequest $request, Turma $turma): TurmaResource
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);

        return new TurmaResource($this->service->update($turma, $request->validated()));
    }

    public function destroy(Request $request, Turma $turma): Response
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);

        $this->service->delete($turma);

        return response()->noContent();
    }
}
