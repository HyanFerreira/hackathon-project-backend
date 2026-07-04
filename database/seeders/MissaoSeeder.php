<?php

namespace Database\Seeders;

use App\Models\Missao;
use Illuminate\Database\Seeder;

class MissaoSeeder extends Seeder
{
    /**
     * @var list<array{titulo:string,descricao:string,icone:string,tipo:string,meta:int,periodo:string,recompensa_pontos:int,recompensa_xp:int}>
     */
    private const MISSOES = [
        // Diárias
        ['titulo' => 'Aquecimento do Dia', 'descricao' => 'Responda 5 questões hoje.', 'icone' => '☀️', 'tipo' => Missao::TIPO_RESPONDER, 'meta' => 5, 'periodo' => Missao::PERIODO_DIARIA, 'recompensa_pontos' => 30, 'recompensa_xp' => 30],
        ['titulo' => 'Pontaria do Dia', 'descricao' => 'Acerte 3 questões hoje.', 'icone' => '🎯', 'tipo' => Missao::TIPO_ACERTAR, 'meta' => 3, 'periodo' => Missao::PERIODO_DIARIA, 'recompensa_pontos' => 40, 'recompensa_xp' => 40],

        // Semanais
        ['titulo' => 'Rotina de Estudos', 'descricao' => 'Responda 30 questões nesta semana.', 'icone' => '📅', 'tipo' => Missao::TIPO_RESPONDER, 'meta' => 30, 'periodo' => Missao::PERIODO_SEMANAL, 'recompensa_pontos' => 100, 'recompensa_xp' => 100],
        ['titulo' => 'Semana Certeira', 'descricao' => 'Acerte 20 questões nesta semana.', 'icone' => '🌟', 'tipo' => Missao::TIPO_ACERTAR, 'meta' => 20, 'periodo' => Missao::PERIODO_SEMANAL, 'recompensa_pontos' => 150, 'recompensa_xp' => 150],
    ];

    public function run(): void
    {
        foreach (self::MISSOES as $missao) {
            Missao::query()->updateOrCreate(
                ['titulo' => $missao['titulo']],
                [...$missao, 'status' => 'ativa'],
            );
        }
    }
}
