<?php

namespace App\Http\Resources\Habilidade;

use App\Http\Resources\Disciplina\DisciplinaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HabilidadeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'disciplina_id' => $this->disciplina_id,
            'codigo' => $this->codigo,
            'descricao' => $this->descricao,
            'etapa' => $this->etapa,
            'ano' => $this->ano,
            'disciplina' => new DisciplinaResource($this->whenLoaded('disciplina')),
        ];
    }
}
