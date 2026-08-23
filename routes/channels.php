<?php

/**
 * Agrega esto a tu routes/channels.php.
 *
 * El canal 'chat.{chatId}' es PRIVADO: solo se autoriza si el usuario
 * autenticado es uno de los dos participantes del chat (vía la
 * coincidencia que lo originó). Esto es lo que también protege la
 * señalización de llamadas (whisper), ya que usa este mismo canal.
 */

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{chatId}', function ($user, int $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat || !$chat->tieneParticipante($user->id)) {
        return false;
    }

    // Lo que se devuelve aquí queda disponible como "member info" del
    // canal en el frontend (útil para mostrar "en línea" del otro usuario
    // si más adelante quieres convertir esto en un canal de presencia).
    return [
        'id' => $user->id,
        'nombre' => $user->nombre_completo ?? $user->nombre,
        'avatar' => $user->avatar,
    ];
});