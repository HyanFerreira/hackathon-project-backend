# Comandos de demonstração

Comandos Artisan para apresentações (ex.: o pitch do hackathon), em `app/Console/Commands`.

## `demo:cartoes`

Gera um PDF com um cartão por aluno de demonstração: QR code + código de acesso.

```bash
php artisan demo:cartoes --url=https://SEU-FRONT --saida=/caminho/cartoes.pdf
```

| Opção | Padrão | Descrição |
|-------|--------|-----------|
| `--url` | `FRONTEND_URL` do `.env` ou `http://127.0.0.1:3000` | URL base do front usada no QR |
| `--saida` | `storage/app/demo/cartoes-alunos.pdf` | Onde salvar o PDF |

- O QR aponta para `{url}/login/estudante?codigo={codigo}`, então o aluno entra só lendo o cartão.
- Precisa que a turma de demonstração (criada pelo `DemoAlunosSeeder`) tenha alunos.
- Depende da extensão `imagick` para gerar os QR codes.

## `demo:reset`

Zera o progresso dos alunos da turma de demonstração: pontos, respostas, conquistas, missões e personagens.

```bash
php artisan demo:reset          # pede confirmação
php artisan demo:reset --force  # sem confirmação
```

Use entre uma apresentação e outra para começar do zero sem recriar o banco.
