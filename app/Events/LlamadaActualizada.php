<?php

namespace App\Events;

use App\Models\Llamada;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se dispara en cada cambio de estado de una llamada: sonando -> en_curso
 * -> finalizada/rechazada/perdida. La señalización WebRTC en sí (SDP
 * offer/answer, ICE candidates) NO pasa por aquí — eso va por
 * Echo.whisper() directamente entre los dos navegadores para que sea
 * instantáneo. Este evento es solo para que la UI (modal de llamada,
 * historial, "llamada perdida" en el chat) se mantenga sincronizada
 * incluso si alguno de los dos no estaba conectado al whisper todavía.
 */
class LlamadaActualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Llamada $llamada)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->llamada->chat_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'llamada.actualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'llamada' => $this->llamada->toChatPayload(),
        ];
    }
}
