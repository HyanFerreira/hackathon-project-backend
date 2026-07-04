<?php

namespace App\Services\Missao;

use App\Models\Aluno;
use App\Models\AlunoMissao;
use App\Models\Missao;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MissaoService
{
    /**
     * Reavalia as missões do aluno no período atual, conclui as recém-completadas
     * (aplicando recompensas) e devolve as que acabaram de ser concluídas.
     *
     * @return Collection<int, Missao>
     */
    public function avaliar(Aluno $aluno): Collection
    {
        $concluidas = collect();

        foreach (Missao::query()->where('status', 'ativa')->get() as $missao) {
            [$inicio, $fim, $referencia] = $this->janela($missao);
            $progresso = $this->progresso($aluno, $missao, $inicio, $fim);

            $registro = AlunoMissao::query()->firstOrNew([
                'aluno_id' => $aluno->id,
                'missao_id' => $missao->id,
                'referencia' => $referencia,
            ]);

            $registro->progresso = min($progresso, $missao->meta);

            if (! $registro->concluida && $progresso >= $missao->meta) {
                $registro->concluida = true;
                $registro->concluida_em = now();
                $this->aplicarRecompensa($aluno, $missao);
                $concluidas->push($missao);
            }

            $registro->save();
        }

        return $concluidas;
    }

    /**
     * Missões do aluno no período atual, com progresso.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function listar(Aluno $aluno): Collection
    {
        return Missao::query()
            ->where('status', 'ativa')
            ->orderBy('periodo')
            ->orderBy('meta')
            ->get()
            ->map(function (Missao $missao) use ($aluno) {
                [$inicio, $fim, $referencia] = $this->janela($missao);
                $registro = AlunoMissao::query()
                    ->where('aluno_id', $aluno->id)
                    ->where('missao_id', $missao->id)
                    ->where('referencia', $referencia)
                    ->first();

                return [
                    'missao' => $missao,
                    'progresso' => min($this->progresso($aluno, $missao, $inicio, $fim), $missao->meta),
                    'meta' => $missao->meta,
                    'concluida' => (bool) $registro?->concluida,
                    'concluida_em' => $registro?->concluida_em,
                    'referencia' => $referencia,
                ];
            });
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: string}
     */
    private function janela(Missao $missao): array
    {
        $agora = Carbon::now();

        if ($missao->periodo === Missao::PERIODO_SEMANAL) {
            return [
                $agora->copy()->startOfWeek(),
                $agora->copy()->endOfWeek(),
                $agora->format('o-\WW'),
            ];
        }

        return [
            $agora->copy()->startOfDay(),
            $agora->copy()->endOfDay(),
            $agora->format('Y-m-d'),
        ];
    }

    private function progresso(Aluno $aluno, Missao $missao, CarbonInterface $inicio, CarbonInterface $fim): int
    {
        return $aluno->respostas()
            ->whereBetween('respondido_em', [$inicio, $fim])
            ->when($missao->tipo === Missao::TIPO_ACERTAR, fn ($q) => $q->where('correta', true))
            ->count();
    }

    private function aplicarRecompensa(Aluno $aluno, Missao $missao): void
    {
        $perfil = $aluno->perfil;

        if (! $perfil || ($missao->recompensa_pontos === 0 && $missao->recompensa_xp === 0)) {
            return;
        }

        $perfil->pontos += $missao->recompensa_pontos;
        $perfil->pontuacao_total += $missao->recompensa_pontos;
        $perfil->xp += $missao->recompensa_xp;
        $perfil->nivel = intdiv($perfil->xp, 100) + 1;
        $perfil->save();
    }
}
