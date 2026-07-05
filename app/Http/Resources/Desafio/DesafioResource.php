<?php

namespace App\Http\Resources\Desafio;

use App\Models\Aluno;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesafioResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'status' => $this->status,
            'disciplina_id' => $this->disciplina_id,
            'quantidade_questoes' => $this->quantidade_questoes,
            'questao_atual' => $this->questao_atual,
            'vencedor_id' => $this->vencedor_id,
            'iniciado_em' => $this->iniciado_em,
            'finalizado_em' => $this->finalizado_em,
            'desafiante' => $this->participante($this->whenLoaded('desafiante')),
            'desafiado' => $this->participante($this->whenLoaded('desafiado')),
        ];
    }

    private function participante($aluno): mixed
    {
        if (! $aluno instanceof Aluno) {
            return null;
        }

        return [
            'id' => $aluno->id,
            'nome' => $aluno->nome,
            'codigo' => $aluno->codigo,
        ];
    }
}
