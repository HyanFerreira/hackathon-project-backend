<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestaoAlternativa extends Model
{
    protected $table = 'questao_alternativas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'questao_id',
        'texto',
        'correta',
    ];

    protected function casts(): array
    {
        return [
            'correta' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Questao, $this>
     */
    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }
}
