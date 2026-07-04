<?php

namespace Database\Seeders;

use App\Models\Disciplina;
use App\Models\Habilidade;
use Illuminate\Database\Seeder;

/**
 * Referência BNCC (Ensino Fundamental) — componentes curriculares e um
 * subconjunto curado de habilidades reais (6º ano) para uso imediato.
 * A base completa da BNCC pode ser importada depois sobre este mesmo modelo.
 */
class BnccSeeder extends Seeder
{
    public function run(): void
    {
        $disciplinas = [
            ['sigla' => 'LP', 'nome' => 'Língua Portuguesa', 'area' => 'Linguagens'],
            ['sigla' => 'AR', 'nome' => 'Arte', 'area' => 'Linguagens'],
            ['sigla' => 'EF', 'nome' => 'Educação Física', 'area' => 'Linguagens'],
            ['sigla' => 'LI', 'nome' => 'Língua Inglesa', 'area' => 'Linguagens'],
            ['sigla' => 'MA', 'nome' => 'Matemática', 'area' => 'Matemática'],
            ['sigla' => 'CI', 'nome' => 'Ciências', 'area' => 'Ciências da Natureza'],
            ['sigla' => 'GE', 'nome' => 'Geografia', 'area' => 'Ciências Humanas'],
            ['sigla' => 'HI', 'nome' => 'História', 'area' => 'Ciências Humanas'],
            ['sigla' => 'ER', 'nome' => 'Ensino Religioso', 'area' => 'Ensino Religioso'],
        ];

        $porSigla = [];
        foreach ($disciplinas as $disciplina) {
            $porSigla[$disciplina['sigla']] = Disciplina::query()->updateOrCreate(
                ['sigla' => $disciplina['sigla']],
                ['nome' => $disciplina['nome'], 'area' => $disciplina['area']],
            );
        }

        $habilidades = [
            // Matemática — 6º ano
            ['MA', 'EF06MA01', 'Comparar, ordenar, ler e escrever números naturais e números racionais na forma decimal, por meio de estimativas e da reta numérica.'],
            ['MA', 'EF06MA02', 'Reconhecer o sistema de numeração decimal, destacando semelhanças e diferenças em relação a outros sistemas de numeração.'],
            ['MA', 'EF06MA03', 'Resolver e elaborar problemas que envolvam cálculos (mentais ou escritos, exatos ou aproximados) com números naturais.'],
            // Língua Portuguesa — 6º ano
            ['LP', 'EF06LP01', 'Reconhecer a impossibilidade de uma neutralidade absoluta no relato de fatos e analisar como isso se materializa nos textos.'],
            ['LP', 'EF67LP01', 'Analisar a estrutura e o funcionamento dos hiperlinks em textos noticiosos publicados na web e o papel dos elementos multissemióticos.'],
            // Ciências — 6º ano
            ['CI', 'EF06CI01', 'Classificar como homogênea ou heterogênea a mistura de dois ou mais materiais (água e sal, água e óleo, água e areia, etc.).'],
            ['CI', 'EF06CI02', 'Identificar evidências de transformações químicas a partir do resultado de misturas de materiais que originam produtos diferentes.'],
            // História — 6º ano
            ['HI', 'EF06HI01', 'Identificar diferentes formas de compreensão da noção de tempo e de periodização dos processos históricos (continuidades e rupturas).'],
            // Geografia — 6º ano
            ['GE', 'EF06GE01', 'Comparar modificações das paisagens nos lugares de vivência e os usos desses lugares em diferentes tempos.'],
        ];

        foreach ($habilidades as [$sigla, $codigo, $descricao]) {
            Habilidade::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'disciplina_id' => $porSigla[$sigla]->id,
                    'descricao' => $descricao,
                    'etapa' => 'EF',
                    'ano' => '6',
                ],
            );
        }
    }
}
