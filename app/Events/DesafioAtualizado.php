<?php

namespace App\Events;

use App\Http\Resources\Desafio\DesafioResource;
use App\Models\Desafio;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Mudança de estado do desafio (aceito/recusado/iniciado/finalizado)
 * → notifica os dois alunos em tempo real.
 */
class DesafioAtualizado implements ShouldBroadcast
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
        return 'desafio.atualizado';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $desafio = $this->desafio->loadMissing(['desafiante', 'desafiado']);

        return ['desafio' => (new DesafioResource($desafio))->resolve()];
    }
}
