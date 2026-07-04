<?php

namespace App\Http\Resources\Questao;

use App\Http\Resources\Habilidade\HabilidadeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'escola_id' => $this->escola_id,
            'professor_id' => $this->professor_id,
            'enunciado' => $this->enunciado,
            'dificuldade' => $this->dificuldade,
            'pontos' => $this->pontos,
            'status' => $this->status,
            'habilidades' => HabilidadeResource::collection($this->whenLoaded('habilidades')),
            'alternativas' => $this->whenLoaded('alternativas', fn () => $this->alternativas->map(fn ($a) => [
                'id' => $a->id,
                'texto' => $a->texto,
                'correta' => $a->correta,
            ])),
        ];
    }
}
