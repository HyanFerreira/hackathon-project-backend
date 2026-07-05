<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessaoAoVivo\ResponderSessaoAoVivoRequest;
use App\Models\SessaoAoVivo;
use App\Services\SessaoAoVivo\SessaoAoVivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessaoAoVivoController extends Controller
{
    public function __construct(private readonly SessaoAoVivoService $service) {}

    public function resumo(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'periodo' => ['sometimes', 'string', 'in:all,30-days,year'],
        ]);

        return response()->json(
            $this->service->resumoAluno($request->user(), $dados['periodo'] ?? 'all'),
        );
    }

    public function ativa(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->ativaParaAluno($request->user()),
        ]);
    }

    public function entrar(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        return response()->json($this->service->entrar($sessao, $request->user()));
    }

    public function atual(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        return response()->json($this->service->estadoAluno($sessao, $request->user()));
    }

    public function responder(ResponderSessaoAoVivoRequest $request, SessaoAoVivo $sessao): JsonResponse
    {
        return response()->json(
            $this->service->responder($sessao, $request->user(), (int) $request->integer('alternativa_id')),
        );
    }
}
