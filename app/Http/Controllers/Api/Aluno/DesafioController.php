<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Requests\Desafio\CriarDesafioRequest;
use App\Http\Requests\Desafio\ResponderDesafioRequest;
use App\Http\Resources\Desafio\DesafioResource;
use App\Models\Aluno;
use App\Models\Desafio;
use App\Services\Desafio\DesafioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DesafioController extends Controller
{
    public function __construct(private readonly DesafioService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $aluno = $request->user();

        $desafios = Desafio::query()
            ->where(fn ($q) => $q
                ->where('desafiante_id', $aluno->id)
                ->orWhere('desafiado_id', $aluno->id))
            ->with(['desafiante', 'desafiado'])
            ->latest('id')
            ->get();

        return DesafioResource::collection($desafios);
    }

    public function store(CriarDesafioRequest $request): JsonResponse
    {
        $desafio = $this->service->criar($request->user(), $request->validated());

        return (new DesafioResource($desafio))->response()->setStatusCode(201);
    }

    public function aceitar(Request $request, Desafio $desafio): DesafioResource
    {
        $this->garantirDesafiado($request, $desafio);

        return new DesafioResource($this->service->aceitar($desafio));
    }

    public function recusar(Request $request, Desafio $desafio): DesafioResource
    {
        $this->garantirDesafiado($request, $desafio);

        return new DesafioResource($this->service->recusar($desafio));
    }

    /**
     * Estado atual da partida (questão atual + cronômetro, ou resultado final).
     * Também resolve o tempo esgotado quando chamado após o prazo.
     */
    public function atual(Request $request, Desafio $desafio): JsonResponse
    {
        $aluno = $this->garantirParticipante($request, $desafio);

        return response()->json($this->service->estado($desafio, $aluno));
    }

    public function responder(ResponderDesafioRequest $request, Desafio $desafio): JsonResponse
    {
        $aluno = $this->garantirParticipante($request, $desafio);

        return response()->json(
            $this->service->responder($desafio, $aluno, (int) $request->integer('alternativa_id')),
        );
    }

    /**
     * Apenas o aluno desafiado pode aceitar/recusar o convite.
     */
    private function garantirDesafiado(Request $request, Desafio $desafio): void
    {
        abort_unless(
            (int) $desafio->desafiado_id === (int) $request->user()->id,
            403,
            'Apenas o aluno desafiado pode responder a este convite.',
        );
    }

    /**
     * Garante que o aluno autenticado participa do desafio e o devolve.
     */
    private function garantirParticipante(Request $request, Desafio $desafio): Aluno
    {
        $aluno = $request->user();

        abort_unless($desafio->envolve($aluno), 403, 'Você não participa deste desafio.');

        return $aluno;
    }
}
