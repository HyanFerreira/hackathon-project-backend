<?php

namespace App\Http\Resources\Missao;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MissaoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'icone' => $this->icone,
            'periodo' => $this->periodo,
            'recompensa_pontos' => $this->recompensa_pontos,
            'recompensa_xp' => $this->recompensa_xp,
        ];
    }
}
