<?php

namespace App\Http\Resources\Ranking;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RankingItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'posicao' => $this->posicao,
            'aluno' => [
                'id' => $this->id,
                'nome' => $this->nome,
                'codigo' => $this->codigo,
            ],
            'pontos' => $this->perfil?->pontos ?? 0,
            'xp' => $this->perfil?->xp ?? 0,
            'nivel' => $this->perfil?->nivel ?? 1,
        ];
    }
}
