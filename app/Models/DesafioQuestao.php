<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesafioQuestao extends Model
{
    protected $table = 'desafio_questoes';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'desafio_id',
        'questao_id',
        'ordem',
    ];

    /**
     * @return BelongsTo<Desafio, $this>
     */
    public function desafio(): BelongsTo
    {
        return $this->belongsTo(Desafio::class);
    }

    /**
     * @return BelongsTo<Questao, $this>
     */
    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }
}
