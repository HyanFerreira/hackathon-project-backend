<?php

namespace Database\Seeders;

use App\Models\Habilidade;
use App\Models\Questao;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestaoSeeder extends Seeder
{
    public function run(): void
    {
        $professor = User::query()->where('cpf', '39053344705')->first(); // Carla
        $habilidade = Habilidade::query()->where('codigo', 'EF06MA01')->first();

        if (! $professor || ! $professor->escola_id || ! $habilidade) {
            return;
        }

        $existe = Questao::query()
            ->where('professor_id', $professor->id)
            ->where('enunciado', 'like', 'Qual é o maior número%')
            ->exists();

        if ($existe) {
            return;
        }

        $questao = Questao::query()->create([
            'escola_id' => $professor->escola_id,
            'professor_id' => $professor->id,
            'enunciado' => 'Qual é o maior número entre as opções abaixo?',
            'dificuldade' => 'facil',
            'pontos' => 10,
            'status' => 'ativa',
        ]);

        $questao->alternativas()->createMany([
            ['texto' => '1.203', 'correta' => false],
            ['texto' => '1.230', 'correta' => true],
            ['texto' => '1.032', 'correta' => false],
            ['texto' => '1.023', 'correta' => false],
        ]);

        $questao->habilidades()->sync([$habilidade->id]);
    }
}
