# Rotas do aluno

Prefixo `/api/aluno`, middlewares `auth:sanctum` + `aluno` (veja [autenticação](autenticacao.md)).

## Perfil e progresso

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `me` | Dados do aluno logado |
| GET | `perfil` | Perfil gamificado (pontos, XP, nível, energia) |
| GET | `dashboard` | Resumo do desempenho |
| GET | `conquistas` | Conquistas desbloqueadas e bloqueadas |
| GET | `missoes` | Missões e progresso |
| GET | `ranking/turma` | Ranking da turma |
| GET | `ranking/escola` | Ranking da escola |

## Questões

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `disciplinas` | Disciplinas disponíveis para praticar |
| GET | `questoes` | Lista de questões |
| POST | `questoes/{questao}/responder` | Responde uma questão |
| GET | `respostas` | Histórico de respostas |

## Loja e personagens

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `loja` | Personagens à venda |
| POST | `loja/{personagem}/comprar` | Compra com pontos |
| GET | `personagens` | Inventário do aluno |
| POST | `personagens/{personagem}/equipar` | Equipa um personagem |

## Desafios entre colegas (tempo real)

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `colegas` | Colegas que podem ser desafiados |
| GET | `desafios` | Desafios do aluno |
| POST | `desafios` | Cria um desafio |
| POST | `desafios/{desafio}/aceitar` | Aceita |
| POST | `desafios/{desafio}/recusar` | Recusa |
| GET | `desafios/{desafio}/atual` | Questão atual |
| POST | `desafios/{desafio}/responder` | Responde a questão atual |

## Sessão ao vivo do professor

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `sessoes-ao-vivo/resumo` | Resumo das sessões |
| GET | `sessoes-ao-vivo/ativa` | Sessão ativa da turma, se houver |
| POST | `sessoes-ao-vivo/{sessao}/entrar` | Entra na sessão |
| GET | `sessoes-ao-vivo/{sessao}/atual` | Questão atual |
| POST | `sessoes-ao-vivo/{sessao}/responder` | Responde |

Os eventos em tempo real dessas áreas estão em [websocket.md](websocket.md).
