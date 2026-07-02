<?php

namespace App\Services\Role;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function all(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Role
    {
        return Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }
}
