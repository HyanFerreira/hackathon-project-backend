<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Requests\Aluno\ResponderQuestaoRequest;
use App\Http\Resources\Aluno\PerfilAlunoResource;
use App\Http\Resources\Aluno\QuestaoAlunoResource;
use App\Models\Questao;
use App\Services\Aluno\RespostaAlunoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuestaoController extends Controller
{
    public function __construct(private readonly RespostaAlunoService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $aluno = $request->user();
        $disciplinaId = $request->integer('disciplina_id') ?: null;

        return QuestaoAlunoResource::collection($this->service->disponiveis($aluno, $disciplinaId));
    }

    public function responder(ResponderQuestaoRequest $request, Questao $questao): JsonResponse
    {
        $aluno = $request->user();

        abort_unless(
            (int) $questao->escola_id === (int) $aluno->escola_id && $questao->status === 'ativa',
            403,
            'Esta questão não está disponível para você.',
        );

        $resultado = $this->service->responder($aluno, $questao, (int) $request->integer('alternativa_id'));

        return response()->json([
            'correta' => $resultado['correta'],
            'mensagem' => $resultado['mensagem'],
            'gabarito' => $resultado['gabarito'],
            'pontos_ganhos' => $resultado['pontos_ganhos'],
            'xp_ganho' => $resultado['xp_ganho'],
            'energia_gasta' => $resultado['energia_gasta'],
            'perfil' => new PerfilAlunoResource($resultado['perfil']),
        ]);
    }
}
