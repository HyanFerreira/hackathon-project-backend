<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessaoAoVivoResposta extends Model
{
    protected $table = 'sessao_ao_vivo_respostas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sessao_ao_vivo_id',
        'sessao_ao_vivo_questao_id',
        'questao_id',
        'aluno_id',
        'alternativa_id',
        'correta',
        'tempo_resposta_ms',
        'pontos_ganhos',
        'xp_ganho',
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
     * @return BelongsTo<SessaoAoVivo, $this>
     */
    public function sessao(): BelongsTo
    {
        return $this->belongsTo(SessaoAoVivo::class, 'sessao_ao_vivo_id');
    }

    /**
     * @return BelongsTo<SessaoAoVivoQuestao, $this>
     */
    public function sessaoQuestao(): BelongsTo
    {
        return $this->belongsTo(SessaoAoVivoQuestao::class, 'sessao_ao_vivo_questao_id');
    }

    /**
     * @return BelongsTo<Questao, $this>
     */
    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }

    /**
     * @return BelongsTo<Aluno, $this>
     */
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }

    /**
     * @return BelongsTo<QuestaoAlternativa, $this>
     */
    public function alternativa(): BelongsTo
    {
        return $this->belongsTo(QuestaoAlternativa::class, 'alternativa_id');
    }
}
