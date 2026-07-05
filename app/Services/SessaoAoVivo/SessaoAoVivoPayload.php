<?php

namespace App\Services\SessaoAoVivo;

use App\Models\Aluno;
use App\Models\SessaoAoVivo;
use App\Models\SessaoAoVivoQuestao;
use App\Models\SessaoAoVivoResposta;

class SessaoAoVivoPayload
{
    /**
     * @return array<string, mixed>
     */
    public static function estadoProfessor(SessaoAoVivo $sessao): array
    {
        return [
            'sessao' => self::sessao($sessao),
            'questao_atual' => self::questaoAtual($sessao),
            'desempenho' => self::desempenho($sessao),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function estadoAluno(SessaoAoVivo $sessao, Aluno $aluno): array
    {
        $questaoAtual = $sessao->questaoAtual()->first();
        $minhaResposta = null;

        if ($questaoAtual instanceof SessaoAoVivoQuestao) {
            $resposta = SessaoAoVivoResposta::query()
                ->where('sessao_ao_vivo_id', $sessao->id)
                ->where('sessao_ao_vivo_questao_id', $questaoAtual->id)
                ->where('aluno_id', $aluno->id)
                ->first();

            if ($resposta instanceof SessaoAoVivoResposta) {
                $minhaResposta = [
                    'alternativa_id' => $resposta->alternativa_id,
                    'correta' => $resposta->correta,
                    'pontos_ganhos' => $resposta->pontos_ganhos,
                    'xp_ganho' => $resposta->xp_ganho,
                    'respondido_em' => $resposta->respondido_em?->toIso8601String(),
                ];
            }
        }

        return [
            'sessao' => self::sessao($sessao),
            'questao_atual' => self::questaoAtual($sessao),
            'eu_respondi' => $minhaResposta !== null,
            'minha_resposta' => $minhaResposta,
            'ranking' => self::desempenho($sessao)['ranking'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function sessao(SessaoAoVivo $sessao): array
    {
        $sessao->loadMissing(['turma', 'professor', 'questoes']);

        return [
            'id' => $sessao->id,
            'titulo' => $sessao->titulo,
            'status' => $sessao->status,
            'turma' => [
                'id' => $sessao->turma?->id,
                'nome' => $sessao->turma?->nome,
                'ano' => $sessao->turma?->ano,
                'turno' => $sessao->turma?->turno,
            ],
            'professor' => [
                'id' => $sessao->professor?->id,
                'nome' => $sessao->professor?->name,
            ],
            'questoes_total' => $sessao->questoes->count(),
            'questao_ids' => $sessao->questoes->pluck('questao_id')->values()->all(),
            'iniciada_em' => $sessao->iniciada_em?->toIso8601String(),
            'pausada_em' => $sessao->pausada_em?->toIso8601String(),
            'finalizada_em' => $sessao->finalizada_em?->toIso8601String(),
            'professor_online_em' => $sessao->professor_online_em?->toIso8601String(),
            'motivo_encerramento' => $sessao->motivo_encerramento,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function questaoAtual(SessaoAoVivo $sessao): ?array
    {
        $sessaoQuestao = $sessao->questaoAtual()
            ->with('questao.alternativas')
            ->first();

        if (! $sessaoQuestao instanceof SessaoAoVivoQuestao || ! $sessaoQuestao->questao) {
            return null;
        }

        $questao = $sessaoQuestao->questao;

        return [
            'id' => $sessaoQuestao->id,
            'questao_id' => $questao->id,
            'ordem' => $sessaoQuestao->ordem,
            'enviada_em' => $sessaoQuestao->enviada_em?->toIso8601String(),
            'encerrada_em' => $sessaoQuestao->encerrada_em?->toIso8601String(),
            'questao' => [
                'id' => $questao->id,
                'enunciado' => $questao->enunciado,
                'dificuldade' => $questao->dificuldade,
                'pontos' => $questao->pontos,
                'alternativas' => $questao->alternativas->map(fn ($alternativa) => [
                    'id' => $alternativa->id,
                    'texto' => $alternativa->texto,
                ])->values()->all(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function desempenho(SessaoAoVivo $sessao): array
    {
        $sessao->loadMissing('turma.alunos');

        $stats = $sessao->respostas()
            ->selectRaw('aluno_id, COUNT(*) as respostas, SUM(CASE WHEN correta THEN 1 ELSE 0 END) as acertos, SUM(pontos_ganhos) as pontos, SUM(xp_ganho) as xp, SUM(tempo_resposta_ms) as tempo_total_ms')
            ->groupBy('aluno_id')
            ->get()
            ->keyBy('aluno_id');

        $ranking = $sessao->turma->alunos
            ->map(function (Aluno $aluno) use ($stats) {
                $dados = $stats->get($aluno->id);
                $respostas = (int) ($dados->respostas ?? 0);
                $acertos = (int) ($dados->acertos ?? 0);

                return [
                    'aluno' => [
                        'id' => $aluno->id,
                        'nome' => $aluno->nome,
                        'codigo' => $aluno->codigo,
                    ],
                    'respostas' => $respostas,
                    'acertos' => $acertos,
                    'pontos' => (int) ($dados->pontos ?? 0),
                    'xp' => (int) ($dados->xp ?? 0),
                    'tempo_total_ms' => (int) ($dados->tempo_total_ms ?? 0),
                    'percentual_acerto' => $respostas > 0 ? round(($acertos / $respostas) * 100, 1) : 0,
                ];
            })
            ->sort(fn (array $a, array $b) => self::compararRanking($a, $b))
            ->values()
            ->map(function (array $item, int $indice) {
                $item['posicao'] = $indice + 1;

                return $item;
            });

        $questaoAtual = $sessao->questaoAtual()->first();
        $respondidasAtual = 0;
        $corretasAtual = 0;
        $participantes = $sessao->participantes()->count();

        if ($questaoAtual instanceof SessaoAoVivoQuestao) {
            $respondidasAtual = $sessao->respostas()
                ->where('sessao_ao_vivo_questao_id', $questaoAtual->id)
                ->count();
            $corretasAtual = $sessao->respostas()
                ->where('sessao_ao_vivo_questao_id', $questaoAtual->id)
                ->where('correta', true)
                ->count();
        }

        return [
            'alunos_total' => $sessao->turma->alunos->count(),
            'participantes' => $participantes,
            'respostas_total' => $sessao->respostas()->count(),
            'questao_atual' => [
                'respondidas' => $respondidasAtual,
                'corretas' => $corretasAtual,
                'pendentes' => max(0, $participantes - $respondidasAtual),
            ],
            'ranking' => $ranking->all(),
        ];
    }

    private static function compararRanking(array $a, array $b): int
    {
        return ($b['pontos'] <=> $a['pontos'])
            ?: ($b['acertos'] <=> $a['acertos'])
            ?: ($a['tempo_total_ms'] <=> $b['tempo_total_ms'])
            ?: strcmp($a['aluno']['nome'], $b['aluno']['nome']);
    }
}
