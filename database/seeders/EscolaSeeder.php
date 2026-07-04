<?php

namespace Database\Seeders;

use App\Models\Escola;
use Illuminate\Database\Seeder;

class EscolaSeeder extends Seeder
{
    /** Escola de referência usada pelos demais seeders (gestor/professor/aluno). */
    public const PRINCIPAL = 'EMEF Prof.ª Maria Aparecida Ujio';

    public const FUNDAMENTAL_1 = 'fundamental_1';

    public const FUNDAMENTAL_2 = 'fundamental_2';

    /**
     * Escolas municipais de Caraguatatuba que atendem Ensino Fundamental.
     * Base: relação oficial de 18/05/2026 e matrículas por etapa do Censo Escolar.
     *
     * @var array<string, list<string>>
     */
    public const ESCOLAS = [
        'CIEFI Prof.ª Adolfina Leonor Soares dos Santos' => [self::FUNDAMENTAL_1],
        'CIEFI Prof.ª Antonia Ribeiro da Silva' => [self::FUNDAMENTAL_1],
        'CIEFI Prof.ª Edna Maria Nogueira Ferraz' => [self::FUNDAMENTAL_2],
        'CIEFI Prof.ª Maria Carlita Saraiva Guedes' => [self::FUNDAMENTAL_1],
        'EMEF Dr. Carlos de Almeida Rodrigues' => [self::FUNDAMENTAL_1],
        'EMEF Prof. Antonio de Freitas Avelar' => [self::FUNDAMENTAL_2],
        'EMEF Prof. Auracy Mansano' => [self::FUNDAMENTAL_2],
        'EMEF Prof. Euclydes Ferreira' => [self::FUNDAMENTAL_2],
        'EMEF Prof. Geraldo de Lima' => [self::FUNDAMENTAL_1],
        'EMEF Prof. Jorge Passos' => [self::FUNDAMENTAL_1],
        'EMEF Prof. Luiz Ribeiro Muniz' => [self::FUNDAMENTAL_2],
        'EMEF Prof. Luiz Silvar do Prado' => [self::FUNDAMENTAL_2],
        'EMEF Prof. Oswaldo Ferreira' => [self::FUNDAMENTAL_1],
        'EMEF Prof.ª Antonia Antunes Arouca' => [self::FUNDAMENTAL_2],
        'EMEF Prof.ª Débora Valle da Silva Pilon' => [self::FUNDAMENTAL_1],
        'EMEF Prof.ª Jane Urbano Focesi' => [self::FUNDAMENTAL_1],
        'EMEF Prof.ª Maria Aparecida de Carvalho' => [self::FUNDAMENTAL_1, self::FUNDAMENTAL_2],
        'EMEF Prof.ª Maria Aparecida Ujio' => [self::FUNDAMENTAL_1, self::FUNDAMENTAL_2],
        'EMEF Prof.ª Maria Moraes de Oliveira' => [self::FUNDAMENTAL_2],
        'EMEFEBS Prof. Ricardo Luques Sammarco Serra' => [self::FUNDAMENTAL_1, self::FUNDAMENTAL_2],
        'EMEI/EMEF Benedito Inácio Soares' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Carlos Altero Ortega' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF João Thimóteo do Rosário' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Masako Sone' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Pedro João de Oliveira' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof. João Baptista Gardelin' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof. João Benedito Marcondes' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof. Lúcio Jacinto dos Santos' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof. Yasutada Nasu' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof. Bernardo Ferreira Louzada' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof.ª Aida de Almeida Castro Grazioli' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof. Alaor Xavier Junqueira' => [self::FUNDAMENTAL_1],
        'EMEI/EMEF Prof.ª Maria Thereza de Souza Castro' => [self::FUNDAMENTAL_1],
    ];

    public function run(): void
    {
        foreach (array_keys(self::ESCOLAS) as $nome) {
            Escola::query()->updateOrCreate(
                ['nome' => $nome],
                [
                    'cidade' => 'Caraguatatuba',
                    'estado' => 'SP',
                    'status' => 'ativa',
                ],
            );
        }
    }
}
