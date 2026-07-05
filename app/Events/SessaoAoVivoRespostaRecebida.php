<?php

namespace App\Events;

use App\Models\SessaoAoVivo;
use App\Models\SessaoAoVivoResposta;
use App\Services\SessaoAoVivo\SessaoAoVivoPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessaoAoVivoRespostaRecebida implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public SessaoAoVivo $sessao,
        public SessaoAoVivoResposta $resposta,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('turma.'.$this->sessao->turma_id),
            new PrivateChannel('professor.'.$this->sessao->professor_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'sessao.resposta';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'sessao' => SessaoAoVivoPayload::sessao($this->sessao),
            'resposta' => [
                'aluno_id' => $this->resposta->aluno_id,
                'questao_id' => $this->resposta->questao_id,
                'correta' => $this->resposta->correta,
                'pontos_ganhos' => $this->resposta->pontos_ganhos,
                'xp_ganho' => $this->resposta->xp_ganho,
                'respondido_em' => $this->resposta->respondido_em?->toIso8601String(),
            ],
            'desempenho' => SessaoAoVivoPayload::desempenho($this->sessao),
        ];
    }
}
