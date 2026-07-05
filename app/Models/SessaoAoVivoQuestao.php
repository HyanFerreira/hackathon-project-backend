<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SessaoAoVivoQuestao extends Model
{
    protected $table = 'sessao_ao_vivo_questoes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sessao_ao_vivo_id',
        'questao_id',
        'ordem',
        'atual',
        'enviada_em',
        'encerrada_em',
    ];

    protected function casts(): array
    {
        return [
            'atual' => 'boolean',
            'enviada_em' => 'datetime',
            'encerrada_em' => 'datetime',
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
     * @return BelongsTo<Questao, $this>
     */
    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }

    /**
     * @return HasMany<SessaoAoVivoResposta, $this>
     */
    public function respostas(): HasMany
    {
        return $this->hasMany(SessaoAoVivoResposta::class);
    }
}
