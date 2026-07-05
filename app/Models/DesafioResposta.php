<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesafioResposta extends Model
{
    protected $table = 'desafio_respostas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'desafio_id',
        'aluno_id',
        'questao_id',
        'alternativa_id',
        'correta',
        'tempo_resposta_ms',
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
}
