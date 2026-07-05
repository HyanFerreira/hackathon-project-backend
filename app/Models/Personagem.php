<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Personagem extends Model
{
    protected $table = 'personagens';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'chave',
        'avatar',
        'nome',
        'descricao',
        'tier',
        'preco',
        'nivel_maximo',
        'status',
    ];

    /**
     * Nome do arquivo de imagem para um nível (armazenado no frontend).
     */
    public function imagem(int $nivel): string
    {
        return "{$this->chave}_level_{$nivel}.svg";
    }

    /**
     * @return BelongsToMany<Aluno, $this>
     */
    public function alunos(): BelongsToMany
    {
        return $this->belongsToMany(Aluno::class, 'aluno_personagem', 'personagem_id', 'aluno_id')
            ->withPivot(['nivel', 'questoes_respondidas', 'equipado', 'comprado_em'])
            ->withTimestamps();
    }
}
