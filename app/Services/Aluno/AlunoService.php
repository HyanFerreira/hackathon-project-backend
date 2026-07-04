<?php

namespace App\Services\Aluno;

use App\Models\Aluno;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AlunoService
{
    public function allForEscola(int $escolaId): Collection
    {
        return Aluno::query()
            ->where('escola_id', $escolaId)
            ->orderBy('nome')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $escolaId, array $data): Aluno
    {
        return Aluno::query()->create([
            'escola_id' => $escolaId,
            'nome' => $data['nome'],
            'codigo' => $this->gerarCodigoUnico(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Aluno $aluno, array $data): Aluno
    {
        $aluno->update(['nome' => $data['nome']]);

        return $aluno;
    }

    public function delete(Aluno $aluno): void
    {
        $aluno->delete();
    }


    
    private function gerarCodigoUnico(): string
    {
        do {
            $codigo = strtoupper(Str::random(6));
        } while (Aluno::query()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
