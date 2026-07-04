<?php

namespace App\Http\Resources\Aluno;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RespostaAlunoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'questao_id' => $this->questao_id,
            'alternativa_id' => $this->alternativa_id,
            'correta' => $this->correta,
            'pontos_ganhos' => $this->pontos_ganhos,
            'xp_ganho' => $this->xp_ganho,
            'energia_gasta' => $this->energia_gasta,
            'respondido_em' => $this->respondido_em,
            'enunciado' => $this->whenLoaded('questao', fn () => $this->questao->enunciado),
        ];
    }
}
