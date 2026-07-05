<?php

namespace App\Services\Professor;

use App\Models\Questao;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Análise de desempenho dos alunos das turmas de um professor,
 * a partir das respostas (respostas_alunos).
 */
class DesempenhoService
{
    /** Mínimo de respostas para um item entrar nos rankings de desempenho. */
    private const MIN_RESPOSTAS = 1;

    private const LIMITE = 10;

    /**
     * @return array<string, mixed>
     */
    public function paraProfessor(User $professor): array
    {
        $turmaIds = $professor->turmas()->pluck('turmas.id');
        $alunoIds = DB::table('aluno_turma')
            ->whereIn('turma_id', $turmaIds)
            ->distinct()
            ->pluck('aluno_id');

        return [
            'resumo' => $this->resumo($professor, $alunoIds),
            'por_turma' => $this->porTurma($turmaIds),
            'habilidades_dificeis' => $this->habilidadesDificeis($alunoIds),
            'disciplinas' => $this->porDisciplina($alunoIds),
            'questoes_mais_erradas' => $this->questoesMaisErradas($alunoIds),
            'alunos_com_dificuldade' => $this->alunosComDificuldade($alunoIds),
        ];
    }

    /**
     * @param  Collection<int, int>  $alunoIds
     * @return array<string, mixed>
     */
    private function resumo(User $professor, Collection $alunoIds): array
    {
        $agg = DB::table('respostas_alunos')
            ->whereIn('aluno_id', $alunoIds)
            ->selectRaw('COUNT(*) as respostas, COALESCE(SUM(correta),0) as acertos, COUNT(DISTINCT aluno_id) as ativos')
            ->first();

        return [
            'turmas' => $professor->turmas()->count(),
            'alunos' => $alunoIds->count(),
            'alunos_ativos' => (int) ($agg->ativos ?? 0),
            'respostas' => (int) ($agg->respostas ?? 0),
            'acertos' => (int) ($agg->acertos ?? 0),
            'taxa_acerto' => $this->taxa((int) ($agg->acertos ?? 0), (int) ($agg->respostas ?? 0)),
            'questoes_criadas' => Questao::query()->where('professor_id', $professor->id)->count(),
        ];
    }

    /**
     * @param  Collection<int, int>  $turmaIds
     * @return list<array<string, mixed>>
     */
    private function porTurma(Collection $turmaIds): array
    {
        return DB::table('turmas as t')
            ->leftJoin('aluno_turma as at', 'at.turma_id', '=', 't.id')
            ->leftJoin('respostas_alunos as r', 'r.aluno_id', '=', 'at.aluno_id')
            ->whereIn('t.id', $turmaIds)
            ->groupBy('t.id', 't.nome')
            ->orderBy('t.nome')
            ->selectRaw('t.id, t.nome, COUNT(DISTINCT at.aluno_id) as alunos, COUNT(r.id) as respostas, COALESCE(SUM(r.correta),0) as acertos')
            ->get()
            ->map(fn ($t) => [
                'turma_id' => (int) $t->id,
                'nome' => $t->nome,
                'alunos' => (int) $t->alunos,
                'respostas' => (int) $t->respostas,
                'taxa_acerto' => $this->taxa((int) $t->acertos, (int) $t->respostas),
            ])
            ->all();
    }

    /**
     * Habilidades da BNCC em que os alunos vão pior (menor taxa de acerto).
     *
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
     * @param  Collection<int, int>  $alunoIds
     * @return list<array<string, mixed>>
     */
    private function questoesMaisErradas(Collection $alunoIds): array
    {
        return DB::table('respostas_alunos as r')
            ->join('questoes as q', 'q.id', '=', 'r.questao_id')
            ->whereIn('r.aluno_id', $alunoIds)
            ->groupBy('q.id', 'q.enunciado')
            ->havingRaw('COUNT(*) >= ?', [self::MIN_RESPOSTAS])
            ->orderByRaw('AVG(r.correta) asc')
            ->limit(self::LIMITE)
            ->selectRaw('q.id, q.enunciado, COUNT(*) as respostas, COALESCE(SUM(r.correta),0) as acertos')
            ->get()
            ->map(fn ($q) => [
                'id' => (int) $q->id,
                'enunciado' => $q->enunciado,
                'respostas' => (int) $q->respostas,
                'acertos' => (int) $q->acertos,
                'taxa_acerto' => $this->taxa((int) $q->acertos, (int) $q->respostas),
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
