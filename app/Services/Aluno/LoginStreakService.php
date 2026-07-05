<?php

namespace App\Services\Aluno;

use App\Models\Aluno;
use App\Models\PerfilAluno;
use Illuminate\Support\Facades\DB;

class LoginStreakService
{
    public const PONTOS_DIARIOS = 5;

    public const INTERVALO_BONUS = 7;

    public const BONUS_SEMANAL = 20;

    public function __construct(private readonly PerfilAlunoService $perfis) {}

    /**
     * Registra a entrada diária do aluno e aplica recompensa apenas uma vez por dia.
     *
     * @return array{perfil: PerfilAluno, streak: array<string, mixed>}
     */
    public function registrarEntrada(Aluno $aluno): array
    {
        return DB::transaction(function () use ($aluno) {
            $perfil = $this->perfis->garantir($aluno);
            $perfil = PerfilAluno::query()->lockForUpdate()->findOrFail($perfil->id);

            if ($perfil->ultimo_login_em?->isSameDay(now())) {
                return [
                    'perfil' => $perfil,
                    'streak' => $this->payload($perfil, false, 0, false),
                ];
            }

            $diasSeguidos = $perfil->ultimo_login_em?->isSameDay(now()->subDay())
                ? $perfil->dias_seguidos_login + 1
                : 1;

            $bonusSemanal = $diasSeguidos % self::INTERVALO_BONUS === 0;
            $pontosGanhos = self::PONTOS_DIARIOS + ($bonusSemanal ? self::BONUS_SEMANAL : 0);

            $perfil->dias_seguidos_login = $diasSeguidos;
            $perfil->maior_dias_seguidos_login = max($perfil->maior_dias_seguidos_login, $diasSeguidos);
            $perfil->ultimo_login_em = now();
            $perfil->pontos += $pontosGanhos;
            $perfil->save();

            return [
                'perfil' => $perfil,
                'streak' => $this->payload($perfil, true, $pontosGanhos, $bonusSemanal),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(PerfilAluno $perfil, bool $atualizado, int $pontosGanhos, bool $bonusSemanal): array
    {
        return [
            'dias_seguidos' => $perfil->dias_seguidos_login,
            'maior_dias_seguidos' => $perfil->maior_dias_seguidos_login,
            'ultimo_login_em' => $perfil->ultimo_login_em?->toIso8601String(),
            'atualizado' => $atualizado,
            'pontos_ganhos' => $pontosGanhos,
            'bonus_semanal' => $bonusSemanal,
            'proximo_bonus_em_dias' => self::proximoBonusEm((int) $perfil->dias_seguidos_login),
            'mensagem' => $this->mensagem($perfil, $pontosGanhos, $bonusSemanal),
        ];
    }

    public static function proximoBonusEm(int $diasSeguidos): int
    {
        if ($diasSeguidos === 0) {
            return self::INTERVALO_BONUS;
        }

        $restante = self::INTERVALO_BONUS - ($diasSeguidos % self::INTERVALO_BONUS);

        return $restante === self::INTERVALO_BONUS ? self::INTERVALO_BONUS : $restante;
    }

    private function mensagem(PerfilAluno $perfil, int $pontosGanhos, bool $bonusSemanal): ?string
    {
        if ($pontosGanhos === 0) {
            return null;
        }

        if ($bonusSemanal) {
            return "Sequência de {$perfil->dias_seguidos_login} dias! Você ganhou {$pontosGanhos} pontos.";
        }

        return "Você manteve sua sequência de {$perfil->dias_seguidos_login} dia(s) e ganhou {$pontosGanhos} pontos.";
    }
}
