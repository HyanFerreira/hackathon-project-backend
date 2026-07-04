<?php

namespace App\Http\Controllers\Api\Professor;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ranking\RankingItemResource;
use App\Models\Turma;
use App\Services\Ranking\RankingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RankingController extends Controller
{
    public function __construct(private readonly RankingService $service) {}

    public function turma(Request $request, Turma $turma): AnonymousResourceCollection
    {
        abort_unless(
            $request->user()->turmas()->whereKey($turma->id)->exists(),
            403,
            'Você não leciona nesta turma.',
        );

        return RankingItemResource::collection($this->service->turma($turma->id));
    }
}
