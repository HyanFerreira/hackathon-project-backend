<?php

namespace App\Console\Commands;

use App\Models\Turma;
use Database\Seeders\DemoAlunosSeeder;
use Database\Seeders\EscolaSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoReset extends Command
{
    protected $signature = 'demo:reset {--force : Não pedir confirmação}';

    protected $description = 'Zera o progresso dos alunos de demonstração (pontos, respostas, conquistas, missões e personagens).';

    public function handle(): int
    {
        $turma = Turma::query()
            ->where('nome', DemoAlunosSeeder::TURMA)
            ->whereHas('escola', fn ($q) => $q->where('nome', EscolaSeeder::PRINCIPAL))
            ->first();

        if (! $turma) {
            $this->error('Turma de demonstração não encontrada.');

            return self::FAILURE;
        }

        $ids = $turma->alunos()->pluck('alunos.id');

        if ($ids->isEmpty()) {
            $this->info('Nenhum aluno de demonstração para resetar.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Zerar o progresso de {$ids->count()} alunos de demonstração?")) {
            return self::SUCCESS;
        }

        foreach (['respostas_alunos', 'aluno_conquista', 'aluno_missao', 'aluno_personagem', 'perfis_alunos'] as $tabela) {
            DB::table($tabela)->whereIn('aluno_id', $ids)->delete();
        }

        $this->info("Progresso zerado para {$ids->count()} alunos de demonstração.");

        return self::SUCCESS;
    }
}
