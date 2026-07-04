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
            // No ranking, "pontos" é a pontuação de desempenho (não o saldo da loja).
            'pontos' => $this->perfil?->pontuacao_total ?? 0,
            'xp' => $this->perfil?->xp ?? 0,
            'nivel' => $this->perfil?->nivel ?? 1,
        ];
    }
}
