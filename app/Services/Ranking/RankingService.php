<?php

namespace App\Services\Ranking;

use App\Models\Aluno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /**
     * Ranking de todos os alunos de uma escola.
     */
    public function escola(int $escolaId): Collection
    {
        return $this->ordenar(
            Aluno::query()->where('alunos.escola_id', $escolaId),
        );
    }

    /**
     * Ranking dos alunos de uma turma.
     */
    public function turma(int $turmaId): Collection
    {
        return $this->ordenar(
            Aluno::query()->whereHas('turmas', fn (Builder $q) => $q->where('turmas.id', $turmaId)),
        );
    }

    /**
     * Ordena por pontos (desc), depois XP (desc) e nome; injeta a posição.
     *
     * @param  Builder<Aluno>  $query
     * @return Collection<int, Aluno>
     */
    private function ordenar(Builder $query): Collection
    {
        $alunos = $query
            ->leftJoin('perfis_alunos', 'perfis_alunos.aluno_id', '=', 'alunos.id')
            ->orderByDesc(DB::raw('COALESCE(perfis_alunos.pontuacao_total, 0)'))
            ->orderByDesc(DB::raw('COALESCE(perfis_alunos.xp, 0)'))
            ->orderBy('alunos.nome')
            ->select('alunos.*')
            ->with([
                'perfil',
                'personagens' => fn ($query) => $query
                    ->where('equipado', true)
                    ->with('personagem'),
            ])
            ->get();

        return $alunos->each(fn (Aluno $aluno, int $indice) => $aluno->posicao = $indice + 1);
    }
}
