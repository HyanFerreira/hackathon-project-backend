# Rotas do professor

Prefixo `/api/professor`, middleware `auth:sanctum`.

## Banco de questões

Exige a permissão `gerenciar questoes`. As questões são avaliadas por habilidade da BNCC e ficam no escopo da escola do professor.

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `questoes` | Lista as questões |
| POST | `questoes` | Cria |
| GET | `questoes/{questao}` | Detalha |
| PUT/PATCH | `questoes/{questao}` | Atualiza |
| DELETE | `questoes/{questao}` | Remove |

## Painel e turmas

Exige o papel `professor`.

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `dashboard` | Visão geral |
| GET | `desempenho` | Desempenho dos alunos |
| GET | `turmas` | Turmas vinculadas |
| GET | `ranking/turmas/{turma}` | Ranking de uma turma |

## Sessão ao vivo

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `sessoes-ao-vivo` | Lista as sessões |
| POST | `sessoes-ao-vivo` | Cria uma sessão |
| GET | `sessoes-ao-vivo/{sessao}` | Detalha |
| POST | `sessoes-ao-vivo/{sessao}/iniciar` | Inicia |
| POST | `sessoes-ao-vivo/{sessao}/pausar` | Pausa |
| POST | `sessoes-ao-vivo/{sessao}/retomar` | Retoma |
| POST | `sessoes-ao-vivo/{sessao}/heartbeat` | Mantém a sessão viva |
| POST | `sessoes-ao-vivo/{sessao}/proxima` | Avança para a próxima questão |
| POST | `sessoes-ao-vivo/{sessao}/questoes/{questao}/enviar` | Envia uma questão específica |
| POST | `sessoes-ao-vivo/{sessao}/encerrar` | Encerra |
| GET | `sessoes-ao-vivo/{sessao}/desempenho` | Desempenho da turma na sessão |

O painel recebe as atualizações pelo canal `professor.{id}` (veja [websocket.md](websocket.md)).
