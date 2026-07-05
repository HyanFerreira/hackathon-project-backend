<?php

namespace Database\Seeders;

use App\Models\Habilidade;
use App\Models\Questao;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestaoSeeder extends Seeder
{
    /**
     * Banco de questões (6º ano) alinhado à BNCC.
     * A alternativa marcada com `true` é o gabarito.
     *
     * @var list<array{habilidade:string,enunciado:string,dificuldade:string,pontos:int,alternativas:list<array{texto:string,correta:bool}>}>
     */
    private const QUESTOES = [
        // ---------------- Matemática ----------------
        [
            'habilidade' => 'EF06MA01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Qual é o menor número entre 0,3 · 0,03 · 0,33 · 0,003?',
            'alternativas' => [
                ['texto' => '0,003', 'correta' => true],
                ['texto' => '0,03', 'correta' => false],
                ['texto' => '0,3', 'correta' => false],
                ['texto' => '0,33', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA02', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'No número 4.286, o algarismo 2 ocupa a ordem das:',
            'alternativas' => [
                ['texto' => 'centenas', 'correta' => true],
                ['texto' => 'unidades', 'correta' => false],
                ['texto' => 'dezenas', 'correta' => false],
                ['texto' => 'unidades de milhar', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA03', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Uma escola tem 12 turmas com 30 alunos cada. Quantos alunos há no total?',
            'alternativas' => [
                ['texto' => '360', 'correta' => true],
                ['texto' => '42', 'correta' => false],
                ['texto' => '300', 'correta' => false],
                ['texto' => '420', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA08', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Uma pizza foi dividida em 8 fatias iguais e João comeu 3 fatias. Que fração da pizza ele comeu?',
            'alternativas' => [
                ['texto' => '3/8', 'correta' => true],
                ['texto' => '8/3', 'correta' => false],
                ['texto' => '5/8', 'correta' => false],
                ['texto' => '3/5', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA13', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Uma blusa custa R$ 80,00 e está com 25% de desconto. Qual é o valor do desconto?',
            'alternativas' => [
                ['texto' => 'R$ 20,00', 'correta' => true],
                ['texto' => 'R$ 25,00', 'correta' => false],
                ['texto' => 'R$ 40,00', 'correta' => false],
                ['texto' => 'R$ 60,00', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA24', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Um terreno retangular tem 15 m de comprimento e 10 m de largura. Qual é o seu perímetro?',
            'alternativas' => [
                ['texto' => '50 m', 'correta' => true],
                ['texto' => '25 m', 'correta' => false],
                ['texto' => '60 m', 'correta' => false],
                ['texto' => '150 m', 'correta' => false],
            ],
        ],

        // ---------------- Língua Portuguesa ----------------
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Na frase "A casa amarela é bonita", a palavra "amarela" é um:',
            'alternativas' => [
                ['texto' => 'adjetivo', 'correta' => true],
                ['texto' => 'substantivo', 'correta' => false],
                ['texto' => 'verbo', 'correta' => false],
                ['texto' => 'artigo', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Qual das palavras abaixo é um substantivo?',
            'alternativas' => [
                ['texto' => 'felicidade', 'correta' => true],
                ['texto' => 'correr', 'correta' => false],
                ['texto' => 'feliz', 'correta' => false],
                ['texto' => 'muito', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Em qual frase a vírgula foi usada corretamente para separar o vocativo?',
            'alternativas' => [
                ['texto' => 'João, feche a porta.', 'correta' => true],
                ['texto' => 'João feche, a porta.', 'correta' => false],
                ['texto' => 'João feche a, porta.', 'correta' => false],
                ['texto' => 'João feche a porta,', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Assinale a frase em que a concordância está correta:',
            'alternativas' => [
                ['texto' => 'Os alunos estudaram para a prova.', 'correta' => true],
                ['texto' => 'Os aluno estudaram para a prova.', 'correta' => false],
                ['texto' => 'Os alunos estudou para a prova.', 'correta' => false],
                ['texto' => 'O alunos estudaram para a prova.', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP01', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Ao ler uma notícia, é correto afirmar que o texto:',
            'alternativas' => [
                ['texto' => 'reflete, em algum grau, o ponto de vista de quem o escreveu', 'correta' => true],
                ['texto' => 'é sempre totalmente neutro e sem opinião', 'correta' => false],
                ['texto' => 'nunca apresenta fatos reais', 'correta' => false],
                ['texto' => 'só pode ser escrito por jornalistas famosos', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF67LP01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Em uma página da internet, para que serve um hiperlink?',
            'alternativas' => [
                ['texto' => 'Levar o leitor a outro conteúdo relacionado', 'correta' => true],
                ['texto' => 'Apagar o texto da página', 'correta' => false],
                ['texto' => 'Trocar a cor do fundo', 'correta' => false],
                ['texto' => 'Aumentar o volume do computador', 'correta' => false],
            ],
        ],

        // ---------------- Ciências ----------------
        [
            'habilidade' => 'EF06CI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A mistura de água com açúcar totalmente dissolvido é classificada como:',
            'alternativas' => [
                ['texto' => 'homogênea', 'correta' => true],
                ['texto' => 'heterogênea', 'correta' => false],
                ['texto' => 'sólida', 'correta' => false],
                ['texto' => 'gasosa', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Qual das opções é um exemplo de mistura heterogênea?',
            'alternativas' => [
                ['texto' => 'água e óleo', 'correta' => true],
                ['texto' => 'água e sal dissolvido', 'correta' => false],
                ['texto' => 'água e açúcar dissolvido', 'correta' => false],
                ['texto' => 'água e álcool', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI02', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Qual fenômeno é um exemplo de transformação química?',
            'alternativas' => [
                ['texto' => 'O enferrujamento do ferro', 'correta' => true],
                ['texto' => 'O derretimento do gelo', 'correta' => false],
                ['texto' => 'A fervura da água', 'correta' => false],
                ['texto' => 'Amassar uma folha de papel', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI05', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A menor unidade estrutural e funcional dos seres vivos é a:',
            'alternativas' => [
                ['texto' => 'célula', 'correta' => true],
                ['texto' => 'molécula', 'correta' => false],
                ['texto' => 'átomo', 'correta' => false],
                ['texto' => 'órgão', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI05', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Qual estrutura está presente na célula vegetal, mas não na célula animal?',
            'alternativas' => [
                ['texto' => 'parede celular', 'correta' => true],
                ['texto' => 'núcleo', 'correta' => false],
                ['texto' => 'membrana plasmática', 'correta' => false],
                ['texto' => 'citoplasma', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI11', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'A camada mais externa e gasosa que envolve o planeta Terra é a:',
            'alternativas' => [
                ['texto' => 'atmosfera', 'correta' => true],
                ['texto' => 'crosta', 'correta' => false],
                ['texto' => 'manto', 'correta' => false],
                ['texto' => 'núcleo', 'correta' => false],
            ],
        ],

        // ---------------- História ----------------
        [
            'habilidade' => 'EF06HI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A divisão da História em grandes períodos (Antiga, Medieval, Moderna...) é chamada de:',
            'alternativas' => [
                ['texto' => 'periodização', 'correta' => true],
                ['texto' => 'cartografia', 'correta' => false],
                ['texto' => 'globalização', 'correta' => false],
                ['texto' => 'urbanização', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Os documentos, objetos e vestígios usados para estudar o passado são chamados de:',
            'alternativas' => [
                ['texto' => 'fontes históricas', 'correta' => true],
                ['texto' => 'lendas', 'correta' => false],
                ['texto' => 'mitos', 'correta' => false],
                ['texto' => 'teorias', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'O período conhecido como Pré-História caracteriza-se por ser anterior à:',
            'alternativas' => [
                ['texto' => 'invenção da escrita', 'correta' => true],
                ['texto' => 'descoberta do fogo', 'correta' => false],
                ['texto' => 'prática da agricultura', 'correta' => false],
                ['texto' => 'construção de casas', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI03', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Segundo a teoria mais aceita, os primeiros grupos humanos chegaram à América vindos principalmente:',
            'alternativas' => [
                ['texto' => 'da Ásia, pelo Estreito de Bering', 'correta' => true],
                ['texto' => 'da Europa, de barco', 'correta' => false],
                ['texto' => 'da África, a pé', 'correta' => false],
                ['texto' => 'da Oceania, de avião', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI08', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'As pirâmides construídas como túmulos dos faraós pertencem à civilização:',
            'alternativas' => [
                ['texto' => 'egípcia', 'correta' => true],
                ['texto' => 'grega', 'correta' => false],
                ['texto' => 'romana', 'correta' => false],
                ['texto' => 'chinesa', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI08', 'dificuldade' => 'dificil', 'pontos' => 20,
            'enunciado' => 'A escrita cuneiforme, uma das mais antigas do mundo, foi desenvolvida pelos povos da:',
            'alternativas' => [
                ['texto' => 'Mesopotâmia', 'correta' => true],
                ['texto' => 'Grécia', 'correta' => false],
                ['texto' => 'Roma', 'correta' => false],
                ['texto' => 'Índia', 'correta' => false],
            ],
        ],

        // ---------------- Geografia ----------------
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
        [
            'habilidade' => 'EF06GE01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Uma paisagem natural é aquela que:',
            'alternativas' => [
                ['texto' => 'não foi modificada pela ação humana', 'correta' => true],
                ['texto' => 'possui muitos prédios e ruas', 'correta' => false],
                ['texto' => 'foi construída por máquinas', 'correta' => false],
                ['texto' => 'só existe nas cidades grandes', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06GE03', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'O movimento de rotação da Terra é o principal responsável por:',
            'alternativas' => [
                ['texto' => 'dia e noite', 'correta' => true],
                ['texto' => 'estações do ano', 'correta' => false],
                ['texto' => 'eclipses da Lua', 'correta' => false],
                ['texto' => 'fases da Lua', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06GE03', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'O movimento de translação da Terra ao redor do Sol dura aproximadamente:',
            'alternativas' => [
                ['texto' => '365 dias (um ano)', 'correta' => true],
                ['texto' => '24 horas', 'correta' => false],
                ['texto' => '30 dias', 'correta' => false],
                ['texto' => '7 dias', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06GE03', 'dificuldade' => 'dificil', 'pontos' => 20,
            'enunciado' => 'As estações do ano acontecem principalmente por causa:',
            'alternativas' => [
                ['texto' => 'da inclinação do eixo da Terra durante a translação', 'correta' => true],
                ['texto' => 'da rotação da Terra em 24 horas', 'correta' => false],
                ['texto' => 'da distância da Terra à Lua', 'correta' => false],
                ['texto' => 'das fases da Lua', 'correta' => false],
            ],
        ],

        // ============ Ampliação do banco ============

        // Matemática
        [
            'habilidade' => 'EF06MA01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Na reta numérica, qual número está entre 1 e 2?',
            'alternativas' => [
                ['texto' => '1,5', 'correta' => true],
                ['texto' => '0,5', 'correta' => false],
                ['texto' => '2,5', 'correta' => false],
                ['texto' => '3', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA02', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'O número "três mil e quarenta e cinco" é escrito como:',
            'alternativas' => [
                ['texto' => '3.045', 'correta' => true],
                ['texto' => '3.450', 'correta' => false],
                ['texto' => '30.045', 'correta' => false],
                ['texto' => '345', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA03', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Ana tinha R$ 150,00 e gastou R$ 87,00. Quanto sobrou?',
            'alternativas' => [
                ['texto' => 'R$ 63,00', 'correta' => true],
                ['texto' => 'R$ 73,00', 'correta' => false],
                ['texto' => 'R$ 237,00', 'correta' => false],
                ['texto' => 'R$ 67,00', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA08', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'A fração 1/2 é equivalente a qual das frações abaixo?',
            'alternativas' => [
                ['texto' => '2/4', 'correta' => true],
                ['texto' => '1/4', 'correta' => false],
                ['texto' => '1/3', 'correta' => false],
                ['texto' => '3/4', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA13', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Quanto é 10% de 250?',
            'alternativas' => [
                ['texto' => '25', 'correta' => true],
                ['texto' => '2,5', 'correta' => false],
                ['texto' => '50', 'correta' => false],
                ['texto' => '250', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA24', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Um quadrado tem 7 cm de lado. Qual é o seu perímetro?',
            'alternativas' => [
                ['texto' => '28 cm', 'correta' => true],
                ['texto' => '14 cm', 'correta' => false],
                ['texto' => '49 cm', 'correta' => false],
                ['texto' => '21 cm', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06MA25', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Um retângulo tem 6 cm de comprimento e 4 cm de largura. Qual é a sua área?',
            'alternativas' => [
                ['texto' => '24 cm²', 'correta' => true],
                ['texto' => '20 cm²', 'correta' => false],
                ['texto' => '10 cm²', 'correta' => false],
                ['texto' => '48 cm²', 'correta' => false],
            ],
        ],

        // Língua Portuguesa
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Na frase "Ele corre todos os dias", a palavra "corre" é um:',
            'alternativas' => [
                ['texto' => 'verbo', 'correta' => true],
                ['texto' => 'substantivo', 'correta' => false],
                ['texto' => 'adjetivo', 'correta' => false],
                ['texto' => 'advérbio', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Qual das palavras está escrita corretamente?',
            'alternativas' => [
                ['texto' => 'exceção', 'correta' => true],
                ['texto' => 'esceção', 'correta' => false],
                ['texto' => 'excessão', 'correta' => false],
                ['texto' => 'eceção', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'O plural da palavra "cidadão" é:',
            'alternativas' => [
                ['texto' => 'cidadãos', 'correta' => true],
                ['texto' => 'cidadões', 'correta' => false],
                ['texto' => 'cidadans', 'correta' => false],
                ['texto' => 'cidadãoes', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP11', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A palavra "rapidamente" pertence à classe dos:',
            'alternativas' => [
                ['texto' => 'advérbios', 'correta' => true],
                ['texto' => 'adjetivos', 'correta' => false],
                ['texto' => 'substantivos', 'correta' => false],
                ['texto' => 'verbos', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06LP01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'O objetivo principal de uma notícia é:',
            'alternativas' => [
                ['texto' => 'informar o leitor sobre um fato', 'correta' => true],
                ['texto' => 'contar uma história inventada', 'correta' => false],
                ['texto' => 'ensinar uma receita de bolo', 'correta' => false],
                ['texto' => 'vender um produto', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF67LP01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Além do texto escrito, uma notícia publicada na internet pode trazer:',
            'alternativas' => [
                ['texto' => 'fotos, vídeos e links', 'correta' => true],
                ['texto' => 'apenas letras', 'correta' => false],
                ['texto' => 'somente números', 'correta' => false],
                ['texto' => 'nada além do título', 'correta' => false],
            ],
        ],

        // Ciências
        [
            'habilidade' => 'EF06CI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A mistura de água e areia é classificada como:',
            'alternativas' => [
                ['texto' => 'heterogênea', 'correta' => true],
                ['texto' => 'homogênea', 'correta' => false],
                ['texto' => 'pura', 'correta' => false],
                ['texto' => 'gasosa', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI02', 'dificuldade' => 'dificil', 'pontos' => 20,
            'enunciado' => 'Uma evidência de que ocorreu uma transformação química é:',
            'alternativas' => [
                ['texto' => 'a formação de um novo material com propriedades diferentes', 'correta' => true],
                ['texto' => 'apenas a mudança do estado físico', 'correta' => false],
                ['texto' => 'apenas a mudança de formato', 'correta' => false],
                ['texto' => 'apenas o aquecimento do material', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI05', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Em geral, as células do nosso corpo só podem ser vistas:',
            'alternativas' => [
                ['texto' => 'com o uso de microscópio', 'correta' => true],
                ['texto' => 'a olho nu', 'correta' => false],
                ['texto' => 'apenas no escuro', 'correta' => false],
                ['texto' => 'apenas com óculos de sol', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI05', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'A estrutura que comanda as atividades da célula e guarda o material genético é o:',
            'alternativas' => [
                ['texto' => 'núcleo', 'correta' => true],
                ['texto' => 'a membrana', 'correta' => false],
                ['texto' => 'a parede celular', 'correta' => false],
                ['texto' => 'o citoplasma', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI11', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'A camada sólida e externa da Terra, onde vivemos, é chamada de:',
            'alternativas' => [
                ['texto' => 'crosta', 'correta' => true],
                ['texto' => 'manto', 'correta' => false],
                ['texto' => 'núcleo', 'correta' => false],
                ['texto' => 'atmosfera', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI06', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'No corpo humano, o sistema responsável por transportar o sangue é o:',
            'alternativas' => [
                ['texto' => 'circulatório', 'correta' => true],
                ['texto' => 'digestório', 'correta' => false],
                ['texto' => 'respiratório', 'correta' => false],
                ['texto' => 'nervoso', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06CI06', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'A principal função do sistema respiratório é:',
            'alternativas' => [
                ['texto' => 'levar oxigênio ao corpo e eliminar gás carbônico', 'correta' => true],
                ['texto' => 'digerir os alimentos', 'correta' => false],
                ['texto' => 'bombear o sangue', 'correta' => false],
                ['texto' => 'sustentar o corpo', 'correta' => false],
            ],
        ],

        // História
        [
            'habilidade' => 'EF06HI01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Um século corresponde a quantos anos?',
            'alternativas' => [
                ['texto' => '100 anos', 'correta' => true],
                ['texto' => '10 anos', 'correta' => false],
                ['texto' => '1000 anos', 'correta' => false],
                ['texto' => '50 anos', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI01', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'O ano de 2026 pertence a qual século?',
            'alternativas' => [
                ['texto' => 'século XXI', 'correta' => true],
                ['texto' => 'século XX', 'correta' => false],
                ['texto' => 'século XIX', 'correta' => false],
                ['texto' => 'século XXII', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI03', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'Os primeiros seres humanos eram nômades porque:',
            'alternativas' => [
                ['texto' => 'mudavam de lugar em busca de alimento', 'correta' => true],
                ['texto' => 'gostavam de viajar de férias', 'correta' => false],
                ['texto' => 'já viviam em grandes cidades', 'correta' => false],
                ['texto' => 'possuíam automóveis', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI03', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'A passagem do modo de vida nômade para o sedentário aconteceu, principalmente, com o desenvolvimento da:',
            'alternativas' => [
                ['texto' => 'agricultura', 'correta' => true],
                ['texto' => 'escrita', 'correta' => false],
                ['texto' => 'internet', 'correta' => false],
                ['texto' => 'eletricidade', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI08', 'dificuldade' => 'media', 'pontos' => 15,
            'enunciado' => 'O rio Nilo foi fundamental para o desenvolvimento da civilização:',
            'alternativas' => [
                ['texto' => 'egípcia', 'correta' => true],
                ['texto' => 'mesopotâmica', 'correta' => false],
                ['texto' => 'grega', 'correta' => false],
                ['texto' => 'romana', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06HI08', 'dificuldade' => 'dificil', 'pontos' => 20,
            'enunciado' => 'A escrita dos antigos egípcios, feita com desenhos e símbolos, é chamada de:',
            'alternativas' => [
                ['texto' => 'hieróglifos', 'correta' => true],
                ['texto' => 'cuneiforme', 'correta' => false],
                ['texto' => 'alfabeto latino', 'correta' => false],
                ['texto' => 'código morse', 'correta' => false],
            ],
        ],

        // Geografia
        [
            'habilidade' => 'EF06GE01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'O conjunto de elementos naturais e humanos que observamos em um lugar é chamado de:',
            'alternativas' => [
                ['texto' => 'paisagem', 'correta' => true],
                ['texto' => 'mapa', 'correta' => false],
                ['texto' => 'clima', 'correta' => false],
                ['texto' => 'população', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06GE01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Qual das opções é um elemento natural da paisagem?',
            'alternativas' => [
                ['texto' => 'um rio', 'correta' => true],
                ['texto' => 'um prédio', 'correta' => false],
                ['texto' => 'uma ponte', 'correta' => false],
                ['texto' => 'uma avenida', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06GE01', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Qual das opções é um elemento cultural (feito pelo ser humano) da paisagem?',
            'alternativas' => [
                ['texto' => 'uma ponte', 'correta' => true],
                ['texto' => 'uma montanha', 'correta' => false],
                ['texto' => 'um rio', 'correta' => false],
                ['texto' => 'uma nuvem', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06GE03', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'Uma volta completa da Terra em torno de si mesma dura aproximadamente:',
            'alternativas' => [
                ['texto' => '24 horas', 'correta' => true],
                ['texto' => '12 horas', 'correta' => false],
                ['texto' => '365 dias', 'correta' => false],
                ['texto' => '30 dias', 'correta' => false],
            ],
        ],
        [
            'habilidade' => 'EF06GE03', 'dificuldade' => 'facil', 'pontos' => 10,
            'enunciado' => 'O movimento de translação é o deslocamento da Terra ao redor do:',
            'alternativas' => [
                ['texto' => 'Sol', 'correta' => true],
                ['texto' => 'da Lua', 'correta' => false],
                ['texto' => 'de Marte', 'correta' => false],
                ['texto' => 'de outra estrela', 'correta' => false],
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
