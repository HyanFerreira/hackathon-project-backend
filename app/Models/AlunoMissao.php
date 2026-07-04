<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlunoMissao extends Model
{
    protected $table = 'aluno_missao';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'aluno_id',
        'missao_id',
        'referencia',
        'progresso',
        'concluida',
        'concluida_em',
    ];

    protected function casts(): array
    {
        return [
            'concluida' => 'boolean',
            'concluida_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Missao, $this>
     */
    public function missao(): BelongsTo
    {
        return $this->belongsTo(Missao::class);
    }
}
