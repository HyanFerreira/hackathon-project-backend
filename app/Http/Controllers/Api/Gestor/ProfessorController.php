<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Concerns\EscopoEscola;
use App\Http\Controllers\Controller;
use App\Http\Requests\Professor\StoreProfessorRequest;
use App\Http\Requests\Professor\UpdateProfessorRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\Professor\ProfessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProfessorController extends Controller
{
    use EscopoEscola;

    public function __construct(private readonly ProfessorService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return UserResource::collection(
            $this->service->allForEscola($this->escolaDoGestor($request)),
        );
    }

    public function store(StoreProfessorRequest $request): JsonResponse
    {
        $professor = $this->service->create($this->escolaDoGestor($request), $request->validated());

        return (new UserResource($professor))->response()->setStatusCode(201);
    }

    public function show(Request $request, User $professor): UserResource
    {
        $this->garantirMesmaEscola($request, $professor->escola_id);

        return new UserResource($professor->load('roles'));
    }

    public function update(UpdateProfessorRequest $request, User $professor): UserResource
    {
        $this->garantirMesmaEscola($request, $professor->escola_id);

        return new UserResource($this->service->update($professor, $request->validated()));
    }

    public function destroy(Request $request, User $professor): Response
    {
        $this->garantirMesmaEscola($request, $professor->escola_id);

        $this->service->delete($professor);

        return response()->noContent();
    }
}
