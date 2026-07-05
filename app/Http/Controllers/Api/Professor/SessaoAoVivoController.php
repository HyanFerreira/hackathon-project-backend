<?php

namespace App\Http\Controllers\Api\Professor;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessaoAoVivo\CriarSessaoAoVivoRequest;
use App\Http\Resources\SessaoAoVivo\SessaoAoVivoResource;
use App\Models\Questao;
use App\Models\SessaoAoVivo;
use App\Services\SessaoAoVivo\SessaoAoVivoPayload;
use App\Services\SessaoAoVivo\SessaoAoVivoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SessaoAoVivoController extends Controller
{
    public function __construct(private readonly SessaoAoVivoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return SessaoAoVivoResource::collection(
            $this->service->listarProfessor($request->user()),
        );
    }

    public function store(CriarSessaoAoVivoRequest $request): JsonResponse
    {
        $sessao = $this->service->criar($request->user(), $request->validated());

        return response()->json(SessaoAoVivoPayload::estadoProfessor($sessao), 201);
    }

    public function show(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json($this->service->estadoProfessor($sessao));
    }

    public function iniciar(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json(SessaoAoVivoPayload::estadoProfessor($this->service->iniciar($sessao)));
    }

    public function pausar(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json(SessaoAoVivoPayload::estadoProfessor($this->service->pausar($sessao)));
    }

    public function retomar(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json(SessaoAoVivoPayload::estadoProfessor($this->service->retomar($sessao)));
    }

    public function heartbeat(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json(SessaoAoVivoPayload::estadoProfessor($this->service->heartbeat($sessao)));
    }

    public function encerrar(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json(SessaoAoVivoPayload::estadoProfessor($this->service->encerrar($sessao)));
    }

    public function enviarQuestao(Request $request, SessaoAoVivo $sessao, Questao $questao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json(SessaoAoVivoPayload::estadoProfessor(
            $this->service->selecionarEEnviarQuestao($sessao, $questao, $request->user())
        ));
    }

    public function proxima(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);

        return response()->json(SessaoAoVivoPayload::estadoProfessor($this->service->enviarProxima($sessao)));
    }

    public function desempenho(Request $request, SessaoAoVivo $sessao): JsonResponse
    {
        $this->garantirProfessor($request, $sessao);
        $sessao = $this->service->heartbeat($sessao);

        return response()->json([
            'sessao' => SessaoAoVivoPayload::sessao($sessao),
            'desempenho' => SessaoAoVivoPayload::desempenho($sessao),
        ]);
    }

    private function garantirProfessor(Request $request, SessaoAoVivo $sessao): void
    {
        abort_unless(
            (int) $sessao->professor_id === (int) $request->user()->id,
            403,
            'Você não conduz esta sessão.',
        );
    }
}
