# Rotas do administrador

Middleware `auth:sanctum`.

## Escolas e gestores (`/api/admin`)

| Recurso | Rotas | Permissão |
|---------|-------|-----------|
| Escolas | `apiResource escolas` | `gerenciar escolas` |
| Gestores | `apiResource gestores` (parâmetro `{gestor}`) | `gerenciar gestores` |

| Método | Rota | Exige |
|--------|------|-------|
| GET | `/api/admin/dashboard` | papel `admin` |

## Impersonação (`/api/impersonate`)

Permite ao admin entrar como outra conta para dar suporte.

| Método | Rota | Exige | Descrição |
|--------|------|-------|-----------|
| POST | `user/{user}` | papel `admin` | Vira um gestor ou professor |
| POST | `aluno/{aluno}` | papel `admin` | Vira um aluno |
| POST | `parar` | qualquer autenticado | Encerra a impersonação (roda com o token de impersonação) |

## Usuários e papéis

| Recurso | Rotas |
|---------|-------|
| Usuários | `apiResource users` |
| Papéis | `apiResource roles` |

Veja a matriz de permissões em [papeis-e-permissoes.md](papeis-e-permissoes.md).
