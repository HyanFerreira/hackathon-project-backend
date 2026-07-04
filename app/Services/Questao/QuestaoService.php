<?php

namespace App\Services\Questao;

use App\Models\Questao;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QuestaoService
{
    public function allForProfessor(int $escolaId, int $professorId): Collection
    {
        return Questao::query()
            ->where('escola_id', $escolaId)
            ->where('professor_id', $professorId)
            ->with(['alternativas', 'habilidades.disciplina'])
            ->latest('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $escolaId, int $professorId, array $data): Questao
    {
        return DB::transaction(function () use ($escolaId, $professorId, $data) {
            $questao = Questao::query()->create([
                'escola_id' => $escolaId,
                'professor_id' => $professorId,
                'enunciado' => $data['enunciado'],
                'dificuldade' => $data['dificuldade'] ?? 'media',
                'pontos' => $data['pontos'] ?? 10,
                'status' => $data['status'] ?? 'ativa',
            ]);

            $this->salvarAlternativas($questao, $data['alternativas']);
            $questao->habilidades()->sync($data['habilidades']);

            return $questao->load(['alternativas', 'habilidades.disciplina']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Questao $questao, array $data): Questao
    {
        return DB::transaction(function () use ($questao, $data) {
            $questao->update(array_filter(
                [
                    'enunciado' => $data['enunciado'] ?? null,
                    'dificuldade' => $data['dificuldade'] ?? null,
                    'pontos' => $data['pontos'] ?? null,
                    'status' => $data['status'] ?? null,
                ],
                fn ($valor) => $valor !== null,
            ));

            if (array_key_exists('alternativas', $data)) {
                $questao->alternativas()->delete();
                $this->salvarAlternativas($questao, $data['alternativas']);
            }

            if (array_key_exists('habilidades', $data)) {
                $questao->habilidades()->sync($data['habilidades']);
            }

            return $questao->load(['alternativas', 'habilidades.disciplina']);
        });
    }

    public function delete(Questao $questao): void
    {
        $questao->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $alternativas
     */
    private function salvarAlternativas(Questao $questao, array $alternativas): void
    {
        foreach ($alternativas as $alternativa) {
            $questao->alternativas()->create([
                'texto' => $alternativa['texto'],
                'correta' => filter_var($alternativa['correta'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
