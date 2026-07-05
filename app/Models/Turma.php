<?php

namespace App\Models;

use Database\Factories\TurmaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Turma extends Model
{
    /** @use HasFactory<TurmaFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'escola_id',
        'nome',
        'ano',
        'turno',
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
     * Professores vinculados à turma (usuários com a role professor).
     *
     * @return BelongsToMany<User, $this>
     */
    public function professores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'professor_turma', 'turma_id', 'professor_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Aluno, $this>
     */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'aluno_turma', 'turma_id', 'aluno_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<SessaoAoVivo, $this>
     */
    public function sessoesAoVivo(): HasMany
    {
        return $this->hasMany(SessaoAoVivo::class);
    }
}
