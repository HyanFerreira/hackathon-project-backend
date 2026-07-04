<?php

namespace App\Http\Controllers\Api\Gestor;

use App\Http\Controllers\Concerns\EscopoEscola;
use App\Http\Controllers\Controller;
use App\Http\Resources\Ranking\RankingItemResource;
use App\Models\Turma;
use App\Services\Ranking\RankingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RankingController extends Controller
{
    use EscopoEscola;

    public function __construct(private readonly RankingService $service) {}

    public function escola(Request $request): AnonymousResourceCollection
    {
        return RankingItemResource::collection(
            $this->service->escola($this->escolaDoUsuario($request)),
        );
    }

    public function turma(Request $request, Turma $turma): AnonymousResourceCollection
    {
        $this->garantirMesmaEscola($request, $turma->escola_id);

        return RankingItemResource::collection($this->service->turma($turma->id));
    }
}
