<?php

namespace Database\Seeders;

use App\Models\Personagem;
use Illuminate\Database\Seeder;

class PersonagemSeeder extends Seeder
{
    /**
     * Personagens da loja. A `chave` casa com os arquivos do frontend
     * (ex.: grunt_chibi_level_1.png). Cada um evolui até 3 níveis.
     *
     * @var list<array{chave:string,nome:string,descricao:string,tier:string,preco:int}>
     */
    private const PERSONAGENS = [
        ['chave' => 'grunt_chibi', 'nome' => 'Grunt', 'descricao' => 'Um parceiro atrapalhado, mas cheio de vontade.', 'tier' => 'comum', 'preco' => 100],
        ['chave' => 'pip_chibi_v2', 'nome' => 'Pip', 'descricao' => 'Pequeno, rápido e curioso.', 'tier' => 'comum', 'preco' => 100],
        ['chave' => 'leo_juv', 'nome' => 'Léo', 'descricao' => 'Um leãozinho corajoso que adora desafios.', 'tier' => 'raro', 'preco' => 250],
        ['chave' => 'luna_juv', 'nome' => 'Luna', 'descricao' => 'Serena e sábia, evolui com a dedicação.', 'tier' => 'raro', 'preco' => 250],
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
