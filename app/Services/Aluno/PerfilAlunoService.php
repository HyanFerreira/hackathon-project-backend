<?php

namespace App\Services\Aluno;

use App\Models\Aluno;
use App\Models\PerfilAluno;

class PerfilAlunoService
{
    /** Minutos para regenerar 1 ponto de energia. */
    private const INTERVALO_REGEN_MIN = 10;

    /**
     * Retorna o perfil do aluno, criando-o com os valores iniciais se necessário,
     * já com a energia regenerada pelo tempo decorrido.
     */
    public function garantir(Aluno $aluno): PerfilAluno
    {
        $perfil = $aluno->perfil()->firstOrCreate([], [
            'pontos' => 0,
            'pontuacao_total' => 0,
            'xp' => 0,
            'nivel' => 1,
            'energia' => 10,
            'energia_maxima' => 10,
            'energia_atualizada_em' => now(),
            'dias_seguidos_login' => 0,
            'maior_dias_seguidos_login' => 0,
            'ultimo_login_em' => null,
        ]);

        return $this->regenerarEnergia($perfil);
    }

    public function regenerarEnergia(PerfilAluno $perfil): PerfilAluno
    {
        if ($perfil->energia >= $perfil->energia_maxima) {
            return $perfil;
        }

        $base = $perfil->energia_atualizada_em ?? $perfil->updated_at ?? now();
        $minutos = (int) $base->diffInMinutes(now());
        $ganho = intdiv($minutos, self::INTERVALO_REGEN_MIN);

        if ($ganho > 0) {
            $perfil->energia = min($perfil->energia_maxima, $perfil->energia + $ganho);
            $perfil->energia_atualizada_em = $perfil->energia >= $perfil->energia_maxima
                ? now()
                : $base->copy()->addMinutes($ganho * self::INTERVALO_REGEN_MIN);
            $perfil->save();
        }

        return $perfil;
    }
}
