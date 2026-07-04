<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfilAluno extends Model
{
    protected $table = 'perfis_alunos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'aluno_id',
        'pontos',
        'pontuacao_total',
        'xp',
        'nivel',
        'energia',
        'energia_maxima',
        'energia_atualizada_em',
    ];

    protected function casts(): array
    {
        return [
            'energia_atualizada_em' => 'datetime',
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
