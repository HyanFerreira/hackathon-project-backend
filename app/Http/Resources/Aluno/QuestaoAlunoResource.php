<?php

namespace App\Http\Resources\Aluno;

use App\Http\Resources\Habilidade\HabilidadeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Questão na visão do aluno: NÃO expõe qual alternativa é a correta.
 */
class QuestaoAlunoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enunciado' => $this->enunciado,
            'dificuldade' => $this->dificuldade,
            'pontos' => $this->pontos,
            'habilidades' => HabilidadeResource::collection($this->whenLoaded('habilidades')),
            'alternativas' => $this->whenLoaded('alternativas', fn () => $this->alternativas->map(fn ($a) => [
                'id' => $a->id,
                'texto' => $a->texto,
            ])),
        ];
    }
}
