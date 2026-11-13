# Rotas do gestor

Prefixo `/api/gestor`, middleware `auth:sanctum`. Tudo fica no escopo da escola do gestor.

## Cadastros

| Recurso | Rotas | Permissão |
|---------|-------|-----------|
| Turmas | `apiResource turmas` | `gerenciar turmas` |
| Professores | `apiResource professores` (parâmetro `{professor}`) | `gerenciar professores` |
| Alunos | `apiResource alunos` | `gerenciar alunos` |

Cada `apiResource` gera `GET /`, `POST /`, `GET /{id}`, `PUT/PATCH /{id}` e `DELETE /{id}`.

## Vínculos

Exige a permissão `gerenciar vinculos`.

| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `turmas/{turma}/professores` | Vincula um professor à turma |
| DELETE | `turmas/{turma}/professores/{professor}` | Desvincula o professor |
| POST | `turmas/{turma}/alunos` | Vincula um aluno à turma |
| DELETE | `turmas/{turma}/alunos/{aluno}` | Desvincula o aluno |

## Painel

Exige o papel `gestor`.

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `dashboard` | Visão geral da escola |
| GET | `desempenho` | Desempenho por turma/disciplina |
| GET | `ranking/escola` | Ranking da escola |
| GET | `ranking/turmas/{turma}` | Ranking de uma turma |
