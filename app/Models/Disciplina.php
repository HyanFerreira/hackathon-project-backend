<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disciplina extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'nome',
        'sigla',
        'area',
    ];

    /**
     * @return HasMany<Habilidade, $this>
     */
    public function habilidades(): HasMany
    {
        return $this->hasMany(Habilidade::class);
    }
}
