<?php

namespace App\Services\Turma;

use App\Models\Turma;
use Illuminate\Database\Eloquent\Collection;

class TurmaService
{
    public function allForEscola(int $escolaId): Collection
    {
        return Turma::query()
            ->where('escola_id', $escolaId)
            ->with(['professores', 'alunos'])
            ->orderBy('nome')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $escolaId, array $data): Turma
    {
        $data['escola_id'] = $escolaId;

        return Turma::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Turma $turma, array $data): Turma
    {
        unset($data['escola_id']);

        $turma->update($data);

        return $turma->load(['professores', 'alunos']);
    }

    public function delete(Turma $turma): void
    {
        $turma->delete();
    }
}
