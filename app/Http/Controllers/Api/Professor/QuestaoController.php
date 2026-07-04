<?php

namespace App\Http\Controllers\Api\Professor;

use App\Http\Controllers\Concerns\EscopoEscola;
use App\Http\Controllers\Controller;
use App\Http\Requests\Questao\StoreQuestaoRequest;
use App\Http\Requests\Questao\UpdateQuestaoRequest;
use App\Http\Resources\Questao\QuestaoResource;
use App\Models\Questao;
use App\Services\Questao\QuestaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class QuestaoController extends Controller
{
    use EscopoEscola;

    public function __construct(private readonly QuestaoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return QuestaoResource::collection(
            $this->service->allForProfessor($this->escolaDoUsuario($request), (int) $request->user()->id),
        );
    }

    public function store(StoreQuestaoRequest $request): JsonResponse
    {
        $questao = $this->service->create(
            $this->escolaDoUsuario($request),
            (int) $request->user()->id,
            $request->validated(),
        );

        return (new QuestaoResource($questao))->response()->setStatusCode(201);
    }

    public function show(Request $request, Questao $questao): QuestaoResource
    {
        $this->garantirDono($request, $questao);

        return new QuestaoResource($questao->load(['alternativas', 'habilidades.disciplina']));
    }

    public function update(UpdateQuestaoRequest $request, Questao $questao): QuestaoResource
    {
        $this->garantirDono($request, $questao);

        return new QuestaoResource($this->service->update($questao, $request->validated()));
    }

    public function destroy(Request $request, Questao $questao): Response
    {
        $this->garantirDono($request, $questao);

        $this->service->delete($questao);

        return response()->noContent();
    }

    /**
     * O professor só acessa questões da própria escola e de sua autoria.
     */
    private function garantirDono(Request $request, Questao $questao): void
    {
        $this->garantirMesmaEscola($request, $questao->escola_id);

        abort_unless(
            (int) $questao->professor_id === (int) $request->user()->id,
            403,
            'Você só pode gerenciar as suas próprias questões.',
        );
    }
}
