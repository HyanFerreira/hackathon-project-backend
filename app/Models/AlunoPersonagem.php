<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlunoPersonagem extends Model
{
    protected $table = 'aluno_personagem';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'aluno_id',
        'personagem_id',
        'nivel',
        'questoes_respondidas',
        'equipado',
        'comprado_em',
    ];

    protected function casts(): array
    {
        return [
            'equipado' => 'boolean',
            'comprado_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Personagem, $this>
     */
    public function personagem(): BelongsTo
    {
        return $this->belongsTo(Personagem::class);
    }

    /**
     * @return BelongsTo<Aluno, $this>
     */
    public function aluno(): BelongsTo
    {
        return $this->belongsTo(Aluno::class);
    }
}
