# Referência BNCC

Rotas somente leitura, disponíveis para qualquer usuário autenticado (`auth:sanctum`). Os dados vêm do `BnccSeeder`.

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/disciplinas` | Lista as disciplinas |
| GET | `/api/disciplinas/{disciplina}` | Detalha uma disciplina |
| GET | `/api/habilidades` | Lista as habilidades da BNCC |
| GET | `/api/habilidades/{habilidade}` | Detalha uma habilidade |

## Relações

- Uma `Disciplina` tem muitas `Habilidade`s.
- Uma `Questao` se liga a várias `Habilidade`s (muitos-para-muitos), o que permite medir o desempenho por habilidade.

> O aluno tem a rota própria `/api/aluno/disciplinas`, com as disciplinas disponíveis para praticar.
