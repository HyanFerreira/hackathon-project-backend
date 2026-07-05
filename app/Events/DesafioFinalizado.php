<?php

namespace App\Events;

use App\Models\Desafio;
use App\Services\Desafio\DesafioPayload;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fim da partida → os dois alunos recebem o resultado (placar + vencedor).
 */
class DesafioFinalizado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Desafio $desafio) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('aluno.'.$this->desafio->desafiante_id),
            new PrivateChannel('aluno.'.$this->desafio->desafiado_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'desafio.finalizado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return DesafioPayload::resultado($this->desafio);
    }
}
