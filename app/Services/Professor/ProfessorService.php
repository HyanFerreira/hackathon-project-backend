<?php

namespace App\Services\Professor;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProfessorService
{
    private const ROLE = 'professor';

    public function allForEscola(int $escolaId): Collection
    {
        return User::query()
            ->role(self::ROLE)
            ->where('escola_id', $escolaId)
            ->with('roles')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $escolaId, array $data): User
    {
        $data['escola_id'] = $escolaId;

        $professor = User::query()->create($data);
        $professor->syncRoles([self::ROLE]);

        return $professor->load('roles');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $professor, array $data): User
    {
        unset($data['escola_id']);

        $professor->update($data);

        return $professor->load('roles');
    }

    public function delete(User $professor): void
    {
        $professor->delete();
    }
}
