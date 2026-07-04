<?php

namespace App\Http\Resources\Conquista;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConquistaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'icone' => $this->icone,
            'recompensa_pontos' => $this->recompensa_pontos,
            'recompensa_xp' => $this->recompensa_xp,
        ];
    }
}
