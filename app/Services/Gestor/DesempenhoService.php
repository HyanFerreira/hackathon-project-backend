<?php

namespace App\Services\Gestor;

use App\Models\Aluno;
use App\Models\Questao;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Análise de desempenho da escola inteira, para o gestor.
 */
class DesempenhoService
{
    private const MIN_RESPOSTAS = 1;

    private const LIMITE = 10;

    /**
     * @return array<string, mixed>
     */
    public function paraGestor(User $gestor): array
    {
        $escolaId = (int) $gestor->escola_id;
        $alunoIds = Aluno::query()->where('escola_id', $escolaId)->pluck('id');

        return [
            'resumo' => $this->resumo($escolaId, $alunoIds),
            'por_turma' => $this->porTurma($escolaId),
            'habilidades_dificeis' => $this->habilidadesDificeis($alunoIds),
            'disciplinas' => $this->porDisciplina($alunoIds),
            'professores_ativos' => $this->professoresAtivos($escolaId),
            'top_alunos' => $this->topAlunos($escolaId),
            'alunos_com_dificuldade' => $this->alunosComDificuldade($alunoIds),
        ];
    }

    /**
     * @param  Collection<int, int>  $alunoIds
     * @return array<string, mixed>
     */
    private function resumo(int $escolaId, Collection $alunoIds): array
    {
        $agg = DB::table('respostas_alunos')
            ->whereIn('aluno_id', $alunoIds)
            ->selectRaw('COUNT(*) as respostas, COALESCE(SUM(correta),0) as acertos, COUNT(DISTINCT aluno_id) as ativos')
            ->first();

        return [
            'turmas' => Turma::query()->where('escola_id', $escolaId)->count(),
            'professores' => User::query()->role('professor')->where('escola_id', $escolaId)->count(),
            'alunos' => $alunoIds->count(),
            'alunos_ativos' => (int) ($agg->ativos ?? 0),
            'respostas' => (int) ($agg->respostas ?? 0),
            'acertos' => (int) ($agg->acertos ?? 0),
            'taxa_acerto' => $this->taxa((int) ($agg->acertos ?? 0), (int) ($agg->respostas ?? 0)),
        ];
    }

    /**
     * Desempenho por turma (melhor → pior).
     *
     * @return list<array<string, mixed>>
     */
    private function porTurma(int $escolaId): array
    {
        return DB::table('turmas as t')
            ->leftJoin('aluno_turma as at', 'at.turma_id', '=', 't.id')
            ->leftJoin('respostas_alunos as r', 'r.aluno_id', '=', 'at.aluno_id')
            ->where('t.escola_id', $escolaId)
            ->groupBy('t.id', 't.nome')
            ->selectRaw('t.id, t.nome, COUNT(DISTINCT at.aluno_id) as alunos, COUNT(r.id) as respostas, COALESCE(SUM(r.correta),0) as acertos')
            ->get()
            ->map(fn ($t) => [
                'turma_id' => (int) $t->id,
                'nome' => $t->nome,
                'alunos' => (int) $t->alunos,
                'respostas' => (int) $t->respostas,
                'taxa_acerto' => $this->taxa((int) $t->acertos, (int) $t->respostas),
            ])
            ->sortByDesc('taxa_acerto')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, int>  $alunoIds
     * @return list<array<string, mixed>>
     */
    private function habilidadesDificeis(Collection $alunoIds): array
    {
        return DB::table('respostas_alunos as r')
            ->join('habilidade_questao as hq', 'hq.questao_id', '=', 'r.questao_id')
            ->join('habilidades as h', 'h.id', '=', 'hq.habilidade_id')
            ->join('disciplinas as d', 'd.id', '=', 'h.disciplina_id')
            ->whereIn('r.aluno_id', $alunoIds)
            ->groupBy('h.id', 'h.codigo', 'h.descricao', 'd.nome')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_RESPOSTAS])
            ->orderByRaw('AVG(r.correta) asc')
            ->limit(self::LIMITE)
            ->selectRaw('h.codigo, h.descricao, d.nome as disciplina, COUNT(*) as respostas, COALESCE(SUM(r.correta),0) as acertos')
            ->get()
            ->map(fn ($h) => [
                'codigo' => $h->codigo,
                'descricao' => $h->descricao,
                'disciplina' => $h->disciplina,
                'respostas' => (int) $h->respostas,
                'acertos' => (int) $h->acertos,
                'taxa_acerto' => $this->taxa((int) $h->acertos, (int) $h->respostas),
            ])
            ->all();
    }

    /**
     * @param  Collection<int, int>  $alunoIds
     * @return list<array<string, mixed>>
     */
    private function porDisciplina(Collection $alunoIds): array
    {
        return DB::table('respostas_alunos as r')
            ->join('habilidade_questao as hq', 'hq.questao_id', '=', 'r.questao_id')
            ->join('habilidades as h', 'h.id', '=', 'hq.habilidade_id')
            ->join('disciplinas as d', 'd.id', '=', 'h.disciplina_id')
            ->whereIn('r.aluno_id', $alunoIds)
            ->groupBy('d.id', 'd.nome')
            ->orderByRaw('AVG(r.correta) asc')
            ->selectRaw('d.id, d.nome, COUNT(*) as respostas, COALESCE(SUM(r.correta),0) as acertos')
            ->get()
            ->map(fn ($d) => [
                'id' => (int) $d->id,
                'nome' => $d->nome,
                'respostas' => (int) $d->respostas,
                'taxa_acerto' => $this->taxa((int) $d->acertos, (int) $d->respostas),
            ])
            ->all();
    }

    /**
     * Professores mais ativos (por questões criadas).
     *
     * @return list<array<string, mixed>>
     */
    private function professoresAtivos(int $escolaId): array
    {
        $professores = User::query()->role('professor')->where('escola_id', $escolaId)->get(['id', 'name']);

        $questoes = Questao::query()
            ->whereIn('professor_id', $professores->pluck('id'))
            ->selectRaw('professor_id, COUNT(*) as total')
            ->groupBy('professor_id')
            ->pluck('total', 'professor_id');

        return $professores
            ->map(fn (User $p) => [
                'id' => $p->id,
                'nome' => $p->name,
                'questoes' => (int) ($questoes[$p->id] ?? 0),
            ])
            ->sortByDesc('questoes')
            ->take(self::LIMITE)
            ->values()
            ->all();
    }

    /**
     * Alunos com maior pontuação da escola.
     *
     * @return list<array<string, mixed>>
     */
    private function topAlunos(int $escolaId): array
    {
        return DB::table('perfis_alunos as p')
            ->join('alunos as a', 'a.id', '=', 'p.aluno_id')
            ->where('a.escola_id', $escolaId)
            ->orderByDesc('p.pontuacao_total')
            ->limit(self::LIMITE)
            ->get(['a.id', 'a.nome', 'p.pontuacao_total as pontuacao', 'p.nivel'])
            ->map(fn ($a) => [
                'id' => (int) $a->id,
                'nome' => $a->nome,
                'pontuacao' => (int) $a->pontuacao,
                'nivel' => (int) $a->nivel,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, int>  $alunoIds
     * @return list<array<string, mixed>>
     */
    private function alunosComDificuldade(Collection $alunoIds): array
    {
        return DB::table('respostas_alunos as r')
            ->join('alunos as a', 'a.id', '=', 'r.aluno_id')
            ->whereIn('r.aluno_id', $alunoIds)
            ->groupBy('a.id', 'a.nome')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_RESPOSTAS])
            ->orderByRaw('AVG(r.correta) asc')
            ->limit(self::LIMITE)
            ->selectRaw('a.id, a.nome, COUNT(*) as respostas, COALESCE(SUM(r.correta),0) as acertos')
            ->get()
            ->map(fn ($a) => [
                'id' => (int) $a->id,
                'nome' => $a->nome,
                'respostas' => (int) $a->respostas,
                'taxa_acerto' => $this->taxa((int) $a->acertos, (int) $a->respostas),
            ])
            ->all();
    }

    private function taxa(int $acertos, int $respostas): int
    {
        return $respostas > 0 ? (int) round(($acertos / $respostas) * 100) : 0;
    }
}
