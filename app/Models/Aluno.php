<?php

namespace App\Models;

use Database\Factories\AlunoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Aluno extends Authenticatable
{
    /** @use HasFactory<AlunoFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * O guard de autenticação usado por este model.
     */
    protected string $guard_name = 'aluno';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'escola_id',
        'nome',
        'codigo',
    ];

    /**
     * @return BelongsTo<Escola, $this>
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * @return BelongsToMany<Turma, $this>
     */
    public function turmas(): BelongsToMany
    {
        return $this->belongsToMany(Turma::class, 'aluno_turma', 'aluno_id', 'turma_id')
            ->withTimestamps();
    }
}
