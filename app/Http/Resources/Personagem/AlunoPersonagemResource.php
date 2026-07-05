<?php

namespace App\Http\Resources\Personagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlunoPersonagemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $alunoPersonagem = $this->resource['aluno_personagem'];
        $personagem = $alunoPersonagem->personagem;

        return [
            'personagem_id' => $personagem->id,
            'chave' => $personagem->chave,
            'nome' => $personagem->nome,
            'tier' => $personagem->tier,
            'nivel' => $alunoPersonagem->nivel,
            'nivel_maximo' => $personagem->nivel_maximo,
            'questoes_respondidas' => $alunoPersonagem->questoes_respondidas,
            'proximo_nivel_em' => $this->resource['proximo_nivel_em'],
            'equipado' => $alunoPersonagem->equipado,
            'imagem' => $personagem->imagem($alunoPersonagem->nivel),
            'avatar' => $personagem->avatar,
        ];
    }
}
