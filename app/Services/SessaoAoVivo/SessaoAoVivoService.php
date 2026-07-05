<?php

namespace App\Services\SessaoAoVivo;

use App\Events\SessaoAoVivoAtualizada;
use App\Events\SessaoAoVivoEncerrada;
use App\Events\SessaoAoVivoQuestaoEnviada;
use App\Events\SessaoAoVivoRespostaRecebida;
use App\Models\Aluno;
use App\Models\Questao;
use App\Models\QuestaoAlternativa;
use App\Models\SessaoAoVivo;
use App\Models\SessaoAoVivoParticipante;
use App\Models\SessaoAoVivoQuestao;
use App\Models\Turma;
use App\Models\User;
use App\Services\Aluno\PerfilAlunoService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SessaoAoVivoService
{
    private const XP_ACERTO = 10;

    private const XP_ERRO = 2;

    public function __construct(private readonly PerfilAlunoService $perfis) {}

    /**
     * @return Collection<int, SessaoAoVivo>
     */
    public function listarProfessor(User $professor): Collection
    {
        $this->encerrarSessoesSemHeartbeat($professor);

        return SessaoAoVivo::query()
            ->where('professor_id', $professor->id)
            ->with(['turma', 'questoes.questao'])
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criar(User $professor, array $dados): SessaoAoVivo
    {
        $turma = $this->turmaDoProfessor($professor, (int) $dados['turma_id']);

        $this->garantirSemSessaoAtiva($professor, $turma);

        return DB::transaction(function () use ($professor, $turma, $dados) {
            $sessao = SessaoAoVivo::query()->create([
                'escola_id' => $turma->escola_id,
                'turma_id' => $turma->id,
                'professor_id' => $professor->id,
                'titulo' => $dados['titulo'] ?? null,
                'status' => SessaoAoVivo::STATUS_AGUARDANDO,
                'professor_online_em' => now(),
            ]);

            SessaoAoVivoAtualizada::dispatch($sessao);

            return $sessao->load(['turma', 'professor', 'questoes.questao']);
        });
    }

    public function selecionarEEnviarQuestao(
        SessaoAoVivo $sessao,
        Questao $questao,
        User $professor
    ): SessaoAoVivo {
        $this->resolverProfessorAusente($sessao);
        $this->garantirStatus($sessao, [SessaoAoVivo::STATUS_EM_ANDAMENTO]);

        $questao = $this->questoesDoProfessor($professor, [$questao->id])->first();

        if ($sessao->questoes()->where('questao_id', $questao->id)->exists()) {
            throw ValidationException::withMessages([
                'questao' => ['Esta questão já foi utilizada nesta sessão.'],
            ]);
        }

        $sessaoQuestao = DB::transaction(function () use ($sessao, $questao) {
            return $sessao->questoes()->create([
                'questao_id' => $questao->id,
                'ordem' => ((int) $sessao->questoes()->max('ordem')) + 1,
            ]);
        });

        return $this->enviarQuestao($sessao, $sessaoQuestao);
    }

    public function iniciar(SessaoAoVivo $sessao): SessaoAoVivo
    {
        $this->resolverProfessorAusente($sessao);
        $this->garantirStatus($sessao, [SessaoAoVivo::STATUS_AGUARDANDO, SessaoAoVivo::STATUS_PAUSADA]);

        $sessao->update([
            'status' => SessaoAoVivo::STATUS_EM_ANDAMENTO,
            'iniciada_em' => $sessao->iniciada_em ?? now(),
            'pausada_em' => null,
            'professor_online_em' => now(),
        ]);

        SessaoAoVivoAtualizada::dispatch($sessao);

        return $sessao->refresh();
    }

    public function pausar(SessaoAoVivo $sessao): SessaoAoVivo
    {
        $this->resolverProfessorAusente($sessao);
        $this->garantirStatus($sessao, [SessaoAoVivo::STATUS_EM_ANDAMENTO]);

        $sessao->update([
            'status' => SessaoAoVivo::STATUS_PAUSADA,
            'pausada_em' => now(),
            'professor_online_em' => now(),
        ]);

        SessaoAoVivoAtualizada::dispatch($sessao);

        return $sessao->refresh();
    }

    public function retomar(SessaoAoVivo $sessao): SessaoAoVivo
    {
        $this->resolverProfessorAusente($sessao);
        $this->garantirStatus($sessao, [SessaoAoVivo::STATUS_PAUSADA]);

        $sessao->update([
            'status' => SessaoAoVivo::STATUS_EM_ANDAMENTO,
            'pausada_em' => null,
            'professor_online_em' => now(),
        ]);

        SessaoAoVivoAtualizada::dispatch($sessao);

        return $sessao->refresh();
    }

    public function heartbeat(SessaoAoVivo $sessao): SessaoAoVivo
    {
        $sessao = $this->resolverProfessorAusente($sessao);

        if ($sessao->ativa()) {
            $sessao->update(['professor_online_em' => now()]);
        }

        return $sessao->refresh();
    }

    public function encerrar(SessaoAoVivo $sessao, string $motivo = 'professor'): SessaoAoVivo
    {
        if (! $sessao->ativa()) {
            return $sessao->refresh();
        }

        $sessao->questoes()->where('atual', true)->update([
            'atual' => false,
            'encerrada_em' => now(),
        ]);

        $sessao->update([
            'status' => SessaoAoVivo::STATUS_FINALIZADA,
            'finalizada_em' => now(),
            'motivo_encerramento' => $motivo,
        ]);

        SessaoAoVivoEncerrada::dispatch($sessao);

        return $sessao->refresh();
    }

    public function enviarQuestao(SessaoAoVivo $sessao, SessaoAoVivoQuestao $sessaoQuestao): SessaoAoVivo
    {
        $this->resolverProfessorAusente($sessao);
        $this->garantirStatus($sessao, [SessaoAoVivo::STATUS_EM_ANDAMENTO]);

        if ((int) $sessaoQuestao->sessao_ao_vivo_id !== (int) $sessao->id) {
            throw ValidationException::withMessages([
                'questao' => ['A questão não pertence a esta sessão.'],
            ]);
        }

        DB::transaction(function () use ($sessao, $sessaoQuestao) {
            $sessao->questoes()->where('atual', true)->update([
                'atual' => false,
                'encerrada_em' => now(),
            ]);

            $sessaoQuestao->update([
                'atual' => true,
                'enviada_em' => now(),
                'encerrada_em' => null,
            ]);

            $sessao->update(['professor_online_em' => now()]);
        });

        SessaoAoVivoQuestaoEnviada::dispatch($sessao->refresh());

        return $sessao->refresh();
    }

    public function enviarProxima(SessaoAoVivo $sessao): SessaoAoVivo
    {
        $proxima = $sessao->questoes()
            ->whereNull('enviada_em')
            ->orderBy('ordem')
            ->first();

        if (! $proxima instanceof SessaoAoVivoQuestao) {
            throw ValidationException::withMessages([
                'questao' => ['Não há próxima questão pendente nesta sessão.'],
            ]);
        }

        return $this->enviarQuestao($sessao, $proxima);
    }

    /**
     * @return array<string, mixed>
     */
    public function entrar(SessaoAoVivo $sessao, Aluno $aluno): array
    {
        $sessao = $this->resolverProfessorAusente($sessao);
        $this->garantirAlunoDaTurma($sessao, $aluno);

        if (! $sessao->ativa()) {
            throw ValidationException::withMessages([
                'sessao' => ['Esta sessão já foi encerrada.'],
            ]);
        }

        $this->registrarParticipante($sessao, $aluno);
        SessaoAoVivoAtualizada::dispatch($sessao);

        return SessaoAoVivoPayload::estadoAluno($sessao->refresh(), $aluno);
    }

    /**
     * @return array<string, mixed>
     */
    public function estadoProfessor(SessaoAoVivo $sessao): array
    {
        $sessao = $this->heartbeat($sessao);

        return SessaoAoVivoPayload::estadoProfessor($sessao);
    }

    /**
     * @return array<string, mixed>
     */
    public function estadoAluno(SessaoAoVivo $sessao, Aluno $aluno): array
    {
        $sessao = $this->resolverProfessorAusente($sessao);
        $this->garantirAlunoDaTurma($sessao, $aluno);

        return SessaoAoVivoPayload::estadoAluno($sessao, $aluno);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function ativaParaAluno(Aluno $aluno): ?array
    {
        $sessao = SessaoAoVivo::query()
            ->whereIn('turma_id', $aluno->turmas()->pluck('turmas.id'))
            ->whereIn('status', [
                SessaoAoVivo::STATUS_AGUARDANDO,
                SessaoAoVivo::STATUS_EM_ANDAMENTO,
                SessaoAoVivo::STATUS_PAUSADA,
            ])
            ->latest('id')
            ->first();

        if (! $sessao instanceof SessaoAoVivo) {
            return null;
        }

        return $this->estadoAluno($sessao, $aluno);
    }

    /**
     * @return array<string, mixed>
     */
    public function responder(SessaoAoVivo $sessao, Aluno $aluno, int $alternativaId): array
    {
        return DB::transaction(function () use ($sessao, $aluno, $alternativaId) {
            $sessao = SessaoAoVivo::query()->lockForUpdate()->findOrFail($sessao->id);
            $sessao = $this->resolverProfessorAusente($sessao);

            $this->garantirAlunoDaTurma($sessao, $aluno);
            $this->garantirStatus($sessao, [SessaoAoVivo::STATUS_EM_ANDAMENTO]);

            $sessaoQuestao = $sessao->questaoAtual()->with('questao.alternativas')->first();

            if (! $sessaoQuestao instanceof SessaoAoVivoQuestao) {
                throw ValidationException::withMessages([
                    'questao' => ['Nenhuma questão está aberta nesta sessão.'],
                ]);
            }

            $jaRespondeu = $sessao->respostas()
                ->where('sessao_ao_vivo_questao_id', $sessaoQuestao->id)
                ->where('aluno_id', $aluno->id)
                ->exists();

            if ($jaRespondeu) {
                throw ValidationException::withMessages([
                    'resposta' => ['Você já respondeu esta questão.'],
                ]);
            }

            $alternativa = QuestaoAlternativa::query()
                ->where('questao_id', $sessaoQuestao->questao_id)
                ->find($alternativaId);

            if (! $alternativa instanceof QuestaoAlternativa) {
                throw ValidationException::withMessages([
                    'alternativa_id' => ['A alternativa não pertence à questão atual.'],
                ]);
            }

            $this->registrarParticipante($sessao, $aluno);

            $correta = (bool) $alternativa->correta;
            $pontos = $correta ? (int) $sessaoQuestao->questao->pontos : 0;
            $xp = $correta ? self::XP_ACERTO : self::XP_ERRO;
            $tempo = $sessaoQuestao->enviada_em
                ? (int) $sessaoQuestao->enviada_em->diffInMilliseconds(now())
                : 0;

            $resposta = $sessao->respostas()->create([
                'sessao_ao_vivo_questao_id' => $sessaoQuestao->id,
                'questao_id' => $sessaoQuestao->questao_id,
                'aluno_id' => $aluno->id,
                'alternativa_id' => $alternativa->id,
                'correta' => $correta,
                'tempo_resposta_ms' => $tempo,
                'pontos_ganhos' => $pontos,
                'xp_ganho' => $xp,
                'respondido_em' => now(),
            ]);

            $this->premiar($aluno, $pontos, $xp);

            SessaoAoVivoRespostaRecebida::dispatch($sessao->refresh(), $resposta);

            return [
                'correta' => $correta,
                'pontos_ganhos' => $pontos,
                'xp_ganho' => $xp,
                'estado' => SessaoAoVivoPayload::estadoAluno($sessao->refresh(), $aluno),
            ];
        });
    }

    public function encerrarSessoesSemHeartbeat(?User $professor = null): void
    {
        SessaoAoVivo::query()
            ->whereIn('status', [SessaoAoVivo::STATUS_AGUARDANDO, SessaoAoVivo::STATUS_EM_ANDAMENTO, SessaoAoVivo::STATUS_PAUSADA])
            ->where('professor_online_em', '<', now()->subSeconds(SessaoAoVivo::HEARTBEAT_TTL_SEGUNDOS))
            ->when($professor, fn ($q) => $q->where('professor_id', $professor->id))
            ->get()
            ->each(fn (SessaoAoVivo $sessao) => $this->encerrar($sessao, 'professor_ausente'));
    }

    private function resolverProfessorAusente(SessaoAoVivo $sessao): SessaoAoVivo
    {
        if (
            $sessao->ativa()
            && $sessao->professor_online_em
            && $sessao->professor_online_em->lt(now()->subSeconds(SessaoAoVivo::HEARTBEAT_TTL_SEGUNDOS))
        ) {
            return $this->encerrar($sessao, 'professor_ausente');
        }

        return $sessao->refresh();
    }

    private function registrarParticipante(SessaoAoVivo $sessao, Aluno $aluno): SessaoAoVivoParticipante
    {
        $participante = SessaoAoVivoParticipante::query()->firstOrNew([
            'sessao_ao_vivo_id' => $sessao->id,
            'aluno_id' => $aluno->id,
        ]);

        if (! $participante->exists) {
            $participante->entrou_em = now();
        }

        $participante->saiu_em = null;
        $participante->save();

        return $participante;
    }

    private function premiar(Aluno $aluno, int $pontos, int $xp): void
    {
        $perfil = $this->perfis->garantir($aluno);
        $perfil->pontos += $pontos;
        $perfil->pontuacao_total += $pontos;
        $perfil->xp += $xp;
        $perfil->nivel = intdiv($perfil->xp, 100) + 1;
        $perfil->save();
    }

    private function garantirSemSessaoAtiva(User $professor, Turma $turma): void
    {
        $ativas = [SessaoAoVivo::STATUS_AGUARDANDO, SessaoAoVivo::STATUS_EM_ANDAMENTO, SessaoAoVivo::STATUS_PAUSADA];

        if (SessaoAoVivo::query()->where('professor_id', $professor->id)->whereIn('status', $ativas)->exists()) {
            throw ValidationException::withMessages([
                'sessao' => ['Encerre a sessão atual antes de criar outra.'],
            ]);
        }

        if (SessaoAoVivo::query()->where('turma_id', $turma->id)->whereIn('status', $ativas)->exists()) {
            throw ValidationException::withMessages([
                'turma_id' => ['Esta turma já possui uma sessão ao vivo ativa.'],
            ]);
        }
    }

    private function turmaDoProfessor(User $professor, int $turmaId): Turma
    {
        $turma = Turma::query()->find($turmaId);

        if (! $turma || (int) $turma->escola_id !== (int) $professor->escola_id || ! $professor->turmas()->whereKey($turmaId)->exists()) {
            throw ValidationException::withMessages([
                'turma_id' => ['Você só pode iniciar sessão em turmas vinculadas a você.'],
            ]);
        }

        return $turma;
    }

    /**
     * @param  array<int, int>  $questaoIds
     * @return Collection<int, Questao>
     */
    private function questoesDoProfessor(User $professor, array $questaoIds): Collection
    {
        $ids = collect($questaoIds)->map(fn ($id) => (int) $id)->unique()->values();

        $questoes = Questao::query()
            ->whereIn('id', $ids)
            ->where('escola_id', $professor->escola_id)
            ->where('professor_id', $professor->id)
            ->where('status', 'ativa')
            ->with('alternativas')
            ->get()
            ->keyBy('id');

        if ($questoes->count() !== $ids->count()) {
            throw ValidationException::withMessages([
                'questoes' => ['Selecione apenas questões ativas do seu banco.'],
            ]);
        }

        return new Collection($ids->map(fn (int $id) => $questoes[$id])->all());
    }

    /**
     * @param  list<string>  $statusPermitidos
     */
    private function garantirStatus(SessaoAoVivo $sessao, array $statusPermitidos): void
    {
        if (! in_array($sessao->status, $statusPermitidos, true)) {
            throw ValidationException::withMessages([
                'sessao' => ['A sessão não está em um estado válido para esta ação.'],
            ]);
        }
    }

    private function garantirAlunoDaTurma(SessaoAoVivo $sessao, Aluno $aluno): void
    {
        $participa = $aluno->turmas()->whereKey($sessao->turma_id)->exists();

        if (! $participa) {
            throw ValidationException::withMessages([
                'sessao' => ['Você não participa da turma desta sessão.'],
            ]);
        }
    }
}
