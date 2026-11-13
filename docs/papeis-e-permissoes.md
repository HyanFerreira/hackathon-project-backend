# Papéis e permissões

Os papéis e permissões usam **Spatie Laravel Permission** (guard `web`) e são criados por `RoleSeeder` e `PermissionSeeder`.

| Papel | Permissões |
|-------|------------|
| `admin` | `gerenciar escolas`, `gerenciar gestores` |
| `gestor` | `gerenciar turmas`, `gerenciar professores`, `gerenciar alunos`, `gerenciar vinculos` |
| `professor` | `gerenciar questoes` |

Alunos não têm papel: são outro model (`Aluno`) e acessam só `/api/aluno/*` pelo middleware `aluno`.

## Como as rotas checam

- `permission:<nome>,sanctum` para cadastros (ex.: `permission:gerenciar turmas,sanctum`).
- `role:<papel>,sanctum` para painéis (ex.: `role:gestor,sanctum`).

## Adicionando uma permissão

1. Inclua a permissão no array `$matriz` de `database/seeders/PermissionSeeder.php`.
2. Rode `php artisan db:seed --class=PermissionSeeder` (o seeder usa `findOrCreate` e `syncPermissions`, então pode rodar de novo).
3. Proteja a rota com `permission:<nome>,sanctum`.
