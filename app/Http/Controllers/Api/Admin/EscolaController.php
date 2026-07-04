<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreEscolaRequest;
use App\Http\Requests\Escola\UpdateEscolaRequest;
use App\Http\Resources\Escola\EscolaResource;
use App\Models\Escola;
use App\Services\Escola\EscolaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EscolaController extends Controller
{
    public function __construct(private readonly EscolaService $service) {}

    public function index(): AnonymousResourceCollection
    {
        return EscolaResource::collection($this->service->all());
    }

    public function store(StoreEscolaRequest $request): JsonResponse
    {
        $escola = $this->service->create($request->validated());

        return (new EscolaResource($escola))->response()->setStatusCode(201);
    }

    public function show(Escola $escola): EscolaResource
    {
        return new EscolaResource($escola);
    }

    public function update(UpdateEscolaRequest $request, Escola $escola): EscolaResource
    {
        return new EscolaResource($this->service->update($escola, $request->validated()));
    }

    public function destroy(Escola $escola): Response
    {
        $this->service->delete($escola);

        return response()->noContent();
    }
}
