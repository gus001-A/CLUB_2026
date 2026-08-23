<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Indicador de "está escribiendo...". No se guarda en base de datos,
 * solo se retransmite al otro participante del chat.
 */
class UsuarioEscribiendo implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $chatId,
        public int $usuarioId,
        public bool $escribiendo,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chatId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'usuario.escribiendo';
    }

    public function broadcastWith(): array
    {
        return [
            'usuario_id' => $this->usuarioId,
            'escribiendo' => $this->escribiendo,
        ];
    }
}
