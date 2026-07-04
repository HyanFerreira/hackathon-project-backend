<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespostaAluno extends Model
{
    protected $table = 'respostas_alunos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'aluno_id',
        'questao_id',
        'alternativa_id',
        'correta',
        'pontos_ganhos',
        'xp_ganho',
        'energia_gasta',
        'respondido_em',
    ];

    protected function casts(): array
    {
        return [
            'correta' => 'boolean',
            'respondido_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Aluno, $this>
     */
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    /**
     * @return BelongsTo<Questao, $this>
     */
    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }

    /**
     * @return BelongsTo<QuestaoAlternativa, $this>
     */
    public function alternativa(): BelongsTo
    {
        return $this->belongsTo(QuestaoAlternativa::class, 'alternativa_id');
    }
}
