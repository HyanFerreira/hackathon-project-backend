<?php

namespace Database\Seeders;

use App\Models\Escola;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Database\Seeder;

class TurmaSeeder extends Seeder
{
    /**
     * Turmas do Ensino Fundamental — Anos Iniciais (Fund. 1).
     *
     * @var list<string>
     */
    private const TURMAS_FUNDAMENTAL_1 = [
        '1º Ano A', '2º Ano A', '3º Ano A', '4º Ano A', '5º Ano A',
    ];

    /**
     * Turmas do Ensino Fundamental — Anos Finais (Fund. 2).
     *
     * @var list<string>
     */
    private const TURMAS_FUNDAMENTAL_2 = [
        '6º Ano A', '7º Ano A', '8º Ano A', '9º Ano A',
    ];

    public function run(): void
    {
        foreach (EscolaSeeder::ESCOLAS as $nomeEscola => $etapas) {
            $escola = Escola::query()->where('nome', $nomeEscola)->first();

            if (! $escola) {
                continue;
            }

            foreach ($this->turmasParaEtapas($etapas) as $nome) {
                Turma::query()->updateOrCreate(
                    ['escola_id' => $escola->id, 'nome' => $nome],
                    ['ano' => '2026', 'turno' => 'manha', 'status' => 'ativa'],
                );
            }
        }

        // Vincula a professora Carla ao 6º Ano A da escola principal, como exemplo.
        $principal = Escola::query()->where('nome', EscolaSeeder::PRINCIPAL)->first();
        $professor = User::query()->where('cpf', '39053344705')->first();

        if ($principal && $professor) {
            $turma = Turma::query()
                ->where('escola_id', $principal->id)
                ->where('nome', '6º Ano A')
                ->first();

            $turma?->professores()->syncWithoutDetaching([$professor->id]);
        }
    }

    /**
     * @param  list<string>  $etapas
     * @return list<string>
     */
    private function turmasParaEtapas(array $etapas): array
    {
        $turmas = [];

        if (in_array(EscolaSeeder::FUNDAMENTAL_1, $etapas, true)) {
            $turmas = array_merge($turmas, self::TURMAS_FUNDAMENTAL_1);
        }

        if (in_array(EscolaSeeder::FUNDAMENTAL_2, $etapas, true)) {
            $turmas = array_merge($turmas, self::TURMAS_FUNDAMENTAL_2);
        }

        return $turmas;
    }
}
