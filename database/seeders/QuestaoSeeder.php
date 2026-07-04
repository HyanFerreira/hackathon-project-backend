<?php

namespace Database\Seeders;

use App\Models\Habilidade;
use App\Models\Questao;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestaoSeeder extends Seeder
{
    /**
     * Questões de exemplo (6º ano) ligadas às habilidades da BNCC.
     * A primeira alternativa marcada com `true` é o gabarito.
     *
     * @var list<array{habilidade:string,enunciado:string,dificuldade:string,pontos:int,alternativas:list<array{texto:string,correta:bool}>}>
     */
    private const QUESTOES = [
        // Matemática
        [
            'habilidade' => 'EF06MA01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Qual é o maior número entre as opções abaixo?',
            'alternativas' => [
                ['texto' => '1.230', 'correta' => true],
                ['texto' => '1.203', 'correta' => false],
                ['texto' => '1.032', 'correta' => false],
                ['texto' => '1.023', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA02', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'No número 3.507, quanto vale o algarismo 5?',
            'alternativas' => [
                ['texto' => '500', 'correta' => true],
                ['texto' => '5', 'correta' => false],
                ['texto' => '50', 'correta' => false],
                ['texto' => '5.000', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA03', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'João tinha 250 figurinhas e ganhou mais 175. Com quantas ficou?',
            'alternativas' => [
                ['texto' => '425', 'correta' => true],
                ['texto' => '415', 'correta' => false],
                ['texto' => '325', 'correta' => false],
                ['texto' => '435', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Qual fração representa a metade de um inteiro?',
            'alternativas' => [
                ['texto' => '1/2', 'correta' => true],
                ['texto' => '1/3', 'correta' => false],
                ['texto' => '1/4', 'correta' => false],
                ['texto' => '2/3', 'correta' => false],
            ],
        ],

        // Língua Portuguesa
        [
            'habilidade' => 'EF06LP01', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'No relato de uma notícia, é correto afirmar que o texto:',
            'alternativas' => [
                ['texto' => 'sempre reflete, em algum grau, o ponto de vista de quem escreve', 'correta' => true],
                ['texto' => 'é totalmente neutro e sem opinião', 'correta' => false],
                ['texto' => 'nunca apresenta fatos reais', 'correta' => false],
                ['texto' => 'só pode ser escrito por robôs', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF67LP01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Em um texto na web, para que serve um hiperlink?',
            'alternativas' => [
                ['texto' => 'Levar o leitor a outra página ou conteúdo relacionado', 'correta' => true],
                ['texto' => 'Apagar o texto da página', 'correta' => false],
                ['texto' => 'Trocar a cor do fundo', 'correta' => false],
                ['texto' => 'Aumentar o volume do computador', 'correta' => false],
            ],
        ],

        // Ciências
        [
            'habilidade' => 'EF06CI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A mistura de água e sal completamente dissolvido é classificada como:',
            'alternativas' => [
                ['texto' => 'homogênea', 'correta' => true],
                ['texto' => 'heterogênea', 'correta' => false],
                ['texto' => 'sólida', 'correta' => false],
                ['texto' => 'gasosa', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI02', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Qual das opções é um exemplo de transformação química?',
            'alternativas' => [
                ['texto' => 'A queima de um pedaço de papel', 'correta' => true],
                ['texto' => 'O derretimento do gelo', 'correta' => false],
                ['texto' => 'A quebra de um copo', 'correta' => false],
                ['texto' => 'A evaporação da água', 'correta' => false],
            ],
        ],

        // História
        [
            'habilidade' => 'EF06HI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A divisão da história em grandes períodos é chamada de:',
            'alternativas' => [
                ['texto' => 'periodização', 'correta' => true],
                ['texto' => 'cartografia', 'correta' => false],
                ['texto' => 'alfabetização', 'correta' => false],
                ['texto' => 'globalização', 'correta' => false],
            ],
        ],

        // Geografia
        [
            'habilidade' => 'EF06GE01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A construção de uma cidade sobre uma área de floresta é um exemplo de:',
            'alternativas' => [
                ['texto' => 'modificação da paisagem pela ação humana', 'correta' => true],
                ['texto' => 'fenômeno puramente natural', 'correta' => false],
                ['texto' => 'movimento de rotação da Terra', 'correta' => false],
                ['texto' => 'eclipse solar', 'correta' => false],
            ],
        ],
    ];

    public function run(): void
    {
        $professor = User::query()->where('cpf', '39053344705')->first(); // Carla

        if (! $professor || ! $professor->escola_id) {
            return;
        }

        foreach (self::QUESTOES as $dados) {
            $habilidade = Habilidade::query()->where('codigo', $dados['habilidade'])->first();

            if (! $habilidade) {
                continue;
            }

            $questao = Questao::query()->updateOrCreate(
                [
                    'professor_id' => $professor->id,
                    'enunciado' => $dados['enunciado'],
                ],
                [
                    'escola_id' => $professor->escola_id,
                    'dificuldade' => $dados['dificuldade'],
                    'pontos' => $dados['pontos'],
                    'status' => 'ativa',
                ],
            );

            $questao->alternativas()->delete();
            $questao->alternativas()->createMany($dados['alternativas']);
            $questao->habilidades()->sync([$habilidade->id]);
        }
    }
}
