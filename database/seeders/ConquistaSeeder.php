<?php

namespace Database\Seeders;

use App\Models\Conquista;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConquistaSeeder extends Seeder
{
    /**
     * Conquistas do sistema (marcos permanentes).
     * tipo => questoes_respondidas | acertos | sequencia_acertos | pontos | nivel
     * O `icone` é o nome do arquivo SVG (slug do nome + .svg), ex.: "foco-total.svg".
     *
     * @var list<array{nome:string,descricao:string,tipo:string,meta:int,recompensa_pontos:int,recompensa_xp:int}>
     */
    private const CONQUISTAS = [
        // Volume de questões respondidas
        ['nome' => 'Primeiros Passos', 'descricao' => 'Responda sua primeira questão.', 'tipo' => Conquista::TIPO_RESPONDIDAS, 'meta' => 1, 'recompensa_pontos' => 10, 'recompensa_xp' => 10],
        ['nome' => 'Estudioso', 'descricao' => 'Responda 10 questões.', 'tipo' => Conquista::TIPO_RESPONDIDAS, 'meta' => 10, 'recompensa_pontos' => 30, 'recompensa_xp' => 30],
        ['nome' => 'Dedicado', 'descricao' => 'Responda 25 questões.', 'tipo' => Conquista::TIPO_RESPONDIDAS, 'meta' => 25, 'recompensa_pontos' => 60, 'recompensa_xp' => 60],
        ['nome' => 'Maratonista do Saber', 'descricao' => 'Responda 50 questões.', 'tipo' => Conquista::TIPO_RESPONDIDAS, 'meta' => 50, 'recompensa_pontos' => 120, 'recompensa_xp' => 120],

        // Acertos totais
        ['nome' => 'Certeiro', 'descricao' => 'Acerte 10 questões.', 'tipo' => Conquista::TIPO_ACERTOS, 'meta' => 10, 'recompensa_pontos' => 40, 'recompensa_xp' => 40],
        ['nome' => 'Mestre das Respostas', 'descricao' => 'Acerte 50 questões.', 'tipo' => Conquista::TIPO_ACERTOS, 'meta' => 50, 'recompensa_pontos' => 150, 'recompensa_xp' => 150],
        ['nome' => 'Gênio', 'descricao' => 'Acerte 100 questões.', 'tipo' => Conquista::TIPO_ACERTOS, 'meta' => 100, 'recompensa_pontos' => 300, 'recompensa_xp' => 300],

        // Sequência de acertos (foco)
        ['nome' => 'Foco Total', 'descricao' => 'Acerte 5 questões seguidas.', 'tipo' => Conquista::TIPO_SEQUENCIA, 'meta' => 5, 'recompensa_pontos' => 50, 'recompensa_xp' => 50],
        ['nome' => 'Imparável', 'descricao' => 'Acerte 10 questões seguidas.', 'tipo' => Conquista::TIPO_SEQUENCIA, 'meta' => 10, 'recompensa_pontos' => 120, 'recompensa_xp' => 120],

        // Pontos acumulados
        ['nome' => 'Colecionador de Pontos', 'descricao' => 'Acumule 500 pontos.', 'tipo' => Conquista::TIPO_PONTOS, 'meta' => 500, 'recompensa_pontos' => 0, 'recompensa_xp' => 100],
        ['nome' => 'Lenda dos Pontos', 'descricao' => 'Acumule 1000 pontos.', 'tipo' => Conquista::TIPO_PONTOS, 'meta' => 1000, 'recompensa_pontos' => 0, 'recompensa_xp' => 250],

        // Nível
        ['nome' => 'Subindo de Nível', 'descricao' => 'Alcance o nível 5.', 'tipo' => Conquista::TIPO_NIVEL, 'meta' => 5, 'recompensa_pontos' => 80, 'recompensa_xp' => 0],
        ['nome' => 'Lenda da Escola', 'descricao' => 'Alcance o nível 10.', 'tipo' => Conquista::TIPO_NIVEL, 'meta' => 10, 'recompensa_pontos' => 200, 'recompensa_xp' => 0],
    ];

    public function run(): void
    {
        foreach (self::CONQUISTAS as $conquista) {
            Conquista::query()->updateOrCreate(
                ['nome' => $conquista['nome']],
                [
                    ...$conquista,
                    'icone' => Str::slug($conquista['nome']).'.svg',
                    'status' => 'ativa',
                ],
            );
        }
    }
}
