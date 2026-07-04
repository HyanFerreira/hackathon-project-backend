<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ranking\RankingItemResource;
use App\Services\Ranking\RankingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RankingController extends Controller
{
    public function __construct(private readonly RankingService $service) {}

    public function turma(Request $request): AnonymousResourceCollection
    {
        $turma = $request->user()->turmas()->first();

        $ranking = $turma ? $this->service->turma($turma->id) : collect();

        return RankingItemResource::collection($ranking);
    }

    public function escola(Request $request): AnonymousResourceCollection
    {
        return RankingItemResource::collection(
            $this->service->escola((int) $request->user()->escola_id),
        );
    }
}
