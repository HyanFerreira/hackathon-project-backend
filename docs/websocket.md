# WebSocket (Reverb)

Os recursos em tempo real usam **Laravel Reverb**. Para funcionar, rode `php artisan reverb:start` **e** `php artisan queue:work`, com `BROADCAST_CONNECTION=reverb` no `.env`.

A autenticação dos canais privados é feita em `/broadcasting/auth` com o token Sanctum.

## Canais (`routes/channels.php`)

| Canal | Quem pode ouvir |
|-------|-----------------|
| `aluno.{id}` | O próprio aluno |
| `turma.{id}` | Alunos da turma e professores vinculados a ela |
| `professor.{id}` | O próprio usuário, se tiver o papel `professor` |

## Eventos de desafio

| Evento (`broadcastAs`) | Classe | Canais |
|------------------------|--------|--------|
| `desafio.recebido` | `DesafioRecebido` | `aluno.{desafiado}` |
| `desafio.atualizado` | `DesafioAtualizado` | `aluno.{desafiante}`, `aluno.{desafiado}` |
| `desafio.questao` | `DesafioProximaQuestao` | `aluno.{desafiante}`, `aluno.{desafiado}` |
| `desafio.finalizado` | `DesafioFinalizado` | `aluno.{desafiante}`, `aluno.{desafiado}` |

## Eventos da sessão ao vivo

Todos vão para `turma.{turma_id}` e `professor.{professor_id}`.

| Evento (`broadcastAs`) | Classe | Payload |
|------------------------|--------|---------|
| `sessao.atualizada` | `SessaoAoVivoAtualizada` | Estado da sessão |
| `sessao.questao` | `SessaoAoVivoQuestaoEnviada` | `sessao`, `questao_atual`, `desempenho` |
| `sessao.resposta` | `SessaoAoVivoRespostaRecebida` | `sessao`, `resposta` (aluno, acerto, pontos, XP), `desempenho` |
| `sessao.encerrada` | `SessaoAoVivoEncerrada` | Estado final |

## Exemplo com Laravel Echo

Como os eventos usam `broadcastAs`, escute com um ponto na frente do nome:

```js
echo.private(`aluno.${alunoId}`)
  .listen('.desafio.recebido', (e) => console.log(e.desafio));
```
