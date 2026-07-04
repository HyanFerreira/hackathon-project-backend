<?php

namespace App\Http\Resources\Aluno;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerfilAlunoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pontos' => $this->pontos,                     // saldo gastável (loja)
            'pontuacao_total' => $this->pontuacao_total,   // pontuação de desempenho (ranking)
            'xp' => $this->xp,
            'nivel' => $this->nivel,
            'xp_para_proximo_nivel' => 100 - ($this->xp % 100),
            'energia' => $this->energia,
            'energia_maxima' => $this->energia_maxima,
            'aluno' => new AlunoResource($this->whenLoaded('aluno')),
        ];
    }
}
