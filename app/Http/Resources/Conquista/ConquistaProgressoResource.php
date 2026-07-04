<?php

namespace App\Http\Resources\Conquista;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConquistaProgressoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $conquista = $this->resource['conquista'];

        return [
            'id' => $conquista->id,
            'nome' => $conquista->nome,
            'descricao' => $conquista->descricao,
            'icone' => $conquista->icone,
            'tipo' => $conquista->tipo,
            'meta' => $conquista->meta,
            'atual' => $this->resource['atual'],
            'desbloqueada' => $this->resource['desbloqueada'],
            'desbloqueada_em' => $this->resource['desbloqueada_em'],
            'recompensa_pontos' => $conquista->recompensa_pontos,
            'recompensa_xp' => $conquista->recompensa_xp,
        ];
    }
}
