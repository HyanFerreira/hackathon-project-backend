<?php

namespace App\Http\Controllers\Api\Referencia;

use App\Http\Controllers\Controller;
use App\Http\Resources\Habilidade\HabilidadeResource;
use App\Models\Habilidade;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HabilidadeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $habilidades = Habilidade::query()
            ->with('disciplina')
            ->when($request->filled('disciplina_id'), fn ($q) => $q->where('disciplina_id', $request->integer('disciplina_id')))
            ->when($request->filled('ano'), fn ($q) => $q->where('ano', $request->string('ano')))
            ->when($request->filled('busca'), fn ($q) => $q->where(fn ($sub) => $sub
                ->where('codigo', 'like', '%'.$request->string('busca').'%')
                ->orWhere('descricao', 'like', '%'.$request->string('busca').'%')))
            ->orderBy('codigo')
            ->get();

        return HabilidadeResource::collection($habilidades);
    }

    public function show(Habilidade $habilidade): HabilidadeResource
    {
        return new HabilidadeResource($habilidade->load('disciplina'));
    }
}
