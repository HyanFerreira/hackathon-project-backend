<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questao extends Model
{
    protected $table = 'questoes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'escola_id',
        'professor_id',
        'enunciado',
        'dificuldade',
        'pontos',
        'status',
    ];

    /**
     * @return BelongsTo<Escola, $this>
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /**
     * @return HasMany<QuestaoAlternativa, $this>
     */
    public function alternativas(): HasMany
    {
        return $this->hasMany(QuestaoAlternativa::class);
    }

    /**
     * @return HasMany<RespostaAluno, $this>
     */
    public function respostas(): HasMany
    {
        return $this->hasMany(RespostaAluno::class);
    }

    /**
     * @return BelongsToMany<Habilidade, $this>
     */
    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(Habilidade::class, 'habilidade_questao', 'questao_id', 'habilidade_id')
            ->withTimestamps();
    }
}
