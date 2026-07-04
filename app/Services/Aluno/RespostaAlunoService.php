<?php

namespace App\Services\Aluno;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Questao;
use App\Models\QuestaoAlternativa;
use App\Services\Conquista\ConquistaService;
use App\Services\Missao\MissaoService;
use App\Services\Personagem\PersonagemService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RespostaAlunoService
{
    /** Bônus de pontos/XP por dificuldade. */
    private const BONUS = ['facil' => 0, 'media' => 2, 'dificil' => 5];

    private const PONTOS_ERRO = 2;

    private const XP_ERRO = 2;

    private const ENERGIA_ERRO = 1;

    public function __construct(
        private readonly PerfilAlunoService $perfis,
        private readonly ConquistaService $conquistas,
        private readonly MissaoService $missoes,
        private readonly PersonagemService $personagens,
    ) {}

    /** Limite máximo de questões retornadas por requisição. */
    private const LIMITE_MAXIMO = 50;

    /**
     * Questões que o aluno ainda pode responder (mesma escola, ativas e não respondidas).
     * Suporta filtro por disciplina, ordem aleatória e limite de quantidade.
     */
    public function disponiveis(Aluno $aluno, ?int $disciplinaId = null, bool $aleatorio = false, ?int $limite = null): Collection
    {
        return Questao::query()
            ->where('escola_id', $aluno->escola_id)
            ->where('status', 'ativa')
            ->whereDoesntHave('respostas', fn ($q) => $q->where('aluno_id', $aluno->id))
            ->when($disciplinaId, fn ($q) => $q->whereHas(
                'habilidades',
                fn ($h) => $h->where('disciplina_id', $disciplinaId),
            ))
            ->with(['habilidades.disciplina', 'alternativas'])
            ->when($aleatorio, fn ($q) => $q->inRandomOrder(), fn ($q) => $q->latest('id'))
            ->when($limite, fn ($q) => $q->limit(min($limite, self::LIMITE_MAXIMO)))
            ->get();
    }

    /**
     * Disciplinas com questões na escola do aluno, com o progresso dele em cada uma.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function disciplinasComProgresso(Aluno $aluno): \Illuminate\Support\Collection
    {
        $totais = DB::table('questoes')
            ->join('habilidade_questao', 'habilidade_questao.questao_id', '=', 'questoes.id')
            ->join('habilidades', 'habilidades.id', '=', 'habilidade_questao.habilidade_id')
            ->where('questoes.escola_id', $aluno->escola_id)
            ->where('questoes.status', 'ativa')
            ->groupBy('habilidades.disciplina_id')
            ->selectRaw('habilidades.disciplina_id as disciplina_id, COUNT(DISTINCT questoes.id) as total')
            ->pluck('total', 'disciplina_id');

        $respondidas = DB::table('respostas_alunos')
            ->join('questoes', 'questoes.id', '=', 'respostas_alunos.questao_id')
            ->join('habilidade_questao', 'habilidade_questao.questao_id', '=', 'questoes.id')
            ->join('habilidades', 'habilidades.id', '=', 'habilidade_questao.habilidade_id')
            ->where('respostas_alunos.aluno_id', $aluno->id)
            ->groupBy('habilidades.disciplina_id')
            ->selectRaw('habilidades.disciplina_id as disciplina_id, COUNT(DISTINCT questoes.id) as total')
            ->pluck('total', 'disciplina_id');

        return Disciplina::query()
            ->whereIn('id', $totais->keys())
            ->orderBy('nome')
            ->get()
            ->map(function (Disciplina $disciplina) use ($totais, $respondidas) {
                $total = (int) ($totais[$disciplina->id] ?? 0);
                $feitas = (int) ($respondidas[$disciplina->id] ?? 0);

                return [
                    'disciplina' => $disciplina,
                    'total' => $total,
                    'respondidas' => $feitas,
                    'disponiveis' => max(0, $total - $feitas),
                ];
            });
    }

    /**
     * Registra a resposta do aluno, aplica pontos/XP/energia e devolve o feedback.
     *
     * @return array<string, mixed>
     */
    public function responder(Aluno $aluno, Questao $questao, int $alternativaId): array
    {
        $perfil = $this->perfis->garantir($aluno);
        $questao->loadMissing('alternativas');

        if ($aluno->respostas()->where('questao_id', $questao->id)->exists()) {
            throw ValidationException::withMessages([
                'questao' => ['Você já respondeu esta questão.'],
            ]);
        }

        $alternativa = $questao->alternativas()->find($alternativaId);

        if (! $alternativa instanceof QuestaoAlternativa) {
            throw ValidationException::withMessages([
                'alternativa_id' => ['A alternativa não pertence a esta questão.'],
            ]);
        }

        if ($perfil->energia < 1) {
            throw ValidationException::withMessages([
                'energia' => ['Sem energia suficiente. Aguarde a regeneração.'],
            ]);
        }

        $correta = (bool) $alternativa->correta;
        $bonus = self::BONUS[$questao->dificuldade] ?? 0;

        // Acerto: mais pontos e sem custo de energia.
        // Erro: menos pontos e perde 1 de energia — mas sem feedback negativo
        // (a mensagem é sempre encorajadora e o gabarito é revelado).
        $pontos = $correta ? $questao->pontos + $bonus : self::PONTOS_ERRO;
        $xp = $correta ? 10 + $bonus : self::XP_ERRO;
        $energiaGasta = $correta ? 0 : self::ENERGIA_ERRO;

        $gabarito = $questao->alternativas->firstWhere('correta', true);

        return DB::transaction(function () use ($aluno, $questao, $alternativa, $perfil, $correta, $pontos, $xp, $energiaGasta, $gabarito) {
            $resposta = $aluno->respostas()->create([
                'questao_id' => $questao->id,
                'alternativa_id' => $alternativa->id,
                'correta' => $correta,
                'pontos_ganhos' => $pontos,
                'xp_ganho' => $xp,
                'energia_gasta' => $energiaGasta,
                'respondido_em' => now(),
            ]);

            $perfil->pontos += $pontos;
            $perfil->xp += $xp;
            $perfil->nivel = intdiv($perfil->xp, 100) + 1;

            if ($energiaGasta > 0) {
                $perfil->energia = max(0, $perfil->energia - $energiaGasta);
                $perfil->energia_atualizada_em = now();
            }

            $perfil->save();

            // Conquistas e missões usam o mesmo perfil (as recompensas somam nele).
            $aluno->setRelation('perfil', $perfil);
            $conquistasDesbloqueadas = $this->conquistas->avaliar($aluno);
            $missoesConcluidas = $this->missoes->avaliar($aluno);
            $personagem = $this->personagens->registrarResposta($aluno);

            return [
                'correta' => $correta,
                'gabarito' => $gabarito ? ['id' => $gabarito->id, 'texto' => $gabarito->texto] : null,
                'mensagem' => $correta
                    ? 'Mandou bem! Você acertou. 🎉'
                    : 'Boa tentativa! Veja a resposta certa e siga em frente — você ainda ganhou pontos. 💪',
                'pontos_ganhos' => $pontos,
                'xp_ganho' => $xp,
                'energia_gasta' => $energiaGasta,
                'conquistas_desbloqueadas' => $conquistasDesbloqueadas,
                'missoes_concluidas' => $missoesConcluidas,
                'personagem' => $personagem,
                'resposta' => $resposta,
                'perfil' => $perfil,
            ];
        });
    }
}
