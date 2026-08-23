<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Avisa al otro participante que sus mensajes ya fueron leídos, para
 * pintar los checks de "leído" en tiempo real sin recargar nada.
 */
class MensajeLeido implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param int $chatId
     * @param int $leidoPorId  id del usuario que acaba de leer
     * @param array<int> $mensajeIds  ids de los mensajes marcados como leídos
     */
    public function __construct(
        public int $chatId,
        public int $leidoPorId,
        public array $mensajeIds,
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
        return 'mensaje.leido';
    }

    public function broadcastWith(): array
    {
        return [
            'chat_id' => $this->chatId,
            'leido_por_id' => $this->leidoPorId,
            'mensaje_ids' => $this->mensajeIds,
            'leido_en' => now()->toIso8601String(),
        ];
    }
}
