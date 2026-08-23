<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Events\LlamadaActualizada;
use App\Models\Chat;
use App\Models\Llamada;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LlamadaController extends Controller
{
    /**
     * POST /chats/{chat}/llamadas
     * Inicia una llamada (audio o video).
     */
    public function store(Request $request, Chat $chat)
    {
        $this->autorizarParticipante($chat);

        $data = $request->validate([
            'tipo' => ['required', 'in:audio,video'],
        ]);

        // Evita dos llamadas activas a la vez en el mismo chat.
        $yaHayLlamadaActiva = $chat->llamadas()
            ->whereIn('estado', ['sonando', 'en_curso'])
            ->exists();

        abort_if($yaHayLlamadaActiva, 409, 'Ya hay una llamada en curso en este chat.');

        $receptor = $chat->otroParticipante(Auth::id());
        abort_if(!$receptor, 422, 'No se pudo determinar al destinatario de la llamada.');

        $llamada = Llamada::create([
            'chat_id' => $chat->id,
            'llamante_id' => Auth::id(),
            'receptor_id' => $receptor->id,
            'tipo' => $data['tipo'],
            'estado' => 'sonando',
            'iniciada_en' => now(),
        ]);

        $llamada->load(['llamante', 'receptor']);
        broadcast(new LlamadaActualizada($llamada))->toOthers();

        return response()->json(['llamada' => $llamada->toChatPayload()], 201);
    }

    /**
     * POST /llamadas/{llamada}/contestar
     */
    public function contestar(Llamada $llamada)
    {
        $this->autorizarParticipante($llamada->chat);
        abort_unless($llamada->receptor_id === Auth::id(), 403, 'Solo el destinatario puede contestar.');
        abort_unless($llamada->estado === 'sonando', 409, 'Esta llamada ya no está disponible.');

        $llamada->update([
            'estado' => 'en_curso',
            'contestada_en' => now(),
        ]);

        $llamada->load(['llamante', 'receptor']);
        broadcast(new LlamadaActualizada($llamada))->toOthers();

        return response()->json(['llamada' => $llamada->toChatPayload()]);
    }

    /**
     * POST /llamadas/{llamada}/colgar
     * Sirve tanto para colgar una llamada en curso, como para rechazar
     * una que está sonando, o para marcarla como perdida si nadie contestó.
     */
    public function colgar(Request $request, Llamada $llamada)
    {
        $this->autorizarParticipante($llamada->chat);

        $data = $request->validate([
            'motivo' => ['nullable', 'in:colgada,rechazada,sin_respuesta,error'],
        ]);

        if (in_array($llamada->estado, ['finalizada', 'rechazada', 'perdida'])) {
            return response()->json(['llamada' => $llamada->toChatPayload()]);
        }

        $motivo = $data['motivo'] ?? ($llamada->estado === 'sonando' ? 'rechazada' : 'colgada');
        $estadoFinal = match ($motivo) {
            'rechazada' => 'rechazada',
            'sin_respuesta' => 'perdida',
            default => 'finalizada',
        };

        $duracion = $llamada->contestada_en
            ? now()->diffInSeconds($llamada->contestada_en)
            : null;

        $llamada->update([
            'estado' => $estadoFinal,
            'finalizada_en' => now(),
            'duracion_segundos' => $duracion,
            'motivo_fin' => $motivo,
        ]);

        $llamada->load(['llamante', 'receptor']);

        // ✅ CREAR NOTIFICACIÓN DE LLAMADA PERDIDA
        if ($estadoFinal === 'perdida' || ($estadoFinal === 'rechazada' && $motivo === 'sin_respuesta')) {
            $this->crearNotificacionLlamadaPerdida($llamada);
        }

        broadcast(new LlamadaActualizada($llamada))->toOthers();

        return response()->json(['llamada' => $llamada->toChatPayload()]);
    }

    /**
     * ✅ Crear notificación de llamada perdida
     */
    private function crearNotificacionLlamadaPerdida(Llamada $llamada): void
    {
        try {
            $llamante = $llamada->llamante;
            $receptor = $llamada->receptor;

            if (!$llamante || !$receptor) {
                Log::warning('No se pudo crear notificación de llamada perdida: faltan datos', [
                    'llamada_id' => $llamada->id,
                ]);
                return;
            }

            $tipoNotificacion = $llamada->tipo === 'video' ? 'videollamada_perdida' : 'llamada_perdida';
            $nombreLlamada = $llamada->tipo === 'video' ? 'videollamada' : 'llamada';

            Notificacion::crear(
                usuarioId: $receptor->id,
                emisorId: $llamante->id,
                tipo: $tipoNotificacion,
                mensaje: "Tienes una {$nombreLlamada} perdida de <strong>{$llamante->nombre}</strong>",
                link: '/mensajes',
            );

            Log::info('Notificación de llamada perdida creada', [
                'llamada_id' => $llamada->id,
                'receptor_id' => $receptor->id,
                'emisor_id' => $llamante->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear notificación de llamada perdida:', [
                'message' => $e->getMessage(),
                'llamada_id' => $llamada->id,
            ]);
        }
    }

    private function autorizarParticipante(Chat $chat): void
    {
        abort_unless($chat->tieneParticipante(Auth::id()), 403, 'No tienes acceso a esta conversación.');
    }
}