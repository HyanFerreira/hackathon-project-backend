<?php

namespace App\Http\Resources\Aluno;

use App\Services\Aluno\LoginStreakService;
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
            'streak' => [
                'dias_seguidos' => $this->dias_seguidos_login,
                'maior_dias_seguidos' => $this->maior_dias_seguidos_login,
                'ultimo_login_em' => $this->ultimo_login_em?->toIso8601String(),
                'proximo_bonus_em_dias' => LoginStreakService::proximoBonusEm((int) $this->dias_seguidos_login),
            ],
            'aluno' => new AlunoResource($this->whenLoaded('aluno')),
        ];
    }
}
