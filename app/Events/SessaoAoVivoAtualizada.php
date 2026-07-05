<?php

namespace App\Events;

use App\Models\SessaoAoVivo;
use App\Services\SessaoAoVivo\SessaoAoVivoPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessaoAoVivoAtualizada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SessaoAoVivo $sessao) {}

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
        return 'sessao.atualizada';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return SessaoAoVivoPayload::estadoProfessor($this->sessao);
    }
}
