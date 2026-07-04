<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conquista extends Model
{
    /** Tipos de condição para desbloqueio. */
    public const TIPO_RESPONDIDAS = 'questoes_respondidas';

    public const TIPO_ACERTOS = 'acertos';

    public const TIPO_SEQUENCIA = 'sequencia_acertos';

    public const TIPO_PONTOS = 'pontos';

    public const TIPO_NIVEL = 'nivel';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'descricao',
        'icone',
        'tipo',
        'meta',
        'recompensa_pontos',
        'recompensa_xp',
        'status',
    ];

    /**
     * @return BelongsToMany<Aluno, $this>
     */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'aluno_conquista', 'conquista_id', 'aluno_id')
            ->withPivot('desbloqueada_em')
            ->withTimestamps();
    }
}
