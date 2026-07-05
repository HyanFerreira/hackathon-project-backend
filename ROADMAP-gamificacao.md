# Projeto: Sistema de Gamificação de Alunos

## 1. Objetivo do sistema

Criar um sistema educacional gamificado para alunos, professores e gestores escolares.

A ideia principal é permitir que professores criem questões, organizem turmas, acompanhem o desempenho dos alunos e realizem sessões ao vivo com WebSocket, onde as questões aparecem em tempo real para os alunos responderem.

Os alunos ganharão pontos ao responder questões, poderão participar de desafios contra colegas da mesma turma, subir em rankings, cumprir missões, desbloquear conquistas e gastar seus pontos em uma loja com cosméticos/personagens.

O gestor da escola terá uma visão administrativa da escola, podendo gerenciar turmas, professores e alunos, além de acompanhar dashboards de desempenho da escola inteira.

---

## 1.1 Decisões de arquitetura do time (registro)

> Decisões tomadas durante a implementação que ajustam/sobrescrevem sugestões deste roadmap.
> Atualizado em 2026-07-04.

### Autenticação e usuários

- **Dois modelos de autenticação distintos:**
  - **`Usuario` (tabela `users`, model `User`)** — usado por **gestor, professor e admin**. Login por **CPF + senha**.
  - **`Aluno` (tabela `alunos`, model `Aluno`)** — model **separado**, com **guard próprio (`aluno`)**. Login **somente por `codigo`** (o código é a credencial; sem senha nesta fase).
- **Professor NÃO tem tabela própria (Opção A).** Professor é um `User` com a role `professor`. Os vínculos com turmas apontarão para `users.id`. (A tabela stub `professores` foi removida.)
- **Roles do sistema (Spatie, guard `web`):** `admin`, `gestor`, `professor`. O `aluno` não usa roles — é um model separado.
  - Mapeamento em relação ao roadmap original: `manager` → `gestor`, `teacher` → `professor`, `student` → model `Aluno` separado.

### Ajustes pendentes para o frontend

- **Criação de usuários/gestores/professores:** remover o campo `password` dos formulários de criação. O backend agora cria automaticamente com a senha fixa inicial `password`.
- **Edição de usuários/gestores/professores:** manter o campo `password` como opcional. Se o campo for enviado, o backend altera a senha; se não for enviado, a senha atual permanece.
- **CRUD de escolas:** remover `cnpj` dos formulários, tabelas, detalhes e payloads. O backend não valida, não persiste e não retorna mais `cnpj` em escolas.

### Endpoints de login já implementados

```txt
POST /api/login          -> CPF + senha (sessão)        [gestor/professor/admin]
POST /api/login/token    -> CPF + senha (token Sanctum)  [gestor/professor/admin]
POST /api/login/aluno    -> codigo (token Sanctum)       [aluno]
GET  /api/me             -> dados do usuário logado (User)
GET  /api/aluno/me       -> dados do aluno logado (Aluno)
POST /api/logout         -> revoga token / encerra sessão (genérico)
```

### Gestão escolar e controle de permissões (Spatie)

Separação de responsabilidades por papel, aplicada via **permissões do Spatie** (middleware `permission:<perm>,sanctum`):

| Permissão | Papel | O que cobre |
|-----------|-------|-------------|
| `gerenciar escolas` | **admin** | CRUD de escolas |
| `gerenciar gestores` | **admin** | CRUD de gestores (Users role `gestor`) |
| `gerenciar turmas` | **gestor** | CRUD de turmas da sua escola |
| `gerenciar professores` | **gestor** | CRUD de professores da sua escola |
| `gerenciar alunos` | **gestor** | CRUD de alunos da sua escola (gera `codigo`) |
| `gerenciar vinculos` | **gestor** | Vincular/desvincular professor↔turma e aluno↔turma |

- **Admin NÃO tem acesso às áreas de gestor e vice-versa** (separação explícita; sem super-admin via `Gate::before`).
- **Escopo por escola:** todo recurso do gestor é filtrado por `escola_id` do gestor autenticado (trait `EscopoEscola`); acesso a recurso de outra escola retorna **403**.
- **Modelo de dados:** `escolas` 1—N `turmas`; `users.escola_id` e `alunos.escola_id` (nullable; admin sem escola). Vínculos por pivots `professor_turma` (turma↔users) e `aluno_turma` (turma↔alunos).

Endpoints:

```txt
# admin (permission: gerenciar escolas / gerenciar gestores)
GET|POST         /api/admin/escolas
GET|PUT|DELETE   /api/admin/escolas/{escola}
GET|POST         /api/admin/gestores
GET|PUT|DELETE   /api/admin/gestores/{gestor}

# gestor (permission: gerenciar turmas/professores/alunos/vinculos)
GET|POST         /api/gestor/turmas
GET|PUT|DELETE   /api/gestor/turmas/{turma}
GET|POST         /api/gestor/professores
GET|PUT|DELETE   /api/gestor/professores/{professor}
GET|POST         /api/gestor/alunos
GET|PUT|DELETE   /api/gestor/alunos/{aluno}
POST             /api/gestor/turmas/{turma}/professores        (body: professor_id)
DELETE           /api/gestor/turmas/{turma}/professores/{professor}
POST             /api/gestor/turmas/{turma}/alunos             (body: aluno_id)
DELETE           /api/gestor/turmas/{turma}/alunos/{aluno}
```

---

### Banco pedagógico — BNCC e questões por habilidade

Substitui a sugestão original de `subjects`/`topics`/`questions` do roadmap:

- **Disciplinas e habilidades vêm da BNCC** (referência nacional, **global e somente leitura**; seeded via `BnccSeeder`). Não são criadas por escola/gestor/professor.
  - `disciplinas`: componentes curriculares (LP, MA, CI, HI, GE, AR, EF, LI, ER).
  - `habilidades`: código BNCC (ex.: `EF06MA01`), descrição, etapa, ano. (Seed inicial = subconjunto curado do 6º ano; a BNCC completa pode ser importada depois sobre o mesmo modelo.)
- **A questão avalia por HABILIDADE, não por disciplina.** `questoes` N↔N `habilidades` (pivot `habilidade_questao`); a disciplina é derivada das habilidades. Sem `disciplina_id` direto na questão.
- **Professor gerencia o próprio banco** (permissão `gerenciar questoes`), escopado à sua escola e à sua autoria. Regras: ≥1 habilidade, ≥2 alternativas e exatamente 1 correta.
- Disciplinas/habilidades são legíveis por qualquer usuário autenticado (gestor e professor consomem).

Endpoints:

```txt
# referência BNCC (qualquer autenticado, somente leitura)
GET  /api/disciplinas
GET  /api/disciplinas/{disciplina}
GET  /api/habilidades?disciplina_id=&ano=&busca=
GET  /api/habilidades/{habilidade}

# professor (permission: gerenciar questoes)
GET|POST         /api/professor/questoes
GET|PUT|DELETE   /api/professor/questoes/{questao}
```

Permissão adicional: `gerenciar questoes` → papel **professor**.

---

### Área do aluno — responder questões, pontos, XP e energia

- **Perfil gamificado** (`perfis_alunos`, 1—1 com aluno): `pontos`, `pontuacao_total`, `xp`, `nivel`, `energia`, `energia_maxima`. Criado sob demanda no primeiro acesso.
- **Duas pontuações distintas:**
  - **`pontos`** = **saldo gastável** (moeda da loja) — sobe ao ganhar, **desce ao comprar** personagens.
  - **`pontuacao_total`** = **pontuação de desempenho** — só sobe, **nunca diminui**. É o que o **ranking** (aluno/gestor/professor) usa e o que o gestor enxerga. No JSON do ranking, o campo `pontos` corresponde à `pontuacao_total`.
  - Ambas sobem juntas ao responder/ganhar conquista/missão; só o saldo cai na loja. Conquistas do tipo `pontos` usam `pontuacao_total`.
- **Responder questões** (`respostas_alunos`): 1 resposta por questão por aluno (unique). O aluno só vê questões **ativas da sua escola ainda não respondidas**; a visão do aluno **não expõe** qual alternativa é a correta (só revela no feedback).
- **Toda questão é de múltipla escolha com gabarito** (exatamente 1 alternativa correta).
- **Feedback sem tom negativo:** o feedback sempre revela o **gabarito** (id + texto) e traz uma **mensagem encorajadora**, tanto no acerto quanto no erro.
- **Recompensa:**
  - Acerto: `pontos = pontos_da_questao + bônus`; `xp = 10 + bônus`; **sem perda de energia**.
  - Erro: `+2 pontos`, `+2 XP` e **−1 energia**.
  - Bônus por dificuldade: fácil 0, média 2, difícil 5.
  - Nível: `nivel = floor(xp / 100) + 1`.
- **Energia:** máx. 10; regenera 1 a cada 10 min (sob demanda). Responder exige energia ≥ 1; **acertar não consome, errar consome 1**.
- Acesso restrito a **tokens de aluno** via middleware `aluno` (`GarantirAluno`).

**Praticar questões (por disciplina e aleatórias):**

- `GET /api/aluno/disciplinas` — disciplinas com questões na escola do aluno + **progresso** (`total`, `respondidas`, `disponiveis`). Alimenta o seletor "por disciplina" e a lista de disciplinas do lobby.
- `GET /api/aluno/questoes?disciplina_id=` — filtra por disciplina específica.
- `GET /api/aluno/questoes?aleatorio=1&limite=N` — questões em ordem aleatória (mistura de disciplinas), limitadas a N (máx. 50).

**Registro de pontuação:** a cada resposta, o backend grava tanto o **histórico** (`respostas_alunos`: `pontos_ganhos`, `xp_ganho`, `energia_gasta`, `correta`, `respondido_em`) quanto a **pontuação acumulada** (`perfis_alunos`: `pontos`, `xp`, `nivel`).

**Conteúdo de exemplo:** `QuestaoSeeder` popula **10 questões (6º ano)** em 5 disciplinas da BNCC (Matemática, Português, Ciências, História, Geografia), cada uma com 4 alternativas e gabarito — dá conteúdo real para os dois modos de prática.

**Shape do `responder`:** o campo `perfil` da resposta vem **envolto em `data`** (`perfil.data`), igual ao `GET /aluno/perfil`, para casar com o frontend. O feedback também traz `conquistas_desbloqueadas`, `missoes_concluidas` e `personagem` (com `subiu_nivel`).

**Frequência de entrada (streak):**

- O perfil do aluno registra `dias_seguidos_login`, `maior_dias_seguidos_login` e `ultimo_login_em`.
- A sequência é atualizada no login do aluno e também no primeiro acesso às rotas `/api/aluno/*`, cobrindo o caso em que o frontend reaproveita um token salvo.
- Só o primeiro acesso do dia avança a sequência e gera recompensa; múltiplos acessos no mesmo dia não duplicam pontos.
- Se o aluno entra no dia seguinte ao último acesso, soma +1 dia; se pula um ou mais dias, a sequência reinicia em 1.
- Recompensa: **+5 pontos gastáveis** no primeiro acesso do dia, com **+20 pontos extras** a cada 7 dias consecutivos (25 pontos no marco semanal). Essa recompensa **não altera `pontuacao_total`**, para não misturar frequência com ranking de desempenho.
- O backend expõe `streak` no `perfil` e também no retorno de `POST /api/login/aluno` para o frontend exibir a recompensa imediatamente.

Endpoints (aluno):

```txt
GET  /api/aluno/me
GET  /api/aluno/perfil
GET  /api/aluno/disciplinas                       (disciplinas com progresso)
GET  /api/aluno/questoes?disciplina_id=&aleatorio=&limite=   (disponíveis, sem 'correta')
POST /api/aluno/questoes/{questao}/responder     (body: alternativa_id) -> feedback + perfil
GET  /api/aluno/respostas                        (histórico)
```

---

### Impersonação (apenas admin)

- **Somente o admin** pode impersonar, e pode virar **qualquer conta**: gestor, professor ou aluno.
- Impersonar emite um **token novo** para a conta-alvo, marcado com a habilidade `impersonado` (o token original do admin continua válido). O front guarda o token do admin e usa o token-alvo enquanto impersona.
- `POST /impersonate/parar` (chamado com o token de impersonação) **revoga** esse token; o front volta a usar o token original.
- Proteções: token de impersonação **não pode impersonar de novo**; a impersonação é detectada pela habilidade explícita (tokens normais têm `*`, então `tokenCan` não serve).

Endpoints:

```txt
POST /api/impersonate/user/{user}    (admin) -> token agindo como o usuário
POST /api/impersonate/aluno/{aluno}  (admin) -> token agindo como o aluno
POST /api/impersonate/parar          -> encerra a impersonação (revoga o token-alvo)
```

---

### Ranking e dashboards básicos

**Ranking** (sobre `perfis_alunos`, sem tabelas novas): ordena por `pontos` desc, depois `xp` desc, depois nome; devolve `posicao`, aluno e pontos/xp/nível. Alunos sem perfil contam como 0.

- Aluno: ranking da **sua turma** e da **sua escola**.
- Gestor: ranking da **escola** e de **qualquer turma da escola** (escopo validado).
- Professor: ranking **apenas das turmas em que leciona** (senão 403).

**Dashboards básicos** (agregados simples por papel):

- Admin: total de escolas, gestores, professores e alunos.
- Gestor: total de turmas, professores e alunos da **sua escola**.
- Professor: nº de turmas, alunos nas suas turmas, questões criadas e as últimas questões.
- Aluno: perfil (pontos/xp/nível/energia) + turma + **posição no ranking da turma**.

Endpoints:

```txt
GET /api/admin/dashboard                      (role admin)
GET /api/gestor/dashboard                      (role gestor)
GET /api/gestor/ranking/escola
GET /api/gestor/ranking/turmas/{turma}
GET /api/professor/dashboard                   (role professor)
GET /api/professor/ranking/turmas/{turma}      (só turmas do professor)
GET /api/aluno/dashboard                        (middleware aluno)
GET /api/aluno/ranking/turma
GET /api/aluno/ranking/escola
```

---

### Conquistas (marcos permanentes)

- **`conquistas`** (referência global, seed via `ConquistaSeeder`): `nome`, `descricao`, `icone`, `tipo`, `meta`, `recompensa_pontos`, `recompensa_xp`, `status`. Ex.: *Estudioso*, *Foco Total*, *Certeiro*, *Gênio*, *Lenda da Escola*.
- **Tipos de condição:** `questoes_respondidas`, `acertos`, `sequencia_acertos` (maior sequência), `pontos`, `nivel`.
- **`aluno_conquista`** (pivot): desbloqueios do aluno com `desbloqueada_em` (unique aluno+conquista).
- **Desbloqueio automático:** a cada resposta, o `ConquistaService` reavalia as estatísticas do aluno, desbloqueia as recém-alcançadas e **aplica as recompensas** (pontos/XP somam no perfil). As novas conquistas voltam no feedback de `responder` em `conquistas_desbloqueadas`.
- **Listagem:** `GET /api/aluno/conquistas` mostra todas com `atual`, `meta`, `desbloqueada` e `desbloqueada_em` (progresso).

---

### Missões (objetivos temporários)

- **`missoes`** (referência global, seed via `MissaoSeeder`): `titulo`, `descricao`, `icone`, `tipo` (`responder`/`acertar`), `meta`, `periodo` (`diaria`/`semanal`), `recompensa_pontos`, `recompensa_xp`.
- **`aluno_missao`**: progresso por período (`referencia` = `Y-m-d` na diária, `o-\WW` na semanal), `progresso`, `concluida`, `concluida_em` (unique aluno+missao+referencia). O período "reseta" naturalmente ao trocar a referência.
- **Progresso ao vivo:** contado sobre `respostas_alunos` dentro da janela do período. A cada resposta, `MissaoService::avaliar` conclui as recém-completadas e aplica recompensas; voltam no feedback em `missoes_concluidas`.
- Endpoint: `GET /api/aluno/missoes`.

### Loja e personagens que evoluem

- **`personagens`** (catálogo global, seed via `PersonagemSeeder`): **11 personagens**. Um inicial gratuito (`lumi`, tier `free`) + 10 (comum → lendário): `grunt_chibi`, `pip_chibi_v2`, `leafy`, `leo`, `luna`, `nox`, `drako`, `kitsune`, `fenro`, `elyra`. Campos: `chave`, `nome`, `tier`, `preco`, `nivel_maximo` (3). As **imagens ficam no frontend** em `public/personagens/{chave}_level_{N}.svg` (SVG); o backend guarda `chave` + nível e expõe `imagem` = nome do arquivo.
- **Avatar (imagem de perfil/cabeça):** coluna `avatar` em `personagens` guarda o nome do arquivo da cabeça do personagem, padrão **`{chave}_perfil.svg`** (ex.: `lumi_perfil.svg`). Exposta na loja, no inventário e no feedback de resposta. As imagens ficam no front (`public/personagens/`).
- **Personagem inicial (Lumi):** todo aluno **começa com o `lumi` equipado** — garantido no login do aluno (`PersonagemService::garantirInicial`). Ele **aparece na loja** marcado como `free`/`ja_possui`, mas **não pode ser comprado** (compra bloqueada para o tier `free`).
- **`aluno_personagem`**: `nivel`, `questoes_respondidas` (com ele equipado), `equipado`, `comprado_em` (unique aluno+personagem).
- **Regra:** o aluno **compra** um personagem gastando pontos e o **equipa** (só 1 equipado). A cada questão respondida com ele equipado, `questoes_respondidas` sobe e o personagem **evolui de nível** (nível 2 em 10 questões, nível 3 em 30). No feedback de `responder` vem `personagem` com `subiu_nivel`.
- Endpoints: `GET /api/aluno/loja`, `POST /api/aluno/loja/{personagem}/comprar`, `GET /api/aluno/personagens`, `POST /api/aluno/personagens/{personagem}/equipar`.

---

### Desafios entre alunos (ao vivo, WebSocket) — Fase 1: fundação

- **WebSocket via Laravel Reverb.** Broadcasting autenticado por token Sanctum (`withBroadcasting` + `auth:sanctum`). Cada aluno assina o canal privado **`aluno.{id}`** (definido em `routes/channels.php`). Para rodar: `php artisan reverb:start` + um worker `php artisan queue:work` (eventos vão pela fila).
- **Colegas de sala:** `GET /aluno/colegas` — alunos das turmas do aluno (menos ele), para escolher quem desafiar.
- **Tabelas:** `desafios` (desafiante, desafiado, turma, disciplina, tipo, status, `questao_atual`, `questao_iniciada_em`, vencedor), `desafio_questoes` (sorteadas por ordem), `desafio_respostas` (resposta + `tempo_resposta_ms`).
- **Tipos:** `amistoso` e `valendo` (pontos). **Status:** pendente → em_andamento → finalizado (+ recusado/expirado/cancelado).
- **Eventos WebSocket:** `DesafioRecebido` (→ canal do desafiado, notificação do convite) e `DesafioAtualizado` (→ ambos os canais, mudança de status).
- **Endpoints (Fase 1):**

```txt
GET  /api/aluno/colegas
GET  /api/aluno/desafios
POST /api/aluno/desafios                     (desafiado_id, disciplina_id?, tipo?, quantidade_questoes?)
POST /api/aluno/desafios/{desafio}/aceitar   (só o desafiado; sorteia questões e inicia)
POST /api/aluno/desafios/{desafio}/recusar   (só o desafiado)
```

### Desafios — Fase 2: partida ao vivo síncrona ✅

- **Cronômetro compartilhado:** cada questão fica ativa por `Desafio::SEGUNDOS_POR_QUESTAO` (20s) a partir de `questao_iniciada_em`. Os dois recebem a questão ao mesmo tempo via broadcast `DesafioProximaQuestao`.
- **Responder com tempo:** `POST /aluno/desafios/{d}/responder` grava a alternativa + `tempo_resposta_ms` (medido no servidor). A visão não revela o gabarito.
- **Avanço:** quando **os dois respondem** a questão atual, avança na hora. Se o **tempo esgota**, quem não respondeu leva timeout (resolvido quando alguém consulta `GET /aluno/desafios/{d}/atual`) e a partida avança. Sem worker de tick — é event-driven e validado no servidor (com `lockForUpdate`).
- **Vencedor:** mais acertos → menor tempo total → empate.
- **Recompensas:**
  - Amistoso: só XP (vencedor +15, perdedor +5, empate +8). Não mexe em pontos/energia/ranking.
  - Valendo: consome **1 energia** de cada no aceite; vencedor +30 pontos/+30 pontuação/+20 XP, perdedor +5/+5/+5, empate +15/+15/+10. **Afeta o ranking** (pontuação). Limite diário anti-abuso (`LIMITE_VALENDO_DIA`).
- **Eventos:** `DesafioProximaQuestao` (nova questão, aos dois) e `DesafioFinalizado` (placar + vencedor, aos dois).
- **Endpoints (Fase 2):**

```txt
GET  /api/aluno/desafios/{desafio}/atual       (questão atual + cronômetro; resolve timeout)
POST /api/aluno/desafios/{desafio}/responder   (alternativa_id + tempo)
```

---

### Desafios — integração no frontend (a fazer)

O backend (Fase 1 + 2) está pronto. Falta o front consumir:

1. **WebSocket (Laravel Echo + Reverb):**
   - Instalar `laravel-echo` + `pusher-js` e configurar o Echo com `broadcaster: 'reverb'`, usando `NEXT_PUBLIC_REVERB_*` (key/host/port/scheme — equivalentes aos `REVERB_*`/`VITE_REVERB_*` do backend).
   - Autenticar canal privado no endpoint **`/broadcasting/auth`** enviando o header `Authorization: Bearer <token do aluno>` (a auth usa `auth:sanctum`).
   - Assinar o canal privado **`aluno.{id}`** (id do aluno logado) e ouvir os eventos (com ponto na frente, pois usam `broadcastAs`):
     - `.desafio.recebido` → convite chegou (mostrar notificação com aceitar/recusar).
     - `.desafio.atualizado` → status mudou (ex.: recusado).
     - `.desafio.questao` → nova questão da partida (renderizar + iniciar cronômetro por `expira_em`).
     - `.desafio.finalizado` → resultado (placar + `vencedor_id`).
2. **Telas:**
   - **Colegas** (`GET /aluno/colegas`) → escolher quem desafiar; formulário (disciplina opcional, tipo amistoso/valendo, nº de questões) → `POST /aluno/desafios`.
   - **Convite recebido** (via `.desafio.recebido`) → aceitar (`POST .../aceitar`) / recusar (`POST .../recusar`).
   - **Partida ao vivo** → ao receber `.desafio.questao` (ou `GET .../atual`), mostrar a questão + **cronômetro** (de `iniciada_em` até `expira_em`); ao responder → `POST .../responder`; quando o timer zerar, chamar `GET .../atual` para forçar o avanço; mostrar "aguardando oponente" via `oponente_respondeu`.
   - **Resultado** (via `.desafio.finalizado` ou `GET .../atual` finalizado) → placar dos dois + vencedor.
3. **Infra da demo:** rodar `php artisan reverb:start` e `php artisan queue:work` (os eventos vão pela fila).

---

### Sessão ao vivo professor↔turma (WebSocket) — backend ✅

- **Modelo:** sessão em português (`sessoes_ao_vivo`, `sessao_ao_vivo_questoes`, `sessao_ao_vivo_participantes`, `sessao_ao_vivo_respostas`). Uma sessão pertence a **um professor** e **uma turma**; o professor só cria sessão para turmas em que leciona e com questões ativas do próprio banco.
- **Uma turma por vez:** o backend bloqueia múltiplas sessões ativas do mesmo professor e também bloqueia outra sessão ativa para a mesma turma.
- **Status:** `aguardando` → `em_andamento` ↔ `pausada` → `finalizada` (`cancelada` reservado). Professor pode iniciar, pausar, retomar, encerrar e enviar questões uma por uma.
- **Professor saiu da tela:** o frontend deve chamar `POST /professor/sessoes-ao-vivo/{sessao}/heartbeat` enquanto a tela estiver aberta e `POST .../encerrar` ao sair. Se o heartbeat ficar velho por `SessaoAoVivo::HEARTBEAT_TTL_SEGUNDOS` (45s), o backend encerra a sessão com `motivo_encerramento = professor_ausente` na próxima interação/listagem.
- **Pontuação da sessão:** acerto soma os pontos da questão e +10 XP; erro soma 0 pontos e +2 XP. Esses pontos também entram no perfil (`pontos` e `pontuacao_total`), mas o ranking exibido na sessão é calculado sobre `sessao_ao_vivo_respostas`.
- **Professor acompanha em tempo real:** payloads trazem `desempenho` com totais, respostas/corretas da questão atual e `ranking` da turma inteira (inclui alunos que ainda não responderam com 0 pontos).
- **Canais privados:** `turma.{id}` (alunos da turma + professores vinculados) e `professor.{id}` (painel do professor). Eventos com `broadcastAs`:
  - `.sessao.atualizada` → status/entrada/pausa/retomada.
  - `.sessao.questao` → questão enviada, sem gabarito.
  - `.sessao.resposta` → resposta recebida + desempenho/ranking atualizado.
  - `.sessao.encerrada` → resultado final/ranking.
- **Endpoints do professor:**

```txt
GET  /api/professor/sessoes-ao-vivo
POST /api/professor/sessoes-ao-vivo                         (turma_id, titulo?, questoes[])
GET  /api/professor/sessoes-ao-vivo/{sessao}
POST /api/professor/sessoes-ao-vivo/{sessao}/iniciar
POST /api/professor/sessoes-ao-vivo/{sessao}/pausar
POST /api/professor/sessoes-ao-vivo/{sessao}/retomar
POST /api/professor/sessoes-ao-vivo/{sessao}/heartbeat
POST /api/professor/sessoes-ao-vivo/{sessao}/encerrar
POST /api/professor/sessoes-ao-vivo/{sessao}/proxima
POST /api/professor/sessoes-ao-vivo/{sessao}/questoes/{sessaoQuestao}/enviar
GET  /api/professor/sessoes-ao-vivo/{sessao}/desempenho
```

- **Endpoints do aluno:**

```txt
GET  /api/aluno/sessoes-ao-vivo/ativa
POST /api/aluno/sessoes-ao-vivo/{sessao}/entrar
GET  /api/aluno/sessoes-ao-vivo/{sessao}/atual
POST /api/aluno/sessoes-ao-vivo/{sessao}/responder           (alternativa_id)
```

---

### Dashboard avançado do professor (desempenho)

`GET /api/professor/desempenho` — visão do desempenho dos alunos das turmas do professor (sobre `respostas_alunos`):

- **resumo**: turmas, alunos, alunos ativos, respostas, acertos, `taxa_acerto` (%), questões criadas.
- **habilidades_dificeis**: habilidades da BNCC com **menor taxa de acerto** (código, descrição, disciplina, respostas, acertos, taxa) — mostra **onde os alunos vão pior**.
- **disciplinas**: taxa de acerto por disciplina (pior → melhor).
- **questoes_mais_erradas**: questões com menor taxa de acerto.
- **alunos_com_dificuldade**: alunos com menor taxa de acerto.
- **por_turma**: alunos, respostas e taxa de acerto por turma.

Serviço: `App\Services\Professor\DesempenhoService` (agregações via query builder). Escopo = alunos das turmas do professor.

**Dados fake para a demo:** `DesempenhoFakeSeeder` popula **4 turmas** (6º–9º Ano A) da escola principal com ~12 alunos cada e respostas realistas (Matemática mais difícil, cada turma com um nível diferente) e atualiza os perfis — enche os gráficos de desempenho, a comparação entre turmas e o ranking. Já incluído no `DatabaseSeeder` (roda no `migrate:fresh --seed`); também roda avulso com `php artisan db:seed --class=DesempenhoFakeSeeder`.

### Dashboard avançado do gestor (desempenho da escola)

`GET /api/gestor/desempenho` — visão da escola inteira (escopo = alunos da escola do gestor):

- **resumo**: turmas, professores, alunos, alunos ativos, respostas, `taxa_acerto` (%).
- **por_turma**: taxa de acerto por turma, ordenada (**melhor → pior**) — comparação entre turmas.
- **habilidades_dificeis** / **disciplinas**: onde a escola vai pior.
- **professores_ativos**: professores por questões criadas.
- **top_alunos**: alunos com maior pontuação da escola.
- **alunos_com_dificuldade**: alunos com menor taxa de acerto.

Serviço: `App\Services\Gestor\DesempenhoService`.

---

### Convenção de nomes

- **Domínio novo em português** para tabelas, colunas e models (`alunos`, `escolas`, `turmas`, `disciplinas`, `questoes`, etc.).
- **`users`/`User` mantidos em inglês** por serem convenção do Laravel (Sanctum, Spatie, sessions se apoiam neles). As tabelas de vendor (`sessions`, `personal_access_tokens`, tabelas do Spatie) também ficam em inglês.

---

# 2. Tipos de usuários

O sistema terá três perfis principais de acesso:

## 2.1 Gestor

O gestor representa a administração da escola.

### Permissões do gestor

- Gerenciar turmas.
- Gerenciar professores.
- Gerenciar alunos.
- Vincular alunos às turmas.
- Vincular professores às turmas.
- Visualizar logins gerados dos alunos.
- Visualizar dashboard geral da escola.
- Visualizar ranking geral da escola.
- Visualizar ranking por turma.
- Acompanhar alunos com maior pontuação.
- Acompanhar desempenho das turmas.

---

## 2.2 Professor

O professor será responsável pela parte pedagógica.

### Permissões do professor

- Visualizar suas turmas.
- Visualizar alunos das suas turmas.
- Criar questões.
- Editar questões.
- Excluir questões.
- Criar banco de questões por disciplina/matéria.
- Criar sessões ao vivo com WebSocket.
- Enviar questões uma por uma durante a sessão ao vivo.
- Consultar desempenho dos alunos das suas turmas.
- Gerar ou consultar login dos alunos, caso essa regra seja mantida.
- Visualizar dashboard separado por turma.

---

## 2.3 Aluno

O aluno será o usuário gamificado do sistema.

### Permissões do aluno

- Acessar sua conta.
- Visualizar perfil.
- Visualizar pontos.
- Visualizar energia.
- Responder questões.
- Participar de sessões ao vivo.
- Participar de desafios contra colegas da mesma turma.
- Participar de partidas amistosas.
- Participar de partidas valendo pontos.
- Ver ranking da turma.
- Ver ranking da escola, se permitido.
- Comprar personagens/cosméticos na loja.
- Equipar personagem.
- Cumprir missões.
- Liberar conquistas.

---

# 3. Ordem geral de implementação

A implementação deve seguir esta ordem para evitar que partes avançadas sejam construídas antes da base.

## Ordem macro

1. Estrutura base do sistema.
2. Autenticação e permissões.
3. Gestão escolar: escolas, turmas, professores e alunos.
4. Vínculos entre turmas, professores e alunos.
5. Geração de login para alunos.
6. Banco de disciplinas e questões.
7. Área básica do aluno para responder questões.
8. Sistema de pontos e energia.
9. Sistema de ranking.
10. Sistema de desafios entre alunos.
11. Loja de personagens/cosméticos.
12. Missões e conquistas.
13. Sessão ao vivo com WebSocket.
14. Dashboards avançados.
15. Acessibilidade adaptativa.
16. Finalização visual seguindo o modelo usado pelo governo/prefeitura.

---

# 4. Fase 1 — Estrutura base do projeto

Esta é a primeira coisa que deve ser implementada.

## 4.1 Criar o projeto

Criar o projeto com a stack escolhida.

Sugestão de stack:

- Backend: Laravel
- Frontend: Blade/Livewire ou React/Next, dependendo da arquitetura escolhida
- Banco de dados: MySQL
- Tempo real: WebSocket
- Autenticação: sistema com roles/perfis
- Estilo visual: Tailwind CSS

---

## 4.2 Configurar o banco de dados

Configurar conexão com MySQL e preparar as primeiras migrations.

---

## 4.3 Criar tabelas principais

As primeiras tabelas devem ser:

- `schools`
- `users`
- `classrooms`
- `classroom_students`
- `classroom_teachers`

---

# 5. Fase 2 — Autenticação e permissões

Antes de qualquer funcionalidade, o sistema precisa saber quem está logado e qual é o tipo de usuário.

## 5.1 Roles do sistema

O campo `role` do usuário pode ter os seguintes valores:

```txt
manager
teacher
student
admin
```

O `admin` é opcional, caso exista uma visão geral para o dono do sistema.

---

## 5.2 Redirecionamento após login

Após o login, o sistema deve redirecionar o usuário de acordo com seu perfil.

```txt
Gestor  -> Dashboard do gestor
Professor -> Dashboard do professor
Aluno -> Área do aluno
```

---

## 5.3 Controle de permissões

Cada usuário deve acessar apenas as áreas permitidas.

Exemplos:

- Aluno não pode acessar área do professor.
- Professor não pode acessar área do gestor.
- Gestor só pode ver dados da sua própria escola.
- Professor só pode ver turmas vinculadas a ele.
- Aluno só pode ver dados da própria turma e do próprio perfil.

---

# 6. Fase 3 — Gestão escolar básica

Esta fase deve ser feita antes da gamificação.

## 6.1 Tabela `schools`

Guarda as escolas cadastradas.

### Campos sugeridos

```txt
id
name
created_at
updated_at
```

Campos opcionais:

```txt
cnpj
city
state
logo
status
```

---

## 6.2 Tabela `users`

Guarda gestores, professores e alunos.

### Campos sugeridos

```txt
id
school_id
name
email
username
password
role
status
created_at
updated_at
```

### Observações

- Para gestor e professor, o login pode ser feito por e-mail.
- Para aluno, o login pode ser feito por `username`.
- A senha deve sempre ser salva criptografada.
- Evitar salvar senha em texto puro.

---

## 6.3 Tabela `classrooms`

Guarda as turmas da escola.

### Campos sugeridos

```txt
id
school_id
name
year
shift
status
created_at
updated_at
```

### Exemplos de turmas

```txt
6º Ano A
7º Ano B
8º Ano C
1º Ensino Médio A
2º Ensino Médio B
```

---

## 6.4 Tabela `classroom_students`

Vincula alunos às turmas.

### Campos sugeridos

```txt
id
classroom_id
student_id
created_at
updated_at
```

### Regras

- Um aluno deve pertencer a uma escola.
- Um aluno deve estar vinculado a pelo menos uma turma.
- Inicialmente, considerar que o aluno pertence a apenas uma turma ativa.

---

## 6.5 Tabela `classroom_teachers`

Vincula professores às turmas.

### Campos sugeridos

```txt
id
classroom_id
teacher_id
created_at
updated_at
```

### Regras

- Um professor pode ter várias turmas.
- Uma turma pode ter vários professores.
- O professor só pode acessar turmas vinculadas a ele.

---

# 7. Fase 4 — Área do gestor

Depois da base de usuários e turmas, criar a visão do gestor.

## 7.1 Funcionalidades iniciais do gestor

Implementar nesta ordem:

1. Dashboard simples.
2. CRUD de turmas.
3. CRUD de professores.
4. CRUD de alunos.
5. Vincular alunos às turmas.
6. Vincular professores às turmas.
7. Consultar logins gerados dos alunos.

---

## 7.2 Dashboard inicial do gestor

Mostrar dados simples:

```txt
Total de turmas
Total de professores
Total de alunos
Total de alunos ativos
Total de professores ativos
```

Depois, o dashboard será evoluído com gráficos.

---

## 7.3 CRUD de turmas

O gestor poderá:

- Criar turma.
- Editar turma.
- Excluir/desativar turma.
- Listar turmas.
- Ver alunos da turma.
- Ver professores da turma.

---

## 7.4 CRUD de professores

O gestor poderá:

- Criar professor.
- Editar professor.
- Excluir/desativar professor.
- Listar professores.
- Vincular professor a uma ou mais turmas.

---

## 7.5 CRUD de alunos

O gestor poderá:

- Criar aluno.
- Editar aluno.
- Excluir/desativar aluno.
- Listar alunos.
- Vincular aluno a uma turma.
- Gerar login do aluno.
- Consultar login inicial do aluno, se a regra permitir.

---

# 8. Fase 5 — Login gerado para alunos

O login de acesso do aluno será gerado pelo professor ou gestor.

## 8.1 Regra principal

Quando um aluno for criado, o sistema deve gerar:

```txt
username
senha temporária
```

Exemplo:

```txt
Usuário: joao.silva123
Senha temporária: 839201
```

---

## 8.2 Segurança

A senha real deve ser salva criptografada no banco.

Se for necessário mostrar a senha temporária para o gestor/professor, usar uma estratégia segura.

Sugestão:

- Salvar a senha criptografada em `users.password`.
- Salvar a senha inicial em uma tabela separada apenas enquanto o aluno ainda não alterou a senha.
- Marcar o usuário com `must_change_password = true`.
- Após o aluno trocar a senha, remover ou invalidar a senha temporária.

---

## 8.3 Campos úteis no usuário aluno

```txt
username
password
must_change_password
status
```

---

## 8.4 Tabela opcional `student_credentials`

Guarda credenciais iniciais geradas para consulta temporária.

```txt
id
student_id
generated_by_user_id
initial_username
initial_password
is_used
created_at
updated_at
```

Observação: em produção, evitar guardar senha em texto puro. Caso seja necessário por regra de negócio, limitar o acesso e remover após primeira troca.

---

# 9. Fase 6 — Banco de disciplinas e questões

Depois de turmas, professores e alunos prontos, criar o banco de questões.

## 9.1 Tabela `subjects`

Guarda as disciplinas.

### Campos sugeridos

```txt
id
school_id
name
status
created_at
updated_at
```

### Exemplos

```txt
Matemática
Português
História
Geografia
Ciências
Inglês
```

---

## 9.2 Tabela `topics`

Guarda matérias/conteúdos dentro de uma disciplina.

### Campos sugeridos

```txt
id
subject_id
name
status
created_at
updated_at
```

### Exemplos

Para Matemática:

```txt
Frações
Porcentagem
Equações
Geometria
```

Para Português:

```txt
Interpretação de texto
Classes gramaticais
Pontuação
Redação
```

---

## 9.3 Tabela `questions`

Guarda as questões criadas pelo professor.

### Campos sugeridos

```txt
id
school_id
teacher_id
subject_id
topic_id
statement
difficulty
points
status
created_at
updated_at
```

### Difficulty

```txt
easy
medium
hard
```

---

## 9.4 Tabela `question_options`

Guarda as alternativas de cada questão.

### Campos sugeridos

```txt
id
question_id
text
is_correct
created_at
updated_at
```

---

## 9.5 Regras do banco de questões

- Toda questão deve pertencer a uma escola.
- Toda questão deve ter um professor criador.
- Toda questão deve pertencer a uma disciplina.
- Uma questão pode ou não pertencer a um tópico específico.
- Toda questão deve ter pelo menos duas alternativas.
- Toda questão deve ter uma alternativa correta.
- O professor pode editar apenas suas questões, a menos que exista permissão especial.
- O gestor pode visualizar questões da escola.

---

# 10. Fase 7 — Área do professor

Depois de criar disciplinas e questões, implementar a área do professor.

## 10.1 Funcionalidades do professor

Implementar nesta ordem:

1. Dashboard simples do professor.
2. Listar turmas do professor.
3. Visualizar alunos de uma turma.
4. Criar questão.
5. Editar questão.
6. Excluir/desativar questão.
7. Listar banco de questões.
8. Filtrar questões por disciplina, matéria e dificuldade.
9. Visualizar desempenho básico dos alunos.

---

## 10.2 Dashboard inicial do professor

Mostrar:

```txt
Minhas turmas
Total de alunos
Total de questões criadas
Últimas questões criadas
```

---

# 11. Fase 8 — Área básica do aluno

Nesta fase, o aluno começa a usar o sistema.

## 11.1 Funcionalidades iniciais do aluno

Implementar nesta ordem:

1. Dashboard do aluno.
2. Ver pontos.
3. Ver energia.
4. Ver nível.
5. Escolher disciplina.
6. Responder questões.
7. Receber feedback de acerto/erro.
8. Salvar resposta.
9. Atualizar pontos.
10. Atualizar energia.
11. Ver histórico de respostas.

---

## 11.2 Dashboard inicial do aluno

Mostrar:

```txt
Nome do aluno
Turma
Pontos atuais
Energia atual
Nível atual
Personagem equipado
Ranking na turma
```

---

# 12. Fase 9 — Sistema de energia

A energia limita certas ações do aluno.

## 12.1 Regra inicial de energia

Sugestão:

```txt
Energia máxima: 10
Acertou questão comum: não perde energia
Errou questão comum: perde 1 energia
Partida amistosa: não perde energia
Partida valendo pontos: perde energia
```

---

## 12.2 Regeneração de energia

Pode ser implementada depois.

Sugestões:

```txt
Recupera 1 energia a cada X minutos
Recupera tudo no início do dia
Recupera energia ao cumprir missão
```

---

## 12.3 Campos úteis para o aluno

Pode ficar em uma tabela separada de perfil gamificado.

### Tabela `student_profiles`

```txt
id
student_id
points
xp
level
energy
max_energy
created_at
updated_at
```

---

# 13. Fase 10 — Sistema de pontos e XP

## 13.1 Regras de pontos

Sugestão inicial:

```txt
Acerto: +10 pontos
Erro: +2 pontos
Questão fácil: pontos normais
Questão média: bônus pequeno
Questão difícil: bônus maior
```

---

## 13.2 Regras de XP

Os pontos podem ser usados na loja, enquanto XP serve para subir de nível.

Exemplo:

```txt
Acerto: +10 XP
Erro: +2 XP
Vitória em desafio amistoso: +5 XP
Vitória em desafio valendo pontos: +20 XP
Participação em sessão ao vivo: +10 XP
```

---

## 13.3 Diferença entre pontos e XP

```txt
Pontos: moeda do sistema, usados na loja.
XP: experiência para subir de nível.
```

---

# 14. Fase 11 — Responder questões

## 14.1 Tabela `student_answers`

Guarda respostas dos alunos.

### Campos sugeridos

```txt
id
student_id
question_id
selected_option_id
is_correct
points_earned
xp_earned
energy_spent
answered_at
created_at
updated_at
```

---

## 14.2 Fluxo de resposta

1. Aluno escolhe uma disciplina.
2. Sistema sorteia ou lista questões.
3. Aluno responde.
4. Sistema verifica se está correta.
5. Sistema calcula pontos.
6. Sistema calcula XP.
7. Sistema atualiza energia, se necessário.
8. Sistema salva a resposta.
9. Sistema mostra feedback.

---

# 15. Fase 12 — Rankings

Depois que pontos existem, implementar rankings.

## 15.1 Tipos de ranking

Implementar nesta ordem:

1. Ranking da turma.
2. Ranking da escola.
3. Ranking semanal.
4. Ranking mensal.
5. Ranking por disciplina.
6. Ranking competitivo de desafios.

---

## 15.2 Ranking da turma

Mostra alunos da mesma turma ordenados por pontuação.

---

## 15.3 Ranking da escola

Mostra alunos da escola inteira ordenados por pontuação.

---

## 15.4 Rankings alternativos

Para deixar o sistema mais justo, também criar rankings de:

```txt
Aluno que mais evoluiu
Aluno mais participativo
Aluno com mais questões respondidas
Aluno com maior sequência de estudos
Aluno com maior número de acertos na semana
```

---

# 16. Fase 13 — Desafios entre alunos

Um aluno poderá desafiar outro aluno da mesma turma.

Esse módulo deve ser implementado depois de:

- Login do aluno.
- Turmas.
- Banco de questões.
- Sistema de pontos.
- Sistema de energia.
- Histórico de respostas.

---

## 16.1 Tipos de partida

Existem dois tipos de desafio:

```txt
Partida amistosa
Partida valendo pontos
```

---

## 16.2 Partida amistosa

A partida amistosa serve para treino e diversão.

### Regras

- Não consome energia.
- Não tira pontos.
- Não deve afetar ranking competitivo.
- Pode dar pouco XP.
- Pode contar para missões e conquistas.
- Pode aparecer no histórico como amistosa.

### Exemplo de recompensa

```txt
Participar: +2 XP
Vencer: +5 XP
```

---

## 16.3 Partida valendo pontos

A partida valendo pontos é competitiva.

### Regras

- Consome energia dos dois alunos.
- Pode valer pontos.
- Afeta ranking competitivo.
- Pode gerar recompensa maior para o vencedor.
- Pode contar para conquistas competitivas.
- Deve ter limite diário para evitar abuso.

### Exemplo de recompensa

```txt
Custo: 1 energia por aluno
Vencedor: +30 pontos
Perdedor: +5 pontos
Empate: +10 pontos para cada
```

---

## 16.4 Fluxo do desafio

1. Aluno entra na área de desafios.
2. Sistema lista colegas da mesma turma.
3. Aluno escolhe um colega.
4. Aluno escolhe disciplina.
5. Aluno escolhe quantidade de questões.
6. Aluno escolhe tipo de partida: amistosa ou valendo pontos.
7. Sistema envia convite ao colega.
8. Colega pode aceitar, recusar ou ignorar.
9. Se aceitar, sistema inicia a partida.
10. Sistema sorteia questões.
11. Os dois alunos respondem.
12. Sistema calcula acertos.
13. Em caso de empate, verifica tempo de resposta.
14. Sistema define vencedor.
15. Sistema aplica pontos/XP/energia.
16. Sistema salva histórico.
17. Sistema atualiza ranking, se necessário.

---

## 16.5 Critérios para vencer

Ordem de desempate:

1. Quem acertou mais questões.
2. Se empatar, quem respondeu mais rápido.
3. Se continuar empatado, declarar empate.

---

## 16.6 Proteções contra abuso

Implementar regras para evitar farm de pontos.

Sugestões:

- Limite de partidas valendo pontos por dia.
- Limite de partidas contra o mesmo colega.
- Amistosas não devem gerar pontos infinitos.
- Pontuação alta apenas em partidas competitivas.
- Registrar histórico de todas as partidas.
- Permitir que professor ou gestor visualize partidas suspeitas.
- Exigir energia para partidas valendo pontos.

---

## 16.7 Tabela `student_challenges`

Guarda o desafio.

### Campos sugeridos

```txt
id
challenger_id
opponent_id
classroom_id
subject_id
type
status
questions_count
energy_cost
winner_id
started_at
finished_at
created_at
updated_at
```

### Valores de `type`

```txt
friendly
ranked
```

### Valores de `status`

```txt
pending
accepted
refused
in_progress
finished
canceled
expired
```

---

## 16.8 Tabela `student_challenge_questions`

Guarda as questões sorteadas para o desafio.

```txt
id
challenge_id
question_id
order
created_at
updated_at
```

---

## 16.9 Tabela `student_challenge_answers`

Guarda as respostas dos alunos no desafio.

```txt
id
challenge_id
student_id
question_id
selected_option_id
is_correct
response_time
points_earned
xp_earned
created_at
updated_at
```

---

# 17. Fase 14 — Loja de personagens/cosméticos

Depois que pontos funcionam, implementar a loja.

## 17.1 Ideia da loja

Os alunos poderão gastar pontos comprando personagens/cosméticos.

---

## 17.2 Tiers dos personagens

Os personagens terão raridade:

```txt
common
rare
epic
legendary
```

Em português:

```txt
Comum
Raro
Épico
Lendário
```

---

## 17.3 Tabela `cosmetics`

Guarda personagens/cosméticos.

```txt
id
name
description
tier
price
image
status
created_at
updated_at
```

---

## 17.4 Tabela `student_cosmetics`

Guarda os cosméticos comprados pelo aluno.

```txt
id
student_id
cosmetic_id
is_equipped
purchased_at
created_at
updated_at
```

---

## 17.5 Regras da loja

- Aluno só pode comprar se tiver pontos suficientes.
- Ao comprar, os pontos são descontados.
- Aluno não pode comprar o mesmo personagem duas vezes.
- Aluno pode equipar apenas um personagem por vez.
- Personagem equipado aparece no perfil do aluno.

---

# 18. Fase 15 — Missões e conquistas

Depois da gamificação básica, implementar missões e conquistas.

## 18.1 Missões

Missões são objetivos temporários.

### Exemplos

```txt
Responder 5 questões hoje
Acertar 3 questões de Matemática
Participar de uma sessão ao vivo
Vencer um desafio amistoso
Vencer uma partida valendo pontos
Ficar 3 dias seguidos estudando
Comprar o primeiro personagem
```

---

## 18.2 Tabela `missions`

```txt
id
title
description
type
goal
reward_points
reward_xp
status
created_at
updated_at
```

---

## 18.3 Tabela `student_missions`

```txt
id
student_id
mission_id
progress
completed
completed_at
created_at
updated_at
```

---

## 18.4 Conquistas

Conquistas são marcos permanentes.

### Exemplos

```txt
Primeira questão respondida
10 questões respondidas
100 questões respondidas
5 acertos seguidos
Primeira vitória em desafio
Primeiro personagem comprado
Primeiro lugar da turma
Participou da primeira sessão ao vivo
```

---

## 18.5 Tabela `achievements`

```txt
id
title
description
condition_type
condition_value
reward_points
reward_xp
icon
status
created_at
updated_at
```

---

## 18.6 Tabela `student_achievements`

```txt
id
student_id
achievement_id
unlocked_at
created_at
updated_at
```

---

# 19. Fase 16 — Sessão ao vivo com WebSocket

Essa é a funcionalidade principal, mas não deve ser implementada primeiro.

Ela depende de:

- Professores.
- Turmas.
- Alunos.
- Banco de questões.
- Sistema de respostas.
- Pontuação.
- Energia.
- Ranking.

---

## 19.1 Objetivo

Permitir que o professor crie uma sessão ao vivo para uma turma e envie questões em tempo real.

---

## 19.2 Fluxo da sessão ao vivo

1. Professor cria uma sessão ao vivo.
2. Professor escolhe uma turma.
3. Professor escolhe questões do banco.
4. Alunos entram na sessão.
5. Professor inicia a sessão.
6. Professor envia a primeira questão.
7. A questão aparece em tempo real para os alunos.
8. Alunos respondem.
9. Sistema salva respostas.
10. Sistema calcula pontuação.
11. Professor vê resumo da questão.
12. Professor envia a próxima questão.
13. Ao final, sistema mostra ranking da sessão.

---

## 19.3 Tabela `live_sessions`

Guarda a sessão ao vivo.

```txt
id
teacher_id
classroom_id
title
status
started_at
finished_at
created_at
updated_at
```

### Valores de `status`

```txt
scheduled
waiting
in_progress
finished
canceled
```

---

## 19.4 Tabela `live_session_questions`

Guarda questões da sessão.

```txt
id
live_session_id
question_id
order
is_current
started_at
finished_at
created_at
updated_at
```

---

## 19.5 Tabela `live_session_participants`

Guarda alunos participantes.

```txt
id
live_session_id
student_id
joined_at
left_at
created_at
updated_at
```

---

## 19.6 Tabela `live_session_answers`

Guarda respostas dos alunos durante a sessão.

```txt
id
live_session_id
question_id
student_id
selected_option_id
is_correct
response_time
points_earned
xp_earned
answered_at
created_at
updated_at
```

---

## 19.7 Eventos WebSocket sugeridos

### Eventos do professor

```txt
session.created
session.started
question.sent
question.closed
session.finished
```

### Eventos do aluno

```txt
student.joined
student.answered
student.left
```

### Eventos para atualização em tempo real

```txt
ranking.updated
question.results.updated
session.status.updated
```

---

# 20. Fase 17 — Dashboards avançados

Depois que o sistema tiver respostas, pontos, desafios e sessões ao vivo, criar dashboards completos.

---

## 20.1 Dashboard avançado do professor

Mostrar por turma:

```txt
Média de acertos
Alunos com maior dificuldade
Questões mais erradas
Disciplinas com pior desempenho
Participação nas sessões ao vivo
Ranking da turma
Evolução dos alunos
Histórico de desafios
```

---

## 20.2 Dashboard avançado do gestor

Mostrar por escola:

```txt
Ranking geral da escola
Ranking por turma
Turmas com melhor desempenho
Turmas com maior dificuldade
Professores mais ativos
Participação geral dos alunos
Alunos com maior pontuação
Alunos com maior evolução
Comparação entre turmas
```

---

# 21. Fase 18 — Acessibilidade

A acessibilidade deve ser considerada desde o começo, mas pode ser evoluída em fases.

## 21.1 Acessibilidade básica

Implementar desde o início:

```txt
Bom contraste
Fonte legível
Botões grandes
Navegação simples
Responsividade
Textos claros
Feedback visual e textual
Não depender apenas de cor para indicar erro/acerto
Textos alternativos em imagens
Labels corretos em formulários
```

---

## 21.2 Acessibilidade adaptativa para PCD/PwD

Ideia futura: permitir configurações adaptativas por aluno.

### Recursos possíveis

```txt
Modo alto contraste
Aumentar fonte
Redução de animações
Tempo extra em questões
Interface simplificada
Leitor de tela compatível
Navegação por teclado
Sinais visuais e textuais juntos
```

---

## 21.3 Tabela opcional `student_accessibility_settings`

```txt
id
student_id
high_contrast
large_text
reduced_motion
extra_time
simplified_interface
created_at
updated_at
```

---

# 22. Fase 19 — Visual do sistema

O visual deve seguir um modelo limpo, institucional e acessível, parecido com sistemas de governo/prefeitura.

## 22.1 Direção visual

```txt
Layout limpo
Menu lateral
Cards padronizados
Tabelas administrativas
Cores institucionais
Boa hierarquia visual
Componentes reutilizáveis
Padrão visual consistente
Acessibilidade
```

---

## 22.2 Componentes importantes

Criar componentes reutilizáveis:

```txt
Button
Input
Select
Textarea
Modal
Card
Table
Badge
Alert
Tabs
Dropdown
Sidebar
Navbar
DashboardCard
RankingCard
QuestionCard
StudentCard
```

---

## 22.3 Regras de componentização

- Sempre reutilizar componentes globais quando existirem.
- Se precisar de um componente que ainda não existe, criar seguindo o padrão do sistema.
- Não criar componente desnecessário para coisas muito simples.
- Criar componentes quando houver repetição, regra visual ou comportamento reutilizável.
- Manter nomes claros e consistentes.

---

# 23. Ordem detalhada de implementação

Esta é a ordem que o Claude deve seguir.

## Etapa 1 — Configuração inicial

1. Criar projeto.
2. Configurar banco de dados.
3. Configurar autenticação.
4. Configurar estrutura de layouts.
5. Criar middleware de autenticação.
6. Criar middleware de roles/permissões.

---

## Etapa 2 — Base de dados escolar

7. Criar migration de `schools`.
8. Criar migration de `users`.
9. Criar migration de `classrooms`.
10. Criar migration de `classroom_students`.
11. Criar migration de `classroom_teachers`.
12. Criar models e relacionamentos.
13. Criar seeders básicos para teste.

---

## Etapa 3 — Login e dashboards iniciais

14. Criar login.
15. Redirecionar por perfil.
16. Criar dashboard do gestor.
17. Criar dashboard do professor.
18. Criar dashboard do aluno.

---

## Etapa 4 — Gestão do gestor

19. CRUD de turmas.
20. CRUD de professores.
21. CRUD de alunos.
22. Vincular alunos às turmas.
23. Vincular professores às turmas.
24. Gerar login de aluno.
25. Tela para consultar logins gerados.

---

## Etapa 5 — Banco pedagógico

26. Criar migration de `subjects`.
27. Criar migration de `topics`.
28. Criar migration de `questions`.
29. Criar migration de `question_options`.
30. Criar CRUD de disciplinas.
31. Criar CRUD de matérias/tópicos.
32. Criar CRUD de questões.
33. Criar filtros por disciplina, tópico e dificuldade.

---

## Etapa 6 — Área do professor

34. Listar turmas do professor.
35. Listar alunos por turma.
36. Listar questões criadas.
37. Criar dashboard básico do professor.
38. Exibir desempenho inicial dos alunos.

---

## Etapa 7 — Área do aluno

39. Criar perfil gamificado do aluno.
40. Criar tabela `student_profiles`.
41. Exibir pontos, XP, nível e energia.
42. Permitir escolher disciplina.
43. Permitir responder questões.
44. Salvar respostas em `student_answers`.
45. Calcular acerto/erro.
46. Aplicar pontos.
47. Aplicar XP.
48. Aplicar gasto de energia.
49. Exibir histórico de respostas.

---

## Etapa 8 — Ranking

50. Criar ranking da turma.
51. Criar ranking da escola.
52. Criar ranking semanal.
53. Criar ranking mensal.
54. Criar ranking por disciplina.
55. Criar ranking competitivo de desafios.

---

## Etapa 9 — Desafios entre alunos

56. Criar área de desafios.
57. Listar colegas da mesma turma.
58. Criar convite de desafio.
59. Criar tabela `student_challenges`.
60. Criar tabela `student_challenge_questions`.
61. Criar tabela `student_challenge_answers`.
62. Permitir aceitar desafio.
63. Permitir recusar desafio.
64. Criar partida amistosa.
65. Criar partida valendo pontos.
66. Sortear questões.
67. Registrar respostas dos dois alunos.
68. Calcular vencedor.
69. Aplicar regra de empate por tempo.
70. Aplicar energia em partida valendo pontos.
71. Não aplicar energia em partida amistosa.
72. Aplicar pontos e XP.
73. Salvar histórico da partida.
74. Atualizar ranking competitivo.
75. Criar limites contra abuso.

---

## Etapa 10 — Loja

76. Criar tabela `cosmetics`.
77. Criar tabela `student_cosmetics`.
78. Criar tela da loja.
79. Listar personagens.
80. Separar personagens por tier.
81. Comprar personagem com pontos.
82. Descontar pontos.
83. Impedir compra duplicada.
84. Criar inventário do aluno.
85. Permitir equipar personagem.
86. Exibir personagem equipado no perfil.

---

## Etapa 11 — Missões e conquistas

87. Criar tabela `missions`.
88. Criar tabela `student_missions`.
89. Criar tabela `achievements`.
90. Criar tabela `student_achievements`.
91. Criar missões diárias.
92. Criar missões semanais.
93. Criar conquistas por respostas.
94. Criar conquistas por desafios.
95. Criar conquistas por loja.
96. Aplicar recompensas de pontos e XP.

---

## Etapa 12 — Sessão ao vivo com WebSocket

97. Configurar WebSocket.
98. Criar tabela `live_sessions`.
99. Criar tabela `live_session_questions`.
100. Criar tabela `live_session_participants`.
101. Criar tabela `live_session_answers`.
102. Professor cria sessão ao vivo.
103. Professor seleciona turma.
104. Professor seleciona questões.
105. Alunos entram na sessão.
106. Professor inicia sessão.
107. Professor envia questão atual.
108. Questão aparece para alunos em tempo real.
109. Alunos respondem.
110. Sistema salva respostas.
111. Sistema calcula pontos.
112. Professor vê resumo da questão.
113. Professor avança para próxima questão.
114. Sistema atualiza ranking da sessão.
115. Professor finaliza sessão.
116. Sistema salva resultado final.

---

## Etapa 13 — Dashboards avançados

117. Criar dashboard avançado do professor.
118. Criar gráficos de desempenho por turma.
119. Criar relatório de questões mais erradas.
120. Criar relatório de alunos com dificuldade.
121. Criar dashboard avançado do gestor.
122. Criar ranking geral da escola.
123. Criar análise comparativa entre turmas.
124. Criar relatório de participação dos alunos.
125. Criar relatório de professores mais ativos.

---

## Etapa 14 — Acessibilidade adaptativa

126. Criar configurações de acessibilidade por aluno.
127. Criar modo alto contraste.
128. Criar opção de aumentar fonte.
129. Criar redução de animações.
130. Criar tempo extra em questões.
131. Criar interface simplificada.
132. Garantir navegação por teclado.
133. Garantir compatibilidade com leitor de tela.

---

## Etapa 15 — Refinamento visual

134. Criar identidade visual final.
135. Padronizar cards.
136. Padronizar tabelas.
137. Padronizar botões.
138. Padronizar formulários.
139. Melhorar responsividade.
140. Melhorar telas mobile.
141. Revisar UX dos fluxos principais.
142. Revisar acessibilidade.
143. Revisar permissões.
144. Revisar segurança.
145. Preparar versão final do MVP.

---

# 24. MVP recomendado

O MVP deve ser menor que o sistema completo.

## MVP inicial

Implementar primeiro:

1. Login com roles.
2. Gestor cadastra turmas, professores e alunos.
3. Gestor/professor gera login do aluno.
4. Professor cria questões.
5. Aluno responde questões.
6. Sistema calcula pontos.
7. Sistema controla energia.
8. Ranking da turma.
9. Desafios entre alunos.
10. Loja simples de personagens.
11. Dashboard básico do professor.
12. Dashboard básico do gestor.

---

## Não implementar no primeiro MVP

Deixar para depois:

```txt
Sessão ao vivo com WebSocket
Missões avançadas
Conquistas avançadas
Dashboard avançado
Acessibilidade adaptativa completa
Ranking mensal/semanal complexo
Sistema antifraude completo
```

---

# 25. Regras importantes para o Claude seguir

## 25.1 Não começar pelo WebSocket

A sessão ao vivo é importante, mas depende de várias partes do sistema. Não implementar antes de ter:

```txt
Usuários
Turmas
Alunos
Professores
Questões
Respostas
Pontos
Energia
```

---

## 25.2 Não começar pela loja

A loja depende dos pontos. Primeiro implementar pontos, depois loja.

---

## 25.3 Não começar por dashboard avançado

Dashboard avançado depende de dados reais. Primeiro criar respostas, pontos, desafios e sessões.

---

## 25.4 Priorizar base sólida

A ordem correta é:

```txt
Base escolar -> Questões -> Respostas -> Pontos/Energia -> Ranking -> Desafios -> Loja -> WebSocket -> Dashboards avançados
```

---

## 25.5 Sempre respeitar permissões

O sistema deve garantir:

```txt
Gestor vê apenas sua escola.
Professor vê apenas suas turmas.
Aluno vê apenas seus próprios dados e dados permitidos da turma.
```

---

## 25.6 Componentização

Sempre priorizar componentes reutilizáveis.

Exemplos:

```txt
Button
Input
Select
Textarea
Card
Table
Modal
Badge
Alert
DashboardCard
RankingCard
QuestionCard
StudentCard
```

Não criar componentes desnecessários para elementos muito simples.

---

## 25.7 Segurança

- Senhas sempre criptografadas.
- Nunca salvar senha real em texto puro.
- Validar permissões no backend.
- Não confiar apenas no frontend.
- Validar se aluno pertence à turma antes de permitir desafio.
- Validar se professor pertence à turma antes de permitir sessão.
- Validar se gestor pertence à escola antes de alterar dados.
- Impedir abuso de partidas valendo pontos.

---

# 26. Resumo final da ordem correta

```txt
1. Projeto e banco de dados
2. Login e roles
3. Escola, gestor, professor e aluno
4. Turmas e vínculos
5. Geração de login dos alunos
6. Disciplinas e matérias
7. Banco de questões
8. Área do professor
9. Área do aluno
10. Responder questões
11. Pontos, XP e energia
12. Ranking
13. Desafios entre alunos
14. Loja de personagens
15. Missões e conquistas
16. Sessão ao vivo com WebSocket
17. Dashboards avançados
18. Acessibilidade adaptativa
19. Refinamento visual
20. Revisão final do MVP
```

---

# 27. Primeira tarefa prática

A primeira tarefa prática do projeto deve ser:

```txt
Criar as migrations, models e relacionamentos básicos para:
schools
users
classrooms
classroom_students
classroom_teachers
```

Depois disso:

```txt
Criar autenticação com roles:
manager
teacher
student
```

Depois disso:

```txt
Criar dashboards simples separados por tipo de usuário.
```

Somente após isso começar os CRUDs do gestor.

---

# 28. Instrução final para o Claude

Siga a ordem de implementação definida neste documento.

Não antecipe módulos avançados antes da base estar pronta.

Sempre que implementar uma fase, garanta:

```txt
Migrations criadas
Models criados
Relacionamentos definidos
Validações aplicadas
Permissões respeitadas
Telas básicas funcionando
Testes manuais possíveis
Código organizado e componentizado
```

A prioridade é construir um MVP funcional, seguro e bem estruturado, para depois evoluir a gamificação, WebSocket, dashboards e acessibilidade.
