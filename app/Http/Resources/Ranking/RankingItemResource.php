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
        $alunoPersonagem = $this->personagens->first();
        $personagem = $alunoPersonagem?->personagem;

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
            'personagem' => $personagem ? [
                'chave' => $personagem->chave,
                'nome' => $personagem->nome,
                'nivel' => $alunoPersonagem->nivel,
                'imagem' => $personagem->imagem($alunoPersonagem->nivel),
                'avatar' => $personagem->avatar,
            ] : null,
        ];
    }
}
