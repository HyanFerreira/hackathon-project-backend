<?php

namespace App\Http\Controllers\Api\Aluno;

use App\Http\Controllers\Controller;
use App\Http\Resources\Aluno\RespostaAlunoResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RespostaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $respostas = $request->user()
            ->respostas()
            ->with('questao')
            ->latest('respondido_em')
            ->get();

        return RespostaAlunoResource::collection($respostas);
    }
}
