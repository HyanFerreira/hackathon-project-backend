<?php

namespace App\Services\Desafio;

use App\Models\Desafio;
use App\Models\Questao;

/**
 * Monta os payloads do desafio (usados no HTTP e no broadcast) — sem gabarito.
 */
class DesafioPayload
{
    /**
     * Questão atual da partida (sem revelar a alternativa correta) + cronômetro.
     *
     * @return array<string, mixed>|null
     */
    public static function questaoAtual(Desafio $desafio): ?array
    {
        $questaoId = $desafio->questaoAtualId();

        if (! $questaoId) {
            return null;
        }

        $questao = Questao::query()->with('alternativas')->find($questaoId);

        if (! $questao) {
            return null;
        }

        $iniciadaEm = $desafio->questao_iniciada_em;

        return [
            'desafio_id' => $desafio->id,
            'status' => $desafio->status,
            'ordem' => $desafio->questao_atual,
            'total' => $desafio->quantidade_questoes,
            'segundos' => Desafio::SEGUNDOS_POR_QUESTAO,
            'iniciada_em' => $iniciadaEm?->toIso8601String(),
            'expira_em' => $iniciadaEm?->copy()->addSeconds(Desafio::SEGUNDOS_POR_QUESTAO)->toIso8601String(),
            'questao' => [
                'id' => $questao->id,
                'enunciado' => $questao->enunciado,
                'dificuldade' => $questao->dificuldade,
                'alternativas' => $questao->alternativas->map(fn ($a) => [
                    'id' => $a->id,
                    'texto' => $a->texto,
                ])->all(),
            ],
        ];
    }

    /**
     * Placar final do desafio (acertos e tempo total de cada aluno).
     *
     * @return array<string, mixed>
     */
    public static function resultado(Desafio $desafio): array
    {
        $porAluno = $desafio->respostas()
            ->selectRaw('aluno_id, SUM(correta) as acertos, SUM(tempo_resposta_ms) as tempo_total')
            ->groupBy('aluno_id')
            ->get()
            ->keyBy('aluno_id');

        $placar = fn (int $alunoId) => [
            'aluno_id' => $alunoId,
            'acertos' => (int) ($porAluno[$alunoId]->acertos ?? 0),
            'tempo_total_ms' => (int) ($porAluno[$alunoId]->tempo_total ?? 0),
        ];

        return [
            'desafio_id' => $desafio->id,
            'status' => $desafio->status,
            'vencedor_id' => $desafio->vencedor_id,
            'empate' => $desafio->status === Desafio::STATUS_FINALIZADO && $desafio->vencedor_id === null,
            'placar' => [
                'desafiante' => $placar((int) $desafio->desafiante_id),
                'desafiado' => $placar((int) $desafio->desafiado_id),
            ],
        ];
    }
}
