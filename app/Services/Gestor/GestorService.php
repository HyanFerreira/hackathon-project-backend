<?php

namespace App\Services\Gestor;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GestorService
{
    private const ROLE = 'gestor';

    public function all(): Collection
    {
        return User::query()
            ->role(self::ROLE)
            ->with(['roles', 'escola'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $gestor = User::query()->create($data);
        $gestor->syncRoles([self::ROLE]);

        return $gestor->load(['roles', 'escola']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $gestor, array $data): User
    {
        $gestor->update($data);

        return $gestor->load(['roles', 'escola']);
    }

    public function delete(User $gestor): void
    {
        $gestor->delete();
    }
}
