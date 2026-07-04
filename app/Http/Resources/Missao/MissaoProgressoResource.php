<?php

namespace App\Http\Resources\Missao;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MissaoProgressoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $missao = $this->resource['missao'];

        return [
            'id' => $missao->id,
            'titulo' => $missao->titulo,
            'descricao' => $missao->descricao,
            'icone' => $missao->icone,
            'tipo' => $missao->tipo,
            'periodo' => $missao->periodo,
            'meta' => $missao->meta,
            'progresso' => $this->resource['progresso'],
            'concluida' => $this->resource['concluida'],
            'concluida_em' => $this->resource['concluida_em'],
            'recompensa_pontos' => $missao->recompensa_pontos,
            'recompensa_xp' => $missao->recompensa_xp,
        ];
    }
}
