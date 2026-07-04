<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Permissões por papel.
     *
     * - admin  → gerencia escolas e gestores.
     * - gestor → gerencia turmas, professores, alunos e os vínculos (escopo da sua escola).
     *
     * @var array<string, list<string>>
     */
    private array $matriz = [
        'admin' => [
            'gerenciar escolas',
            'gerenciar gestores',
        ],
        'gestor' => [
            'gerenciar turmas',
            'gerenciar professores',
            'gerenciar alunos',
            'gerenciar vinculos',
        ],
    ];

    public function run(): void
    {
        $todas = collect($this->matriz)->flatten()->unique();

        foreach ($todas as $permissao) {
            Permission::findOrCreate($permissao, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->matriz as $role => $permissoes) {
            Role::findOrCreate($role, 'web')->syncPermissions($permissoes);
        }
    }
}
