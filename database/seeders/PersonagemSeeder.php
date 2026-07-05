<?php

namespace Database\Seeders;

use App\Models\Personagem;
use Illuminate\Database\Seeder;

class PersonagemSeeder extends Seeder
{
    /**
     * Personagens da loja. A `chave` casa com os arquivos SVG do frontend
     * (ex.: grunt_chibi_level_1.svg). Cada um evolui até 3 níveis.
     *
     * @var list<array{chave:string,nome:string,descricao:string,tier:string,preco:int}>
     */
    private const PERSONAGENS = [
        // Comuns
        ['chave' => 'grunt_chibi', 'nome' => 'Grunt', 'descricao' => 'Um parceiro atrapalhado, mas cheio de vontade.', 'tier' => 'comum', 'preco' => 100],
        ['chave' => 'pip_chibi_v2', 'nome' => 'Pip', 'descricao' => 'Pequeno, rápido e curioso.', 'tier' => 'comum', 'preco' => 100],
        ['chave' => 'leafy', 'nome' => 'Leafy', 'descricao' => 'Um broto travesso que cresce junto com você.', 'tier' => 'comum', 'preco' => 120],

        // Raros
        ['chave' => 'leo', 'nome' => 'Léo', 'descricao' => 'Um leãozinho corajoso que adora desafios.', 'tier' => 'raro', 'preco' => 250],
        ['chave' => 'luna', 'nome' => 'Luna', 'descricao' => 'Serena e sábia, evolui com a dedicação.', 'tier' => 'raro', 'preco' => 250],
        ['chave' => 'nox', 'nome' => 'Nox', 'descricao' => 'Espírito da noite, calmo e curioso.', 'tier' => 'raro', 'preco' => 280],

        // Épicos
        ['chave' => 'drako', 'nome' => 'Drako', 'descricao' => 'Um dragãozinho destemido em formação.', 'tier' => 'epico', 'preco' => 400],
        ['chave' => 'kitsune', 'nome' => 'Kitsune', 'descricao' => 'Raposa mística de muitas caudas.', 'tier' => 'epico', 'preco' => 400],
        ['chave' => 'fenro', 'nome' => 'Fenro', 'descricao' => 'Guardião flamejante e leal.', 'tier' => 'epico', 'preco' => 450],

        // Lendário
        ['chave' => 'elyra', 'nome' => 'Elyra', 'descricao' => 'Uma lenda luminosa, o ápice da jornada.', 'tier' => 'lendario', 'preco' => 600],
    ];

    public function run(): void
    {
        foreach (self::PERSONAGENS as $personagem) {
            Personagem::query()->updateOrCreate(
                ['chave' => $personagem['chave']],
                [...$personagem, 'nivel_maximo' => 3, 'status' => 'ativo'],
            );
        }
    }
}
