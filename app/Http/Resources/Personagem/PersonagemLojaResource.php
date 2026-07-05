<?php

namespace App\Http\Resources\Personagem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonagemLojaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $personagem = $this->resource['personagem'];

        return [
            'id' => $personagem->id,
            'chave' => $personagem->chave,
            'nome' => $personagem->nome,
            'descricao' => $personagem->descricao,
            'tier' => $personagem->tier,
            'preco' => $personagem->preco,
            'nivel_maximo' => $personagem->nivel_maximo,
            'imagem' => $personagem->imagem(1),
            'avatar' => $personagem->avatar,
            'ja_possui' => $this->resource['ja_possui'],
        ];
    }
}
