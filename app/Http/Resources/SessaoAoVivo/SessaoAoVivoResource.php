<?php

namespace App\Http\Resources\SessaoAoVivo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessaoAoVivoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'status' => $this->status,
            'turma' => $this->whenLoaded('turma', fn () => [
                'id' => $this->turma->id,
                'nome' => $this->turma->nome,
                'ano' => $this->turma->ano,
                'turno' => $this->turma->turno,
            ]),
            'questoes_total' => $this->whenLoaded('questoes', fn () => $this->questoes->count()),
            'iniciada_em' => $this->iniciada_em?->toIso8601String(),
            'pausada_em' => $this->pausada_em?->toIso8601String(),
            'finalizada_em' => $this->finalizada_em?->toIso8601String(),
            'professor_online_em' => $this->professor_online_em?->toIso8601String(),
            'motivo_encerramento' => $this->motivo_encerramento,
            'questoes' => $this->whenLoaded('questoes', fn () => $this->questoes->map(fn ($sessaoQuestao) => [
                'id' => $sessaoQuestao->id,
                'questao_id' => $sessaoQuestao->questao_id,
                'ordem' => $sessaoQuestao->ordem,
                'atual' => $sessaoQuestao->atual,
                'enviada_em' => $sessaoQuestao->enviada_em?->toIso8601String(),
                'encerrada_em' => $sessaoQuestao->encerrada_em?->toIso8601String(),
                'questao' => $sessaoQuestao->relationLoaded('questao') ? [
                    'id' => $sessaoQuestao->questao->id,
                    'enunciado' => $sessaoQuestao->questao->enunciado,
                    'dificuldade' => $sessaoQuestao->questao->dificuldade,
                    'pontos' => $sessaoQuestao->questao->pontos,
                ] : null,
            ])),
        ];
    }
}
