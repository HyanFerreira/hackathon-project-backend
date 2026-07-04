<?php

namespace Database\Seeders;

use App\Models\Escola;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Database\Seeder;

class TurmaSeeder extends Seeder
{
    public function run(): void
    {
        $central = Escola::query()->where('nome', 'Escola Municipal Central')->first();

        if (! $central) {
            return;
        }

        foreach (['6º Ano A', '7º Ano B'] as $nome) {
            Turma::query()->updateOrCreate(
                ['escola_id' => $central->id, 'nome' => $nome],
                ['ano' => '2026', 'turno' => 'manha', 'status' => 'ativa'],
            );
        }

        // Vincula a professora Carla à turma 6º Ano A como exemplo.
        $turma = Turma::query()->where('escola_id', $central->id)->where('nome', '6º Ano A')->first();
        $professor = User::query()->where('cpf', '39053344705')->first();

        if ($turma && $professor) {
            $turma->professores()->syncWithoutDetaching([$professor->id]);
        }
    }
}
