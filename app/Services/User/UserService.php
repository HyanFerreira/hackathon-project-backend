<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function all(): Collection
    {
        return User::query()->with('roles')->orderBy('name')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        $roles = $data['roles'] ?? [];
        unset($data['roles']);

        $user = User::query()->create($data);
        $user->syncRoles($roles);

        return $user->load('roles');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $roles = $data['roles'] ?? null;
        unset($data['roles']);

        $user->update($data);

        if ($roles !== null) {
            $user->syncRoles($roles);
        }

        return $user->load('roles');
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
