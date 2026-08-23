<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Chat;
use App\Models\Mensaje;
use App\Models\Coincidencia;
use App\Models\Fotos;
use App\Models\Notificacion;
use App\Events\MensajeEnviado;
use App\Events\UsuarioEscribiendo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ChatController extends Controller
{
    /**
     * Muestra la página de mensajes
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!$user) {
            Auth::logout();
            return redirect()->route('login');
        }

        try {
            // Verificar que el usuario tenga un perfil
            $perfil = Perfil::where('usuario_id', $user->id)->first();
            
            // Si no tiene perfil, redirigir a completar perfil
            if (!$perfil) {
                return redirect()->route('perfil.completar')->with('flash', [
                    'toast' => [
                        'type' => 'info',
                        'title' => 'Completa tu perfil',
                        'message' => 'Para acceder a tus mensajes, primero completa tu perfil.',
                        'duration' => 5000,
                    ]
                ]);
            }

            // Obtener conversaciones del usuario
            $conversaciones = $this->getConversaciones($user);
            
            // Obtener mensajes de la primera conversación (si existe)
            $mensajes = [];
            if (!empty($conversaciones)) {
                $mensajes = $this->getMensajes($conversaciones[0]['id']);
            }

            // Datos del usuario para la vista
            $usuarioData = [
                'id' => $user->id,
                'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                'apodo' => $user->apodo ?? $user->nombre ?? 'Usuario',
                'avatar' => $user->avatar,
                'verificado' => ($user->estado === 'verificado' || $user->estado === 'pendiente'),
                'rol' => $user->rol ?? 'usuario',
                'estado' => $user->estado ?? 'incompleto',
            ];

            Log::info('Cargando página de mensajes', [
                'user_id' => $user->id,
                'conversaciones_encontradas' => count($conversaciones)
            ]);

            return Inertia::render('Usuario/Mensajes', [
                'usuario' => $usuarioData,
                'conversaciones' => $conversaciones,
                'mensajes' => $mensajes,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar página de mensajes', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);

            return redirect()->route('inicio')->with('flash', [
                'toast' => [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'No se pudieron cargar los mensajes. Por favor, intenta nuevamente.',
                    'duration' => 5000,
                ]
            ]);
        }
    }

    /**
     * Obtiene las conversaciones del usuario
     */
    protected function getConversaciones($user)
    {
        try {
            // Obtener chats donde el usuario tiene coincidencias
            $chats = Chat::whereHas('coincidencia', function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->with(['coincidencia.usuarioA', 'coincidencia.usuarioB', 
                        'coincidencia.usuarioA.perfil', 'coincidencia.usuarioB.perfil',
                        'coincidencia.usuarioA.perfil.fotos', 'coincidencia.usuarioB.perfil.fotos'])
                ->orderBy('ultimo_mensaje_en', 'desc')
                ->get();

            $resultados = [];

            foreach ($chats as $chat) {
                $coincidencia = $chat->coincidencia;
                if (!$coincidencia) continue;

                // Determinar el otro usuario
                $otroUsuario = $coincidencia->usuario_a_id === $user->id 
                    ? $coincidencia->usuarioB 
                    : $coincidencia->usuarioA;

                if (!$otroUsuario) continue;

                $perfil = $otroUsuario->perfil;
                
                // Obtener avatar del otro usuario
                $avatar = $this->getAvatarFromUser($otroUsuario, $perfil);
                
                // Obtener nombre del otro usuario
                $nombre = $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario';
                $edad = $otroUsuario->fecha_nacimiento ? Carbon::parse($otroUsuario->fecha_nacimiento)->age : null;
                
                // Formatear nombre según tipo de perfil
                $nombreMostrar = $nombre;
                if ($perfil && $perfil->tipo === 'pareja') {
                    $nombreMostrar = $nombre . ' & ' . ($edad ? $edad . ' & ' . ($edad + rand(-2, 2)) : '');
                } elseif ($edad) {
                    $nombreMostrar = $nombre . ', ' . $edad;
                }

                // Obtener último mensaje
                $ultimoMensaje = $chat->ultimo_mensaje;
                $preview = $ultimoMensaje ? $ultimoMensaje->texto : 'Sin mensajes';
                $tiempo = $ultimoMensaje ? $this->formatearTiempo($ultimoMensaje->created_at) : 'Recién';
                
                // Contar mensajes no leídos
                $noLeidos = Mensaje::where('chat_id', $chat->id)
                    ->where('remitente_id', '!=', $user->id)
                    ->where('leido', false)
                    ->count();

                // Obtener intereses del perfil
                $intereses = [];
                if ($perfil && isset($perfil->intereses) && is_array($perfil->intereses)) {
                    $intereses = $this->formatearIntereses($perfil->intereses);
                }

                $resultados[] = [
                    'id' => $chat->id,
                    'usuario' => [
                        'id' => $otroUsuario->id,
                        'nombre' => $nombreMostrar,
                        'avatar' => $avatar,
                        'verificado' => $perfil && $perfil->esta_verificado ?? false,
                    ],
                    'enLinea' => $otroUsuario->esta_activo ?? false,
                    'ultimo_mensaje' => $preview,
                    'tiempo' => $tiempo,
                    'no_leidos' => $noLeidos,
                    'ciudad' => $perfil->ubicacion_ciudad ?? $otroUsuario->ciudad ?? 'Ciudad no especificada',
                    'distancia' => 'A ' . rand(1, 5) . ' km de ti',
                    'compatibilidad' => $coincidencia->compatibilidad ?? 85,
                    'intereses' => $intereses,
                    'sobre' => $perfil->descripcion ?? 'Sin descripción disponible.',
                    'coincidencia_id' => $coincidencia->id,
                    'usuario_id' => $otroUsuario->id,
                ];
            }

            // Si no hay conversaciones, devolver array vacío
            return $resultados;

        } catch (\Exception $e) {
            Log::error('Error al obtener conversaciones', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return [];
        }
    }

    /**
     * Obtiene los mensajes de una conversación específica
     */
    public function getMensajes($chatId)
    {
        try {
            $user = Auth::user();
            
            // Marcar mensajes como leídos
            Mensaje::where('chat_id', $chatId)
                ->where('remitente_id', '!=', $user->id)
                ->where('leido', false)
                ->update([
                    'leido' => true,
                    'leido_en' => now()
                ]);

            // Obtener mensajes del chat
            $mensajes = Mensaje::where('chat_id', $chatId)
                ->with('remitente')
                ->orderBy('created_at', 'asc')
                ->get();

            $resultados = [];

            foreach ($mensajes as $mensaje) {
                $remitente = $mensaje->remitente;
                $esRemitente = $mensaje->remitente_id === $user->id;
                
                $avatar = $remitente ? $this->getAvatarFromUser($remitente) : '/images/shared/avatar-default.jpg';
                $nombre = $remitente ? ($remitente->nombre ?? $remitente->apodo ?? 'Usuario') : 'Usuario';

                $resultados[] = [
                    'id' => $mensaje->id,
                    'remitente_id' => $mensaje->remitente_id,
                    'texto' => $mensaje->texto,
                    'tipo' => $mensaje->tipo ?? 'texto',
                    'archivo_url' => $mensaje->archivo_path ? Storage::url($mensaje->archivo_path) : null,
                    'archivo_nombre_original' => $mensaje->archivo_nombre_original,
                    'duracion_segundos' => $mensaje->duracion_segundos,
                    'duracion_formateada' => $mensaje->duracion_segundos ? $this->formatDuracion($mensaje->duracion_segundos) : null,
                    'esRemitente' => $esRemitente,
                    'nombre' => $nombre,
                    'avatar' => $avatar,
                    'tiempo' => $this->formatearTiempo($mensaje->created_at),
                    'fecha' => $mensaje->created_at->format('H:i'),
                    'created_at' => $mensaje->created_at->toIso8601String(),
                    'leido' => $mensaje->leido,
                    'leido_en' => $mensaje->leido_en,
                    'archivos_adjuntos' => $mensaje->archivos_adjuntos ?? [],
                ];
            }

            return $resultados;

        } catch (\Exception $e) {
            Log::error('Error al obtener mensajes', [
                'message' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return [];
        }
    }

    /**
     * GET /chats
     * Lista de conversaciones del usuario autenticado (API)
     */
    public function chatsList()
    {
        try {
            $user = Auth::user();
            $conversaciones = $this->getConversaciones($user);
            
            return response()->json([
                'chats' => $conversaciones
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener lista de chats', [
                'message' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'chats' => []
            ], 500);
        }
    }

    /**
     * GET /chats/{chat}/mensajes
     * Obtener mensajes de un chat (API)
     */
    public function show(Request $request, Chat $chat)
    {
        try {
            $user = Auth::user();
            
            if (!$chat->tieneParticipante($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este chat'
                ], 403);
            }

            // Marcar mensajes como leídos
            Mensaje::where('chat_id', $chat->id)
                ->where('remitente_id', '!=', $user->id)
                ->where('leido', false)
                ->update([
                    'leido' => true,
                    'leido_en' => now()
                ]);

            $mensajes = Mensaje::where('chat_id', $chat->id)
                ->with('remitente')
                ->orderBy('created_at', 'asc')
                ->get();

            $resultados = [];
            foreach ($mensajes as $mensaje) {
                $resultados[] = $this->formatMensaje($mensaje, $user);
            }

            // Obtener el otro participante
            $coincidencia = $chat->coincidencia;
            if (!$coincidencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coincidencia no encontrada'
                ], 404);
            }

            $otroUsuario = $coincidencia->usuario_a_id === $user->id
                ? $coincidencia->usuarioB
                : $coincidencia->usuarioA;

            return response()->json([
                'mensajes' => $resultados,
                'otro_participante' => $otroUsuario ? [
                    'id' => $otroUsuario->id,
                    'nombre' => $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario',
                    'avatar' => $otroUsuario->avatar,
                    'verificado' => $otroUsuario->estado === 'verificado' || $otroUsuario->estado === 'pendiente',
                ] : null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener mensajes en show', [
                'message' => $e->getMessage(),
                'chat_id' => $chat->id,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener mensajes'
            ], 500);
        }
    }

    /**
     * POST /chats/{chat}/mensajes
     * Envía un mensaje (texto o archivo)
     */
    public function store(Request $request, Chat $chat)
    {
        try {
            $user = Auth::user();
            
            // Verificar que el usuario es participante del chat
            if (!$chat->tieneParticipante($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este chat'
                ], 403);
            }

            // Validar que hay mensaje o archivo
            $request->validate([
                'texto' => 'nullable|string|max:5000',
                'archivo' => 'nullable|file|max:51200', // 50MB
            ]);

            if (empty($request->texto) && !$request->hasFile('archivo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes escribir un mensaje o adjuntar un archivo'
                ], 422);
            }

            $data = [
                'chat_id' => $chat->id,
                'remitente_id' => $user->id,
                'texto' => $request->texto,
                'tipo' => 'texto',
                'leido' => false,
                'estado' => 'enviado',
            ];

            // Procesar archivo si existe
            if ($request->hasFile('archivo')) {
                $archivo = $request->file('archivo');
                $mimeType = $archivo->getMimeType();
                
                if (str_starts_with($mimeType, 'image/')) {
                    $tipo = 'imagen';
                    $carpeta = 'chat/imagenes';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $tipo = 'video';
                    $carpeta = 'chat/videos';
                } elseif (str_starts_with($mimeType, 'audio/')) {
                    $tipo = 'audio';
                    $carpeta = 'chat/audios';
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de archivo no soportado'
                    ], 422);
                }

                $path = $archivo->store($carpeta, 'public');
                $data['tipo'] = $tipo;
                $data['archivo_path'] = $path;
                $data['archivo_nombre_original'] = $archivo->getClientOriginalName();
                $data['archivo_tamano_bytes'] = $archivo->getSize();
            }

            $mensaje = Mensaje::create($data);
            
            // Actualizar último mensaje del chat
            $chat->update(['ultimo_mensaje_en' => now()]);

            // Cargar remitente
            $mensaje->load('remitente');

            // Formateamos UNA vez y reutilizamos el mismo arreglo tanto para
            // la respuesta HTTP (abajo) como para el broadcast — así el
            // remitente y el receptor ven exactamente la misma forma de datos.
            $mensajeFormateado = $this->formatMensaje($mensaje, $user);

            // 🔧 FIX: esto es lo que faltaba por completo — sin esta línea,
            // el mensaje se guardaba en la base de datos pero NUNCA se
            // avisaba por WebSocket al otro participante. Por eso tenías
            // que recargar la página para verlo: no había forma de que
            // llegara en vivo, el evento simplemente nunca se disparaba.
            try {
                broadcast(new MensajeEnviado($mensajeFormateado))->toOthers();
                Log::info('📡 Evento mensaje.enviado emitido', ['chat_id' => $chat->id, 'mensaje_id' => $mensaje->id]);
            } catch (\Exception $e) {
                Log::error('❌ Error al emitir mensaje.enviado: ' . $e->getMessage());
            }

            // Notificar al otro usuario
            $coincidencia = $chat->coincidencia;
            if ($coincidencia) {
                $otroUsuarioId = $coincidencia->usuario_a_id === $user->id
                    ? $coincidencia->usuario_b_id
                    : $coincidencia->usuario_a_id;

                Notificacion::crear(
                    usuarioId: $otroUsuarioId,
                    emisorId: $user->id,
                    tipo: 'mensaje',
                    mensaje: "<strong>{$user->nombre}</strong> te envió un mensaje",
                    link: '/mensajes',
                );
            }

            return response()->json([
                'success' => true,
                'mensaje' => $mensajeFormateado,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al enviar mensaje', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
                'chat_id' => $chat->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje en ChatController::store', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'chat_id' => $chat->id ?? null
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /chats/{chat}/marcar-leido
     */
    public function marcarLeido(Chat $chat)
    {
        try {
            $user = Auth::user();
            
            if (!$chat->tieneParticipante($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este chat'
                ], 403);
            }
            
            Mensaje::where('chat_id', $chat->id)
                ->where('remitente_id', '!=', $user->id)
                ->where('leido', false)
                ->update([
                    'leido' => true,
                    'leido_en' => now()
                ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error al marcar leído', [
                'message' => $e->getMessage(),
                'chat_id' => $chat->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como leído'
            ], 500);
        }
    }

    /**
     * POST /chats/{chat}/escribiendo
     */
    public function escribiendo(Request $request, Chat $chat)
    {
        try {
            $user = Auth::user();
            
            if (!$chat->tieneParticipante($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes acceso a este chat'
                ], 403);
            }
            
            $escribiendo = $request->input('escribiendo', true);

            // 🔧 FIX: esta línea estaba comentada — por eso el indicador
            // "escribiendo..." nunca le llegaba al otro participante, aunque
            // el endpoint respondía success:true normalmente (el POST sí
            // funcionaba, solo nunca avisaba a nadie por WebSocket).
            try {
                broadcast(new UsuarioEscribiendo($chat->id, $user->id, $escribiendo))->toOthers();
            } catch (\Exception $e) {
                Log::error('❌ Error al emitir usuario.escribiendo: ' . $e->getMessage());
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error en escribiendo', [
                'message' => $e->getMessage(),
                'chat_id' => $chat->id,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al notificar escritura'
            ], 500);
        }
    }

    /**
     * DELETE /mensajes/{mensaje}
     */
    public function destroy(Mensaje $mensaje)
    {
        try {
            $user = Auth::user();
            
            if ($mensaje->remitente_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes borrar mensajes de otra persona'
                ], 403);
            }
            
            $mensaje->delete();
            
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error al borrar mensaje', [
                'message' => $e->getMessage(),
                'mensaje_id' => $mensaje->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al borrar el mensaje'
            ], 500);
        }
    }

    /**
     * Envía un mensaje en una conversación (legacy)
     */
    public function enviar(Request $request)
    {
        Log::info('=== INICIO enviar mensaje ===', [
            'user_id' => Auth::id(),
            'chat_id' => $request->chat_id
        ]);

        try {
            $request->validate([
                'chat_id' => 'required|exists:chats,id',
                'texto' => 'required|string|max:5000',
            ]);

            $user = Auth::user();
            
            // Verificar que el chat pertenece al usuario
            $chat = Chat::with('coincidencia')->find($request->chat_id);
            if (!$chat || !$chat->coincidencia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat no encontrado'
                ], 404);
            }

            $coincidencia = $chat->coincidencia;
            if ($coincidencia->usuario_a_id !== $user->id && $coincidencia->usuario_b_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar mensajes en este chat'
                ], 403);
            }

            // Crear el mensaje
            $mensaje = Mensaje::create([
                'chat_id' => $request->chat_id,
                'remitente_id' => $user->id,
                'texto' => $request->texto,
                'leido' => false,
                'estado' => 'enviado',
            ]);

            // Actualizar el último mensaje del chat
            $chat->update([
                'ultimo_mensaje_en' => now()
            ]);

            // Notificar al OTRO participante
            $otroUsuarioId = $coincidencia->usuario_a_id === $user->id
                ? $coincidencia->usuario_b_id
                : $coincidencia->usuario_a_id;

            Notificacion::crear(
                usuarioId: $otroUsuarioId,
                emisorId: $user->id,
                tipo: 'mensaje',
                mensaje: "<strong>{$user->nombre}</strong> te envió un mensaje",
                link: '/mensajes',
            );

            Log::info('Mensaje enviado', [
                'mensaje_id' => $mensaje->id,
                'chat_id' => $request->chat_id,
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'mensaje' => [
                    'id' => $mensaje->id,
                    'texto' => $mensaje->texto,
                    'tipo' => 'texto',
                    'remitente_id' => $mensaje->remitente_id,
                    'esRemitente' => true,
                    'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                    'avatar' => $user->avatar,
                    'tiempo' => $this->formatearTiempo($mensaje->created_at),
                    'fecha' => $mensaje->created_at->format('H:i'),
                    'created_at' => $mensaje->created_at->toIso8601String(),
                    'leido' => $mensaje->leido,
                ],
                'message' => 'Mensaje enviado correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al enviar mensaje', [
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje'
            ], 500);
        }
    }

    /**
     * Marca los mensajes como leídos (legacy)
     */
    public function marcarLeidos(Request $request)
    {
        try {
            $request->validate([
                'chat_id' => 'required|exists:chats,id',
            ]);

            $user = Auth::user();
            
            $actualizados = Mensaje::where('chat_id', $request->chat_id)
                ->where('remitente_id', '!=', $user->id)
                ->where('leido', false)
                ->update([
                    'leido' => true,
                    'leido_en' => now()
                ]);

            Log::info('Mensajes marcados como leídos', [
                'chat_id' => $request->chat_id,
                'user_id' => $user->id,
                'actualizados' => $actualizados
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mensajes marcados como leídos',
                'actualizados' => $actualizados
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar mensajes como leídos', [
                'message' => $e->getMessage(),
                'chat_id' => $request->chat_id ?? null
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar mensajes como leídos'
            ], 500);
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Obtiene el avatar de un usuario
     */
    protected function getAvatarFromUser($user, $perfil = null)
    {
        if (!$user) {
            return '/images/shared/avatar-default.jpg';
        }

        // Si el usuario tiene foto_principal
        if ($user->foto_principal) {
            if (str_starts_with($user->foto_principal, 'http') || str_starts_with($user->foto_principal, '/')) {
                return $user->foto_principal;
            }
            return '/storage/' . $user->foto_principal;
        }

        // Buscar en el perfil
        if ($perfil) {
            $foto = $perfil->fotos()->where('es_principal', true)->first();
            if ($foto) {
                $url = $foto->url ?? $foto->ruta_foto ?? null;
                if ($url) {
                    if (str_starts_with($url, 'http') || str_starts_with($url, '/')) {
                        return $url;
                    }
                    return '/storage/' . $url;
                }
            }
        }

        return '/images/shared/avatar-default.jpg';
    }

    /**
     * Formatea intereses para la vista
     */
    protected function formatearIntereses($intereses)
    {
        if (empty($intereses)) return [];
        
        $iconos = [
            'viajes' => 'pi-send',
            'viajar' => 'pi-send',
            'viaje' => 'pi-send',
            'cenas' => 'pi-star',
            'comida' => 'pi-star',
            'gastronomía' => 'pi-star',
            'bienestar' => 'pi-heart',
            'salud' => 'pi-heart',
            'fitness' => 'pi-heart',
            'música' => 'pi-volume-up',
            'musica' => 'pi-volume-up',
            'conciertos' => 'pi-volume-up',
            'arte' => 'pi-palette',
            'cultura' => 'pi-palette',
            'cine' => 'pi-play',
            'series' => 'pi-play',
            'streaming' => 'pi-play',
            'deporte' => 'pi-star',
            'deportes' => 'pi-star',
            'lectura' => 'pi-book',
            'libros' => 'pi-book',
            'tecnología' => 'pi-wifi',
            'tech' => 'pi-wifi',
            'videojuegos' => 'pi-game',
            'juegos' => 'pi-game',
        ];

        $resultados = [];
        foreach ($intereses as $interes) {
            $label = is_array($interes) ? ($interes['label'] ?? $interes['nombre'] ?? $interes['valor'] ?? 'Interés') : (string) $interes;
            $icono = $iconos[strtolower($label)] ?? 'pi-tag';
            $resultados[] = [
                'icon' => $icono,
                'label' => $label
            ];
        }

        return array_slice($resultados, 0, 5);
    }

    /**
     * Formatea el tiempo para la vista
     */
    protected function formatearTiempo($fecha)
    {
        if (!$fecha) return 'Recién';
        
        try {
            $diff = Carbon::parse($fecha)->diffForHumans();
            
            $diff = str_replace('minutes', 'min', $diff);
            $diff = str_replace('minute', 'min', $diff);
            $diff = str_replace('hours', 'h', $diff);
            $diff = str_replace('hour', 'h', $diff);
            $diff = str_replace('days', 'd', $diff);
            $diff = str_replace('day', 'd', $diff);
            $diff = str_replace('weeks', 'sem', $diff);
            $diff = str_replace('week', 'sem', $diff);
            $diff = str_replace('months', 'mes', $diff);
            $diff = str_replace('month', 'mes', $diff);
            $diff = str_replace('years', 'año', $diff);
            $diff = str_replace('year', 'año', $diff);
            $diff = str_replace('from now', '', $diff);
            $diff = str_replace('ago', 'hace', $diff);
            $diff = trim($diff);
            
            return $diff ?: 'Recién';
        } catch (\Exception $e) {
            return 'Recién';
        }
    }

    /**
     * Formatea duración de video
     */
    private function formatDuracion($segundos)
    {
        $minutos = intdiv($segundos, 60);
        $segundosRestantes = $segundos % 60;
        return sprintf('%d:%02d', $minutos, $segundosRestantes);
    }

    /**
     * Formatear mensaje para JSON
     */
    private function formatMensaje($mensaje, $user)
    {
        $remitente = $mensaje->remitente;
        $esRemitente = $mensaje->remitente_id === $user->id;
        
        return [
            'id' => $mensaje->id,
            'chat_id' => $mensaje->chat_id,
            'remitente_id' => $mensaje->remitente_id,
            'texto' => $mensaje->texto,
            'tipo' => $mensaje->tipo ?? 'texto',
            'archivo_url' => $mensaje->archivo_path ? Storage::url($mensaje->archivo_path) : null,
            'archivo_nombre_original' => $mensaje->archivo_nombre_original,
            'duracion_segundos' => $mensaje->duracion_segundos,
            'duracion_formateada' => $mensaje->duracion_segundos ? $this->formatDuracion($mensaje->duracion_segundos) : null,
            'leido' => $mensaje->leido,
            'created_at' => $mensaje->created_at->toIso8601String(),
            'remitente' => $remitente ? [
                'id' => $remitente->id,
                'nombre' => $remitente->nombre ?? $remitente->apodo ?? 'Usuario',
                'avatar' => $remitente->avatar,
            ] : null,
        ];
    }
}