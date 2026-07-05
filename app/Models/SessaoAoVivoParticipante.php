<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessaoAoVivoParticipante extends Model
{
    protected $table = 'sessao_ao_vivo_participantes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sessao_ao_vivo_id',
        'aluno_id',
        'entrou_em',
        'saiu_em',
    ];

    protected function casts(): array
    {
        return [
            'entrou_em' => 'datetime',
            'saiu_em' => 'datetime',
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
     * @return BelongsTo<Aluno, $this>
     */
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}
