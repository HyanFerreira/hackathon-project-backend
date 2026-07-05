# Paideia — Backend (API)

API do **Paideia**, um sistema educacional gamificado (alunos, professores e gestores escolares) construído em **Laravel 12**. Inclui banco de questões da BNCC, responder questões com pontos/XP/energia, ranking, loja de personagens, conquistas, missões, **desafios ao vivo** e **sessão ao vivo do professor** (WebSocket), dashboards de desempenho e mais.

> O **frontend** é um projeto separado (Next.js) — veja a seção [Frontend](#frontend).

---

## Stack

- **PHP 8.2+** / **Laravel 12**
- **MySQL 8+**
- **Laravel Sanctum** (autenticação por token)
- **Spatie Laravel Permission** (papéis/permissões: admin, gestor, professor)
- **Laravel Reverb** (WebSocket para desafios e sessão ao vivo)
- **simplesoftwareio/simple-qrcode** + **barryvdh/laravel-dompdf** (cartões de acesso em PDF)

---

## Requisitos

- PHP **8.2+** com as extensões: `pdo_mysql`, `mbstring`, `dom`, `xml`, `curl`, `openssl`, `bcmath`
- Para os cartões de acesso (QR em PDF): extensão **`imagick`** (recomendada) ou `gd`
- **Composer 2**
- **MySQL 8+** (ou MariaDB equivalente)

Confira as extensões com:

```bash
php -m | grep -iE "pdo_mysql|mbstring|dom|imagick|gd"
```

---

## Instalação

```bash
# 1) Clonar o repositório
git clone https://github.com/HyanFerreira/hackathon-project-backend.git
cd hackathon-project-backend

# 2) Instalar as dependências PHP
composer install

# 3) Criar o arquivo de ambiente e a chave da aplicação
cp .env.example .env
php artisan key:generate
```

### 4) Configurar o banco de dados

Crie o banco no MySQL:

```bash
mysql -u root -p -e "CREATE DATABASE hackathon_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

E ajuste as credenciais no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hackathon_api
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

### 5) Rodar as migrations com os dados iniciais (seed)

```bash
php artisan migrate:fresh --seed
```

Isso cria todas as tabelas e popula: papéis/permissões, disciplinas e habilidades da BNCC, **60 questões**, personagens da loja, conquistas, missões, as escolas municipais, as contas de teste e **dados fake de desempenho** (para os gráficos e o ranking).

---

## Rodar o sistema

O sistema usa **3 processos** (abra um terminal para cada). Só o primeiro é obrigatório; os outros dois são necessários para os recursos em tempo real (desafios e sessão ao vivo).

```bash
# 1) API HTTP  ->  http://127.0.0.1:8000
php artisan serve

# 2) Servidor WebSocket (Reverb)  ->  porta 8080
php artisan reverb:start

# 3) Worker da fila (entrega os eventos de broadcast)
php artisan queue:work
```

> Dica: para os celulares da plateia acessarem numa apresentação, rode o front apontando para o **IP da sua máquina na rede** (ex.: `http://192.168.0.10:3000`), não `127.0.0.1`.

---

## Contas de teste (criadas pelo seed)

Login de **equipe** (gestor/professor/admin) é por **CPF + senha**; o **aluno** entra por **código**.

| Papel | Login | Senha |
|-------|-------|-------|
| Admin | CPF `52998224725` | `password` |
| Gestor | CPF `11144477735` | `password` |
| Professor (Carla) | CPF `39053344705` | `password` |
| Aluno (Davi) | código `ALU001` | — (só código) |

Endpoints de login:

```
POST /api/login/token   { "cpf": "...", "password": "password" }   -> equipe
POST /api/login/aluno   { "codigo": "ALU001" }                      -> aluno
```

O seed de desempenho também cria ~48 alunos fake (com respostas) nas turmas 6º–9º Ano A.

---

## Comandos úteis

```bash
# Recriar tudo do zero (apaga e recria o banco + seed)
php artisan migrate:fresh --seed

# Repopular apenas os dados fake de desempenho (gráficos/ranking)
php artisan db:seed --class=DesempenhoFakeSeeder

# Demonstração: gerar PDF com cartões de acesso (QR + código) para imprimir
php artisan demo:cartoes --url=https://SEU-FRONT
#   -> gera storage/app/demo/cartoes-alunos.pdf (cria 100 alunos numa "Turma Demonstração")

# Zerar o progresso dos alunos de demonstração entre apresentações
php artisan demo:reset --force

# Rodar a suíte de testes (requer a extensão pdo_sqlite)
php artisan test
```

---

## Estrutura da API

- Prefixo: `/api`
- Autenticação: **Bearer token** (Sanctum) no header `Authorization: Bearer <token>`
- Áreas: `/api/admin/*`, `/api/gestor/*`, `/api/professor/*`, `/api/aluno/*` + referência BNCC (`/api/disciplinas`, `/api/habilidades`)
- WebSocket: canais privados `aluno.{id}`, `turma.{id}` e `professor.{id}` (auth em `/broadcasting/auth` via Sanctum)

O detalhamento das fases, regras e endpoints está em `ROADMAP-gamificacao.md`.

---

## Frontend

O frontend é um projeto **Next.js** separado: `hackathon-project-frontend`.

```bash
git clone https://github.com/HyanFerreira/hackathon-project-frontend.git
cd hackathon-project-frontend
npm install
npm run dev   # http://127.0.0.1:3000
```

Aponte a URL da API (e as variáveis do Reverb) no `.env` do front. A API já aceita CORS das portas `3000`/`3001` (veja `CORS_ALLOWED_ORIGINS` no `.env`).

---

## Solução de problemas

- **`could not find driver` ao rodar `php artisan test`**: falta a extensão `pdo_sqlite` (a suíte usa SQLite em memória). Instale `php-sqlite3`.
- **Desafios/sessão ao vivo não atualizam em tempo real**: confirme que `php artisan reverb:start` **e** `php artisan queue:work` estão rodando, e que `BROADCAST_CONNECTION=reverb` no `.env`.
- **Cartões de acesso (QR) falham ao gerar**: instale a extensão `imagick`.
- **Erro de conexão com o banco**: confira que o MySQL está no ar e que o banco `hackathon_api` foi criado.
