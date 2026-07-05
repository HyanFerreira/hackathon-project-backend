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
        'dias_seguidos_login',
        'maior_dias_seguidos_login',
        'ultimo_login_em',
    ];

    protected function casts(): array
    {
        return [
            'energia_atualizada_em' => 'datetime',
            'ultimo_login_em' => 'datetime',
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
