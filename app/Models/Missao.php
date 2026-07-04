<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Missao extends Model
{
    protected $table = 'missoes';

    public const TIPO_RESPONDER = 'responder';

    public const TIPO_ACERTAR = 'acertar';

    public const PERIODO_DIARIA = 'diaria';

    public const PERIODO_SEMANAL = 'semanal';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'titulo',
        'descricao',
        'icone',
        'tipo',
        'meta',
        'periodo',
        'recompensa_pontos',
        'recompensa_xp',
        'status',
    ];
}
