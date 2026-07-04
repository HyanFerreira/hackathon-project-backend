<?php

namespace App\Http\Resources\Turma;

use App\Http\Resources\Aluno\AlunoResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TurmaResource extends JsonResource
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
            'ano' => $this->ano,
            'turno' => $this->turno,
            'status' => $this->status,
            'professores' => UserResource::collection($this->whenLoaded('professores')),
            'alunos' => AlunoResource::collection($this->whenLoaded('alunos')),
        ];
    }
}
