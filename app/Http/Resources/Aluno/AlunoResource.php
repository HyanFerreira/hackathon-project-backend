<?php

namespace App\Http\Resources\Aluno;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlunoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'escola_id' => $this->escola_id,
            'nome' => $this->nome,
            'codigo' => $this->codigo,
        ];
    }
}
