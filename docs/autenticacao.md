# Autenticação

A API usa **Laravel Sanctum** com tokens Bearer. Existem dois tipos de conta autenticável:

| Tipo | Model | Como entra | Papéis |
|------|-------|------------|--------|
| Equipe | `App\Models\User` | CPF + senha | `admin`, `gestor`, `professor` (Spatie Permission) |
| Aluno | `App\Models\Aluno` | Código de acesso (ex.: `ALU001`) | — |

## Endpoints públicos

| Método | Rota | Uso |
|--------|------|-----|
| POST | `/api/login` | Login de equipe por sessão (cookie, SPA) |
| POST | `/api/login/token` | Login de equipe retornando token (`cpf`, `password`) |
| POST | `/api/login/aluno` | Login de aluno (`codigo`) |

## Endpoints autenticados (`auth:sanctum`)

| Método | Rota | Uso |
|--------|------|-----|
| GET | `/api/me` | Dados do usuário logado |
| POST | `/api/logout` | Revoga o token atual ou encerra a sessão |
| GET | `/api/aluno/me` | Dados do aluno logado (só token de aluno) |

## Enviando o token

```
Authorization: Bearer <token>
Accept: application/json
```

## Middleware `aluno`

As rotas em `/api/aluno/*` passam pelo middleware `GarantirAluno`, que:

- retorna **403** (`Acesso restrito a alunos.`) se o token não for de um `Aluno`;
- registra a entrada do dia no `LoginStreakService` (sequência de dias de login).
