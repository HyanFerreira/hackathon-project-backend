<?php

namespace App\Services\Conquista;

use App\Models\Aluno;
use App\Models\Conquista;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConquistaService
{
    /**
     * Avalia as conquistas do aluno, desbloqueia as recém-alcançadas
     * (aplicando as recompensas) e devolve as que acabaram de ser obtidas.
     *
     * @return Collection<int, Conquista>
     */
    public function avaliar(Aluno $aluno): Collection
    {
        $stats = $this->estatisticas($aluno);
        $jaObtidas = $aluno->conquistas()->pluck('conquistas.id')->all();

        $novas = Conquista::query()
            ->where('status', 'ativa')
            ->whereNotIn('id', $jaObtidas)
            ->get()
            ->filter(fn (Conquista $c) => $this->valorAtual($c->tipo, $stats) >= $c->meta)
            ->values();

        foreach ($novas as $conquista) {
            $inserted = DB::table('aluno_conquista')->insertOrIgnore([
                'aluno_id' => $aluno->id,
                'conquista_id' => $conquista->id,
                'desbloqueada_em' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($inserted > 0) {
                $this->aplicarRecompensa($aluno, $conquista);
            }
        }

        return $novas;
    }

    public function sincronizar(Aluno $aluno): void
    {
        $this->avaliar($aluno);
    }

    /**
     * Progresso do aluno em todas as conquistas (obtidas e pendentes).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function progresso(Aluno $aluno): Collection
    {
        $stats = $this->estatisticas($aluno);
        $obtidas = $aluno->conquistas()->pluck('aluno_conquista.desbloqueada_em', 'conquistas.id');

        return Conquista::query()
            ->where('status', 'ativa')
            ->orderBy('meta')
            ->get()
            ->map(fn (Conquista $c) => [
                'conquista' => $c,
                'atual' => min($this->valorAtual($c->tipo, $stats), $c->meta),
                'desbloqueada' => $obtidas->has($c->id),
                'desbloqueada_em' => $obtidas->get($c->id),
            ]);
    }

    /**
     * @return array<string, int>
     */
    private function estatisticas(Aluno $aluno): array
    {
        $perfil = $aluno->perfil;

        return [
            Conquista::TIPO_RESPONDIDAS => $aluno->respostas()->count(),
            Conquista::TIPO_ACERTOS => $aluno->respostas()->where('correta', true)->count(),
            Conquista::TIPO_SEQUENCIA => $this->maiorSequenciaDeAcertos($aluno),
            Conquista::TIPO_PONTOS => (int) ($perfil->pontuacao_total ?? 0),
            Conquista::TIPO_NIVEL => (int) ($perfil->nivel ?? 1),
        ];
    }

    /**
     * @param  array<string, int>  $stats
     */
    private function valorAtual(string $tipo, array $stats): int
    {
        return $stats[$tipo] ?? 0;
    }

    private function maiorSequenciaDeAcertos(Aluno $aluno): int
    {
        $maior = 0;
        $atual = 0;

        foreach ($aluno->respostas()->orderBy('respondido_em')->orderBy('id')->pluck('correta') as $correta) {
            $atual = $correta ? $atual + 1 : 0;
            $maior = max($maior, $atual);
        }

        return $maior;
    }

    private function aplicarRecompensa(Aluno $aluno, Conquista $conquista): void
    {
        $perfil = $aluno->perfil;

        if (! $perfil || ($conquista->recompensa_pontos === 0 && $conquista->recompensa_xp === 0)) {
            return;
        }

        $perfil->pontos += $conquista->recompensa_pontos;
        $perfil->pontuacao_total += $conquista->recompensa_pontos;
        $perfil->xp += $conquista->recompensa_xp;
        $perfil->nivel = intdiv($perfil->xp, 100) + 1;
        $perfil->save();
    }
}
