<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gestor\StoreGestorRequest;
use App\Http\Requests\Gestor\UpdateGestorRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\Gestor\GestorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class GestorController extends Controller
{
    public function __construct(private readonly GestorService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection($this->service->all());
    }

    public function store(StoreGestorRequest $request): JsonResponse
    {
        $gestor = $this->service->create($request->validated());

        return (new UserResource($gestor))->response()->setStatusCode(201);
    }

    public function show(User $gestor): UserResource
    {
        return new UserResource($gestor->load(['roles', 'escola']));
    }

    public function update(UpdateGestorRequest $request, User $gestor): UserResource
    {
        return new UserResource($this->service->update($gestor, $request->validated()));
    }

    public function destroy(User $gestor): Response
    {
        $this->service->delete($gestor);

        return response()->noContent();
    }
}
