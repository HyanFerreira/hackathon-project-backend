<?php

namespace App\Models;

use Database\Factories\EscolaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escola extends Model
{
    /** @use HasFactory<EscolaFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'cnpj',
        'cidade',
        'estado',
        'status',
    ];

    /**
     * @return HasMany<Turma, $this>
     */
    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class);
    }

    /**
     * @return HasMany<Aluno, $this>
     */
    public function alunos(): HasMany
    {
        return $this->hasMany(Aluno::class);
    }

    /**
     * Usuários (gestores/professores) vinculados à escola.
     *
     * @return HasMany<User, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
