<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Missao\MissaoProgressoResource;
use App\Services\Missao\MissaoService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MissaoController extends Controller
{
    public function __construct(private readonly MissaoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return MissaoProgressoResource::collection(
            $this->service->listar($request->user()),
        );
    }
}
