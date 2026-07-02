<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use App\Services\Role\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $service)
    {
    }

    public function index(): AnonymousResourceCollection
    {
        return RoleResource::collection($this->service->all());
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $role = $this->service->create($request->validated());

        return (new RoleResource($role))->response()->setStatusCode(201);
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource($role);
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        return new RoleResource($this->service->update($role, $request->validated()));
    }

    public function destroy(Role $role): Response
    {
        $this->service->delete($role);

        return response()->noContent();
    }
}
