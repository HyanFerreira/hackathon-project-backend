<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Conquista\ConquistaProgressoResource;
use App\Services\Conquista\ConquistaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConquistaController extends Controller
{
    public function __construct(private readonly ConquistaService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ConquistaProgressoResource::collection(
            $this->service->progresso($request->user()),
        );
    }
}
