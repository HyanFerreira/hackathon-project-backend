<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Escola;
use App\Models\PerfilAluno;
use App\Models\Questao;
use App\Models\RespostaAluno;
use App\Models\Turma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Popula respostas fake (realistas) para os alunos da turma da escola principal,
 * dando conteúdo aos gráficos de desempenho do professor e ao ranking.
 * Standalone: rode com `php artisan db:seed --class=DesempenhoFakeSeeder`.
 */
class DesempenhoFakeSeeder extends Seeder
{
    /** Turmas populadas e o modificador de desempenho de cada uma (comparação entre turmas). */
    private const TURMAS = [
        '6º Ano A' => 0.0,
        '7º Ano A' => 0.08,
        '8º Ano A' => -0.06,
        '9º Ano A' => 0.13,
    ];

    private const QTD_ALUNOS = 12;

    private const ALFABETO = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /** Modificador de dificuldade por disciplina (Matemática mais difícil para a turma). */
    private const MOD_DISCIPLINA = ['MA' => -0.22, 'CI' => -0.05, 'HI' => 0.05, 'GE' => 0.05, 'LP' => 0.0];

    private const MOD_DIFICULDADE = ['facil' => 0.10, 'media' => 0.0, 'dificil' => -0.15];

    private const BONUS = ['facil' => 0, 'media' => 2, 'dificil' => 5];

    public function run(): void
    {
        $escola = Escola::query()->where('nome', EscolaSeeder::PRINCIPAL)->first();

        if (! $escola) {
            $this->command?->warn('Escola principal não encontrada. Rode o seed base antes.');

            return;
        }

        $questoes = Questao::query()
            ->with(['alternativas', 'habilidades.disciplina'])
            ->where('escola_id', $escola->id)
            ->where('status', 'ativa')
            ->get();

        if ($questoes->isEmpty()) {
            $this->command?->warn('Sem questões na escola. Rode o QuestaoSeeder antes.');

            return;
        }

        $totalAlunos = 0;

        foreach (self::TURMAS as $nome => $modificador) {
            $turma = Turma::query()->where('escola_id', $escola->id)->where('nome', $nome)->first();

            if (! $turma) {
                continue;
            }

            foreach ($this->garantirAlunos($escola, $turma) as $aluno) {
                $this->gerarRespostas($aluno, $questoes, $modificador);
                $totalAlunos++;
            }
        }

        $this->command?->info("Dados fake gerados para {$totalAlunos} alunos em ".count(self::TURMAS).' turmas.');
    }

    /**
     * @return Collection<int, Aluno>
     */
    private function garantirAlunos(Escola $escola, Turma $turma): Collection
    {
        $existentes = $turma->alunos()->count();

        for ($i = $existentes; $i < self::QTD_ALUNOS; $i++) {
            $aluno = Aluno::query()->create([
                'escola_id' => $escola->id,
                'nome' => fake('pt_BR')->firstName().' '.fake('pt_BR')->lastName(),
                'codigo' => $this->codigoUnico(),
            ]);
            $turma->alunos()->attach($aluno->id);
        }

        return $turma->alunos()->get();
    }

    /**
     * @param  Collection<int, Questao>  $questoes
     */
    private function gerarRespostas(Aluno $aluno, $questoes, float $modificadorTurma = 0.0): void
    {
        // Zera dados anteriores deste aluno (idempotência).
        RespostaAluno::query()->where('aluno_id', $aluno->id)->delete();

        $skill = mt_rand(38, 90) / 100 + $modificadorTurma; // aptidão do aluno + nível da turma
        $selecionadas = $questoes->shuffle()->take(mt_rand((int) ($questoes->count() * 0.6), $questoes->count()));

        $pontos = 0;
        $xp = 0;

        foreach ($selecionadas as $questao) {
            $sigla = $questao->habilidades->first()?->disciplina?->sigla ?? '';
            $prob = min(0.97, max(0.05,
                $skill + (self::MOD_DISCIPLINA[$sigla] ?? 0) + (self::MOD_DIFICULDADE[$questao->dificuldade] ?? 0),
            ));

            $acertou = (mt_rand(1, 100) / 100) <= $prob;
            $alternativa = $questao->alternativas->firstWhere('correta', $acertou)
                ?? $questao->alternativas->first();

            if (! $alternativa) {
                continue;
            }

            $bonus = self::BONUS[$questao->dificuldade] ?? 0;
            $pg = $acertou ? $questao->pontos + $bonus : 2;
            $xg = $acertou ? 10 + $bonus : 2;

            RespostaAluno::query()->create([
                'aluno_id' => $aluno->id,
                'questao_id' => $questao->id,
                'alternativa_id' => $alternativa->id,
                'correta' => $acertou,
                'pontos_ganhos' => $pg,
                'xp_ganho' => $xg,
                'energia_gasta' => 0,
                'respondido_em' => now()->subDays(mt_rand(0, 13))->subMinutes(mt_rand(0, 1439)),
            ]);

            $pontos += $pg;
            $xp += $xg;
        }

        PerfilAluno::query()->updateOrCreate(
            ['aluno_id' => $aluno->id],
            [
                'pontos' => $pontos,
                'pontuacao_total' => $pontos,
                'xp' => $xp,
                'nivel' => intdiv($xp, 100) + 1,
            ],
        );
    }

    private function codigoUnico(): string
    {
        do {
            $codigo = '';
            for ($k = 0; $k < 5; $k++) {
                $codigo .= self::ALFABETO[random_int(0, strlen(self::ALFABETO) - 1)];
            }
        } while (Aluno::query()->where('codigo', $codigo)->exists());

        return $codigo;
    }
}
