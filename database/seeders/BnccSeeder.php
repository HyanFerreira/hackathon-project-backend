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
            ['MA', 'EF06MA08', 'Reconhecer que os números racionais positivos podem ser expressos nas formas fracionária e decimal, relacionando-os a parte de um todo.'],
            ['MA', 'EF06MA13', 'Resolver e elaborar problemas que envolvam porcentagens (como 10%, 25%, 50%, 75% e 100%).'],
            ['MA', 'EF06MA24', 'Resolver e elaborar problemas que envolvam o cálculo do perímetro de figuras planas.'],
            ['MA', 'EF06MA25', 'Resolver e elaborar problemas que envolvam o cálculo da área de figuras planas, como retângulos e quadrados.'],
            // Língua Portuguesa — 6º ano
            ['LP', 'EF06LP01', 'Reconhecer a impossibilidade de uma neutralidade absoluta no relato de fatos e analisar como isso se materializa nos textos.'],
            ['LP', 'EF67LP01', 'Analisar a estrutura e o funcionamento dos hiperlinks em textos noticiosos publicados na web e o papel dos elementos multissemióticos.'],
            ['LP', 'EF06LP11', 'Utilizar, ao produzir texto, conhecimentos linguísticos e gramaticais: ortografia, pontuação, concordância e classes de palavras.'],
            // Ciências — 6º ano
            ['CI', 'EF06CI01', 'Classificar como homogênea ou heterogênea a mistura de dois ou mais materiais (água e sal, água e óleo, água e areia, etc.).'],
            ['CI', 'EF06CI02', 'Identificar evidências de transformações químicas a partir do resultado de misturas de materiais que originam produtos diferentes.'],
            ['CI', 'EF06CI05', 'Explicar a organização básica das células e seu papel como unidade estrutural e funcional dos seres vivos.'],
            ['CI', 'EF06CI06', 'Concluir que os diferentes sistemas do corpo humano (como circulatório, respiratório e digestório) funcionam de forma integrada.'],
            ['CI', 'EF06CI11', 'Identificar as diferentes camadas que estruturam o planeta Terra (da estrutura interna à atmosfera).'],
            // História — 6º ano
            ['HI', 'EF06HI01', 'Identificar diferentes formas de compreensão da noção de tempo e de periodização dos processos históricos (continuidades e rupturas).'],
            ['HI', 'EF06HI03', 'Identificar as hipóteses sobre a origem do ser humano e a chegada dos primeiros grupos humanos ao continente americano.'],
            ['HI', 'EF06HI08', 'Identificar aspectos das primeiras civilizações urbanas (como Mesopotâmia e Egito) e suas formas de organização.'],
            // Geografia — 6º ano
            ['GE', 'EF06GE01', 'Comparar modificações das paisagens nos lugares de vivência e os usos desses lugares em diferentes tempos.'],
            ['GE', 'EF06GE03', 'Descrever os movimentos do planeta Terra (rotação e translação) e suas consequências, como o dia e a noite e as estações do ano.'],
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
