<?php

namespace App\Services\Desafio;

use App\Events\DesafioAtualizado;
use App\Events\DesafioFinalizado;
use App\Events\DesafioProximaQuestao;
use App\Events\DesafioRecebido;
use App\Models\Aluno;
use App\Models\Desafio;
use App\Models\Questao;
use App\Models\QuestaoAlternativa;
use App\Services\Aluno\PerfilAlunoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DesafioService
{
    /** Minutos até o convite expirar. */
    private const EXPIRACAO_MIN = 2;

    /** Custo de energia (cada aluno) numa partida valendo pontos. */
    private const CUSTO_ENERGIA = 1;

    /** Limite de partidas valendo pontos por aluno por dia. */
    private const LIMITE_VALENDO_DIA = 10;

    public function __construct(private readonly PerfilAlunoService $perfis) {}

    /**
     * Cria um desafio (pendente) e notifica o colega desafiado.
     *
     * @param  array<string, mixed>  $dados
     */
    public function criar(Aluno $desafiante, array $dados): Desafio
    {
        $desafiado = Aluno::query()->find($dados['desafiado_id']);

        if (! $desafiado || (int) $desafiado->id === (int) $desafiante->id) {
            throw ValidationException::withMessages([
                'desafiado_id' => ['Selecione um colega válido.'],
            ]);
        }

        $turmaId = $this->turmaEmComum($desafiante, $desafiado);

        if (! $turmaId) {
            throw ValidationException::withMessages([
                'desafiado_id' => ['Você só pode desafiar colegas da sua turma.'],
            ]);
        }

        $desafio = Desafio::query()->create([
            'escola_id' => $desafiante->escola_id,
            'turma_id' => $turmaId,
            'desafiante_id' => $desafiante->id,
            'desafiado_id' => $desafiado->id,
            'disciplina_id' => $dados['disciplina_id'] ?? null,
            'tipo' => $dados['tipo'] ?? Desafio::TIPO_AMISTOSO,
            'status' => Desafio::STATUS_PENDENTE,
            'quantidade_questoes' => $dados['quantidade_questoes'] ?? 5,
            'questao_atual' => 0,
            'expira_em' => now()->addMinutes(self::EXPIRACAO_MIN),
        ]);

        DesafioRecebido::dispatch($desafio);

        return $desafio->load(['desafiante', 'desafiado']);
    }

    /**
     * O desafiado aceita: valida energia/limite (valendo), sorteia as questões
     * e inicia a partida ao vivo (primeira questão via broadcast).
     */
    public function aceitar(Desafio $desafio): Desafio
    {
        $this->garantirPendente($desafio);

        if ($desafio->tipo === Desafio::TIPO_VALENDO) {
            $this->validarValendo($desafio);
        }

        return DB::transaction(function () use ($desafio) {
            $ordem = 1;
            foreach ($this->sortearQuestoes($desafio) as $questaoId) {
                $desafio->questoes()->create(['questao_id' => $questaoId, 'ordem' => $ordem++]);
            }

            $total = $desafio->questoes()->count();

            if ($total === 0) {
                throw ValidationException::withMessages([
                    'desafio' => ['Não há questões disponíveis para este desafio.'],
                ]);
            }

            if ($desafio->tipo === Desafio::TIPO_VALENDO) {
                $this->consumirEnergia($desafio);
            }

            $desafio->update([
                'status' => Desafio::STATUS_EM_ANDAMENTO,
                'quantidade_questoes' => $total,
                'iniciado_em' => now(),
                'questao_atual' => 1,
                'questao_iniciada_em' => now(),
            ]);

            DesafioProximaQuestao::dispatch($desafio);

            return $desafio->load(['desafiante', 'desafiado']);
        });
    }

    public function recusar(Desafio $desafio): Desafio
    {
        $this->garantirPendente($desafio);

        $desafio->update(['status' => Desafio::STATUS_RECUSADO]);
        DesafioAtualizado::dispatch($desafio);

        return $desafio->load(['desafiante', 'desafiado']);
    }

    /**
     * Estado atual da partida para um aluno (resolve expiração antes).
     *
     * @return array<string, mixed>
     */
    public function estado(Desafio $desafio, Aluno $aluno): array
    {
        return DB::transaction(function () use ($desafio, $aluno) {
            $desafio = Desafio::query()->lockForUpdate()->findOrFail($desafio->id);
            $this->resolverExpiracao($desafio);

            return $this->montarEstado($desafio, $aluno);
        });
    }

    /**
     * Registra a resposta do aluno na questão atual (com o tempo).
     *
     * @return array<string, mixed>
     */
    public function responder(Desafio $desafio, Aluno $aluno, int $alternativaId): array
    {
        return DB::transaction(function () use ($desafio, $aluno, $alternativaId) {
            $desafio = Desafio::query()->lockForUpdate()->findOrFail($desafio->id);
            $this->resolverExpiracao($desafio);

            if ($desafio->status !== Desafio::STATUS_EM_ANDAMENTO) {
                return $this->montarEstado($desafio, $aluno);
            }

            $questaoId = $desafio->questaoAtualId();

            $jaRespondeu = $desafio->respostas()
                ->where('aluno_id', $aluno->id)
                ->where('questao_id', $questaoId)
                ->exists();

            if ($jaRespondeu) {
                throw ValidationException::withMessages([
                    'resposta' => ['Você já respondeu esta questão.'],
                ]);
            }

            $alternativa = QuestaoAlternativa::query()
                ->where('questao_id', $questaoId)
                ->find($alternativaId);

            if (! $alternativa instanceof QuestaoAlternativa) {
                throw ValidationException::withMessages([
                    'alternativa_id' => ['A alternativa não pertence à questão atual.'],
                ]);
            }

            $limite = Desafio::SEGUNDOS_POR_QUESTAO * 1000;
            $tempo = (int) min($desafio->questao_iniciada_em->diffInMilliseconds(now()), $limite);

            $desafio->respostas()->create([
                'aluno_id' => $aluno->id,
                'questao_id' => $questaoId,
                'alternativa_id' => $alternativa->id,
                'correta' => (bool) $alternativa->correta,
                'tempo_resposta_ms' => $tempo,
                'respondido_em' => now(),
            ]);

            if ($this->ambosResponderam($desafio, $questaoId)) {
                $this->avancarOuFinalizar($desafio);
            }

            return $this->montarEstado($desafio->refresh(), $aluno);
        });
    }

    // ----------------------------------------------------------------------
    // Internos
    // ----------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function montarEstado(Desafio $desafio, Aluno $aluno): array
    {
        if ($desafio->status === Desafio::STATUS_EM_ANDAMENTO) {
            $questaoId = $desafio->questaoAtualId();
            $oponenteId = (int) $desafio->desafiante_id === (int) $aluno->id
                ? $desafio->desafiado_id
                : $desafio->desafiante_id;

            $payload = DesafioPayload::questaoAtual($desafio) ?? [];
            $payload['eu_respondi'] = $desafio->respostas()
                ->where('aluno_id', $aluno->id)->where('questao_id', $questaoId)->exists();
            $payload['oponente_respondeu'] = $desafio->respostas()
                ->where('aluno_id', $oponenteId)->where('questao_id', $questaoId)->exists();

            return $payload;
        }

        return DesafioPayload::resultado($desafio);
    }

    private function resolverExpiracao(Desafio $desafio): void
    {
        if ($desafio->status !== Desafio::STATUS_EM_ANDAMENTO || ! $desafio->questao_iniciada_em) {
            return;
        }

        $expirou = $desafio->questao_iniciada_em
            ->copy()->addSeconds(Desafio::SEGUNDOS_POR_QUESTAO)->isPast();

        if (! $expirou) {
            return;
        }

        $questaoId = $desafio->questaoAtualId();
        $limite = Desafio::SEGUNDOS_POR_QUESTAO * 1000;

        foreach ([$desafio->desafiante_id, $desafio->desafiado_id] as $alunoId) {
            $respondeu = $desafio->respostas()
                ->where('aluno_id', $alunoId)->where('questao_id', $questaoId)->exists();

            if (! $respondeu) {
                $desafio->respostas()->create([
                    'aluno_id' => $alunoId,
                    'questao_id' => $questaoId,
                    'alternativa_id' => null,
                    'correta' => false,
                    'tempo_resposta_ms' => $limite,
                    'respondido_em' => now(),
                ]);
            }
        }

        $this->avancarOuFinalizar($desafio);
    }

    private function avancarOuFinalizar(Desafio $desafio): void
    {
        if ($desafio->questao_atual < $desafio->quantidade_questoes) {
            $desafio->update([
                'questao_atual' => $desafio->questao_atual + 1,
                'questao_iniciada_em' => now(),
            ]);

            DesafioProximaQuestao::dispatch($desafio->load(['desafiante', 'desafiado']));

            return;
        }

        $this->finalizar($desafio);
    }

    private function finalizar(Desafio $desafio): void
    {
        $stats = $desafio->respostas()
            ->selectRaw('aluno_id, SUM(correta) as acertos, SUM(tempo_resposta_ms) as tempo')
            ->groupBy('aluno_id')
            ->get()
            ->keyBy('aluno_id');

        $a = (int) $desafio->desafiante_id;
        $b = (int) $desafio->desafiado_id;
        $acertosA = (int) ($stats[$a]->acertos ?? 0);
        $acertosB = (int) ($stats[$b]->acertos ?? 0);
        $tempoA = (int) ($stats[$a]->tempo ?? 0);
        $tempoB = (int) ($stats[$b]->tempo ?? 0);

        $vencedor = match (true) {
            $acertosA > $acertosB => $a,
            $acertosB > $acertosA => $b,
            $tempoA < $tempoB => $a,
            $tempoB < $tempoA => $b,
            default => null, // empate
        };

        $desafio->update([
            'status' => Desafio::STATUS_FINALIZADO,
            'vencedor_id' => $vencedor,
            'finalizado_em' => now(),
            'questao_iniciada_em' => null,
        ]);

        $this->aplicarRecompensas($desafio, $vencedor);

        DesafioFinalizado::dispatch($desafio->load(['desafiante', 'desafiado']));
    }

    private function aplicarRecompensas(Desafio $desafio, ?int $vencedorId): void
    {
        $valendo = $desafio->tipo === Desafio::TIPO_VALENDO;

        foreach ([$desafio->desafiante, $desafio->desafiado] as $aluno) {
            $empate = $vencedorId === null;
            $venceu = $vencedorId === (int) $aluno->id;

            if ($valendo) {
                [$pontos, $xp] = match (true) {
                    $empate => [15, 10],
                    $venceu => [30, 20],
                    default => [5, 5],
                };
                $this->premiar($aluno, $pontos, $pontos, $xp);
            } else {
                $xp = $empate ? 8 : ($venceu ? 15 : 5);
                $this->premiar($aluno, 0, 0, $xp);
            }
        }
    }

    private function premiar(Aluno $aluno, int $pontos, int $pontuacao, int $xp): void
    {
        $perfil = $this->perfis->garantir($aluno);
        $perfil->pontos += $pontos;
        $perfil->pontuacao_total += $pontuacao;
        $perfil->xp += $xp;
        $perfil->nivel = intdiv($perfil->xp, 100) + 1;
        $perfil->save();
    }

    private function ambosResponderam(Desafio $desafio, ?int $questaoId): bool
    {
        return $desafio->respostas()->where('questao_id', $questaoId)->distinct('aluno_id')->count('aluno_id') >= 2;
    }

    private function validarValendo(Desafio $desafio): void
    {
        foreach ([$desafio->desafiante, $desafio->desafiado] as $aluno) {
            $partidasHoje = Desafio::query()
                ->where('tipo', Desafio::TIPO_VALENDO)
                ->whereIn('status', [Desafio::STATUS_EM_ANDAMENTO, Desafio::STATUS_FINALIZADO])
                ->whereDate('iniciado_em', today())
                ->where(fn ($q) => $q->where('desafiante_id', $aluno->id)->orWhere('desafiado_id', $aluno->id))
                ->count();

            if ($partidasHoje >= self::LIMITE_VALENDO_DIA) {
                throw ValidationException::withMessages([
                    'desafio' => ['Limite diário de partidas valendo pontos atingido.'],
                ]);
            }

            if ($this->perfis->garantir($aluno)->energia < self::CUSTO_ENERGIA) {
                throw ValidationException::withMessages([
                    'energia' => ['Energia insuficiente para uma partida valendo pontos.'],
                ]);
            }
        }
    }

    private function consumirEnergia(Desafio $desafio): void
    {
        foreach ([$desafio->desafiante, $desafio->desafiado] as $aluno) {
            $perfil = $this->perfis->garantir($aluno);
            $perfil->energia = max(0, $perfil->energia - self::CUSTO_ENERGIA);
            $perfil->energia_atualizada_em = now();
            $perfil->save();
        }
    }

    private function garantirPendente(Desafio $desafio): void
    {
        if ($desafio->status !== Desafio::STATUS_PENDENTE) {
            throw ValidationException::withMessages([
                'desafio' => ['Este desafio não está mais pendente.'],
            ]);
        }

        if ($desafio->expira_em && $desafio->expira_em->isPast()) {
            $desafio->update(['status' => Desafio::STATUS_EXPIRADO]);

            throw ValidationException::withMessages([
                'desafio' => ['O convite deste desafio expirou.'],
            ]);
        }
    }

    /**
     * @return Collection<int, int>
     */
    private function sortearQuestoes(Desafio $desafio): Collection
    {
        return Questao::query()
            ->where('escola_id', $desafio->escola_id)
            ->where('status', 'ativa')
            ->when($desafio->disciplina_id, fn ($q) => $q->whereHas(
                'habilidades',
                fn ($h) => $h->where('disciplina_id', $desafio->disciplina_id),
            ))
            ->inRandomOrder()
            ->limit($desafio->quantidade_questoes)
            ->pluck('id');
    }

    private function turmaEmComum(Aluno $a, Aluno $b): ?int
    {
        return $a->turmas()
            ->whereIn('turmas.id', $b->turmas()->pluck('turmas.id'))
            ->value('turmas.id');
    }
}
