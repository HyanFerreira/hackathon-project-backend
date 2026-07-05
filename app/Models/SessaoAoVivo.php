<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessaoAoVivo extends Model
{
    public const STATUS_AGUARDANDO = 'aguardando';

    public const STATUS_EM_ANDAMENTO = 'em_andamento';

    public const STATUS_PAUSADA = 'pausada';

    public const STATUS_FINALIZADA = 'finalizada';

    public const STATUS_CANCELADA = 'cancelada';

    /**
     * Segundos sem heartbeat do professor até a sessão ser encerrada.
     */
    public const HEARTBEAT_TTL_SEGUNDOS = 45;

    protected $table = 'sessoes_ao_vivo';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'escola_id',
        'turma_id',
        'professor_id',
        'titulo',
        'status',
        'iniciada_em',
        'pausada_em',
        'finalizada_em',
        'professor_online_em',
        'motivo_encerramento',
    ];

    protected function casts(): array
    {
        return [
            'iniciada_em' => 'datetime',
            'pausada_em' => 'datetime',
            'finalizada_em' => 'datetime',
            'professor_online_em' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Escola, $this>
     */
    public function escola(): BelongsTo
    {
        return $this->belongsTo(Escola::class);
    }

    /**
     * @return BelongsTo<Turma, $this>
     */
    public function turma(): BelongsTo
    {
        return $this->belongsTo(Turma::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /**
     * @return HasMany<SessaoAoVivoQuestao, $this>
     */
    public function questoes(): HasMany
    {
        return $this->hasMany(SessaoAoVivoQuestao::class)->orderBy('ordem');
    }

    /**
     * @return HasOne<SessaoAoVivoQuestao, $this>
     */
    public function questaoAtual(): HasOne
    {
        return $this->hasOne(SessaoAoVivoQuestao::class)->where('atual', true);
    }

    /**
     * @return HasMany<SessaoAoVivoParticipante, $this>
     */
    public function participantes(): HasMany
    {
        return $this->hasMany(SessaoAoVivoParticipante::class);
    }

    /**
     * @return HasMany<SessaoAoVivoResposta, $this>
     */
    public function respostas(): HasMany
    {
        return $this->hasMany(SessaoAoVivoResposta::class);
    }

    public function ativa(): bool
    {
        return in_array($this->status, [
            self::STATUS_AGUARDANDO,
            self::STATUS_EM_ANDAMENTO,
            self::STATUS_PAUSADA,
        ], true);
    }
}
