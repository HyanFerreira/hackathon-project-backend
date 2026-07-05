<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desafio extends Model
{
    public const TIPO_AMISTOSO = 'amistoso';

    public const TIPO_VALENDO = 'valendo';

    public const STATUS_PENDENTE = 'pendente';

    public const STATUS_RECUSADO = 'recusado';

    public const STATUS_EM_ANDAMENTO = 'em_andamento';

    public const STATUS_FINALIZADO = 'finalizado';

    public const STATUS_CANCELADO = 'cancelado';

    public const STATUS_EXPIRADO = 'expirado';

    /** Tempo (em segundos) que cada questão fica disponível na partida ao vivo. */
    public const SEGUNDOS_POR_QUESTAO = 20;

    public function questaoAtualId(): ?int
    {
        return $this->questoes()->where('ordem', $this->questao_atual)->value('questao_id');
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'escola_id',
        'turma_id',
        'desafiante_id',
        'desafiado_id',
        'disciplina_id',
        'tipo',
        'status',
        'quantidade_questoes',
        'questao_atual',
        'questao_iniciada_em',
        'vencedor_id',
        'expira_em',
        'iniciado_em',
        'finalizado_em',
    ];

    protected function casts(): array
    {
        return [
            'questao_iniciada_em' => 'datetime',
            'expira_em' => 'datetime',
            'iniciado_em' => 'datetime',
            'finalizado_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Aluno, $this>
     */
    public function desafiante(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'desafiante_id');
    }

    /**
     * @return BelongsTo<Aluno, $this>
     */
    public function desafiado(): BelongsTo
    {
        return $this->belongsTo(Aluno::class, 'desafiado_id');
    }

    /**
     * @return BelongsTo<Disciplina, $this>
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    /**
     * @return HasMany<DesafioQuestao, $this>
     */
    public function questoes(): HasMany
    {
        return $this->hasMany(DesafioQuestao::class)->orderBy('ordem');
    }

    /**
     * @return HasMany<DesafioResposta, $this>
     */
    public function respostas(): HasMany
    {
        return $this->hasMany(DesafioResposta::class);
    }

    public function envolve(Aluno $aluno): bool
    {
        return (int) $this->desafiante_id === (int) $aluno->id
            || (int) $this->desafiado_id === (int) $aluno->id;
    }
}
