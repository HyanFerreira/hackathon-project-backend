<?php

namespace App\Services\Aluno;

use App\Models\Aluno;
use App\Models\Questao;
use App\Models\QuestaoAlternativa;
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

    public function __construct(private readonly PerfilAlunoService $perfis) {}

    /**
     * Questões que o aluno ainda pode responder (mesma escola, ativas e não respondidas).
     */
    public function disponiveis(Aluno $aluno, ?int $disciplinaId = null): Collection
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
            ->latest('id')
            ->get();
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

            return [
                'correta' => $correta,
                'gabarito' => $gabarito ? ['id' => $gabarito->id, 'texto' => $gabarito->texto] : null,
                'mensagem' => $correta
                    ? 'Mandou bem! Você acertou. 🎉'
                    : 'Boa tentativa! Veja a resposta certa e siga em frente — você ainda ganhou pontos. 💪',
                'pontos_ganhos' => $pontos,
                'xp_ganho' => $xp,
                'energia_gasta' => $energiaGasta,
                'resposta' => $resposta,
                'perfil' => $perfil,
            ];
        });
    }
}
