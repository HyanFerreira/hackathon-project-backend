<?php

namespace App\Http\Resources\Aluno;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DisciplinaProgressoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $disciplina = $this->resource['disciplina'];

        return [
            'id' => $disciplina->id,
            'nome' => $disciplina->nome,
            'sigla' => $disciplina->sigla,
            'area' => $disciplina->area,
            'total' => $this->resource['total'],
            'respondidas' => $this->resource['respondidas'],
            'disponiveis' => $this->resource['disponiveis'],
        ];
    }
}
