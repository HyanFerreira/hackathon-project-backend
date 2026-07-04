<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Habilidade extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'disciplina_id',
        'codigo',
        'descricao',
        'etapa',
        'ano',
    ];

    /**
     * @return BelongsTo<Disciplina, $this>
     */
    public function disciplina(): BelongsTo
    {
        return $this->belongsTo(Disciplina::class);
    }

    /**
     * @return BelongsToMany<Questao, $this>
     */
    public function questoes(): BelongsToMany
    {
        return $this->belongsToMany(Questao::class, 'habilidade_questao', 'habilidade_id', 'questao_id')
            ->withTimestamps();
    }
}
