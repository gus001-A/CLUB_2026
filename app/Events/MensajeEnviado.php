<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se dispara cada vez que se guarda un mensaje nuevo (texto, imagen, video
 * o audio). ShouldBroadcastNow en vez de ShouldBroadcast: en un chat
 * queremos que salga de inmediato, sin esperar el ciclo de la cola.
 *
 * 🔧 FIX: antes recibía el modelo Mensaje completo y llamaba a
 * $mensaje->toChatPayload() dentro de broadcastWith() — pero
 * ChatController nunca usa ese método (usa su propio formatMensaje()
 * privado), así que probablemente no existe en el modelo actual y el
 * broadcast fallaba en silencio. Ahora recibe directamente el arreglo
 * YA FORMATEADO (el mismo que arma formatMensaje()), garantizando que el
 * payload que llega por WebSocket sea IDÉNTICO al que ya recibe el propio
 * remitente en la respuesta HTTP normal.
 */
class MensajeEnviado implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $mensajeData)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->mensajeData['chat_id']),
        ];
    }

    public function broadcastAs(): string
    {
        return 'mensaje.enviado';
    }

    public function broadcastWith(): array
    {
        return [
            'mensaje' => $this->mensajeData,
        ];
    }
}