<?php

namespace App\Services\Personagem;

use App\Models\Aluno;
use App\Models\AlunoPersonagem;
use App\Models\Personagem;
use App\Services\Aluno\PerfilAlunoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PersonagemService
{
    /** Questões acumuladas (com o personagem equipado) necessárias para cada nível. */
    private const NIVEIS = [2 => 10, 3 => 30];

    /** Personagem inicial gratuito que todo aluno recebe ao começar. */
    public const CHAVE_INICIAL = 'lumi';

    /** Tier do personagem gratuito (não fica à venda na loja). */
    public const TIER_FREE = 'free';

    public function __construct(private readonly PerfilAlunoService $perfis) {}

    /**
     * Garante que o aluno já possui (e tem equipado) o personagem inicial gratuito.
     * Chamado quando o aluno inicia o sistema (login).
     */
    public function garantirInicial(Aluno $aluno): void
    {
        if ($aluno->personagens()->exists()) {
            return;
        }

        $lumi = Personagem::query()->where('chave', self::CHAVE_INICIAL)->first();

        if (! $lumi) {
            return;
        }

        $aluno->personagens()->create([
            'personagem_id' => $lumi->id,
            'nivel' => 1,
            'questoes_respondidas' => 0,
            'equipado' => true,
            'comprado_em' => now(),
        ]);
    }

    /**
     * Catálogo da loja com o que o aluno já possui. Inclui o personagem
     * inicial gratuito (tier `free`), que aparece, mas não pode ser comprado.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function catalogo(Aluno $aluno): Collection
    {
        $this->garantirInicial($aluno);
        $possui = $aluno->personagens()->pluck('personagem_id')->all();

        return Personagem::query()
            ->where('status', 'ativo')
            ->orderBy('preco')
            ->get()
            ->map(fn (Personagem $p) => [
                'personagem' => $p,
                'ja_possui' => in_array($p->id, $possui, true),
            ]);
    }

    /**
     * Personagens do aluno com nível e progresso (inclui o inicial gratuito).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function inventario(Aluno $aluno): Collection
    {
        $this->garantirInicial($aluno);

        return $aluno->personagens()->with('personagem')->get()->map(fn (AlunoPersonagem $ap) => [
            'aluno_personagem' => $ap,
            'proximo_nivel_em' => $this->questoesParaProximoNivel($ap->questoes_respondidas, $ap->personagem->nivel_maximo),
        ]);
    }

    public function comprar(Aluno $aluno, Personagem $personagem): AlunoPersonagem
    {
        if ($personagem->tier === self::TIER_FREE) {
            throw ValidationException::withMessages([
                'personagem' => ['Este personagem não está à venda.'],
            ]);
        }

        if ($aluno->personagens()->where('personagem_id', $personagem->id)->exists()) {
            throw ValidationException::withMessages([
                'personagem' => ['Você já possui este personagem.'],
            ]);
        }

        $perfil = $this->perfis->garantir($aluno);

        if ($perfil->pontos < $personagem->preco) {
            throw ValidationException::withMessages([
                'pontos' => ['Pontos insuficientes para comprar este personagem.'],
            ]);
        }

        return DB::transaction(function () use ($aluno, $personagem, $perfil) {
            $perfil->pontos -= $personagem->preco;
            $perfil->save();

            // Equipa automaticamente se o aluno ainda não tem nenhum equipado.
            $temEquipado = $aluno->personagens()->where('equipado', true)->exists();

            return $aluno->personagens()->create([
                'personagem_id' => $personagem->id,
                'nivel' => 1,
                'questoes_respondidas' => 0,
                'equipado' => ! $temEquipado,
                'comprado_em' => now(),
            ]);
        });
    }

    public function equipar(Aluno $aluno, Personagem $personagem): AlunoPersonagem
    {
        $possuido = $aluno->personagens()->where('personagem_id', $personagem->id)->first();

        if (! $possuido instanceof AlunoPersonagem) {
            throw ValidationException::withMessages([
                'personagem' => ['Você não possui este personagem.'],
            ]);
        }

        return DB::transaction(function () use ($aluno, $possuido) {
            $aluno->personagens()->update(['equipado' => false]);
            $possuido->equipado = true;
            $possuido->save();

            return $possuido->load('personagem');
        });
    }

    /**
     * Registra uma questão respondida no personagem equipado, evoluindo-o.
     *
     * @return array<string, mixed>|null
     */
    public function registrarResposta(Aluno $aluno): ?array
    {
        $equipado = $aluno->personagens()->where('equipado', true)->with('personagem')->first();

        if (! $equipado instanceof AlunoPersonagem) {
            return null;
        }

        $nivelAntes = $equipado->nivel;
        $equipado->questoes_respondidas += 1;
        $equipado->nivel = $this->nivelPara($equipado->questoes_respondidas, $equipado->personagem->nivel_maximo);
        $equipado->save();

        return [
            'personagem' => $equipado->personagem,
            'nivel' => $equipado->nivel,
            'questoes_respondidas' => $equipado->questoes_respondidas,
            'subiu_nivel' => $equipado->nivel > $nivelAntes,
        ];
    }

    public function nivelPara(int $questoes, int $nivelMaximo): int
    {
        $nivel = 1;

        foreach (self::NIVEIS as $n => $requisito) {
            if ($questoes >= $requisito) {
                $nivel = $n;
            }
        }

        return min($nivel, $nivelMaximo);
    }

    public function questoesParaProximoNivel(int $questoes, int $nivelMaximo): ?int
    {
        foreach (self::NIVEIS as $nivel => $requisito) {
            if ($nivel <= $nivelMaximo && $questoes < $requisito) {
                return $requisito - $questoes;
            }
        }

        return null; // já está no nível máximo
    }
}
