<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Comentario;
use App\Models\Contenido;
use App\Models\Like;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InteraccionContenidoController extends Controller
{
    /**
     * POST /contenidos/{contenido}/like
     * Da o quita el like del usuario autenticado sobre este contenido
     * (toggle). Bloqueado si el contenido es premium y el usuario no
     * tiene acceso (no es el creador, ni tiene suscripción activa).
     */
    public function toggleLike(Contenido $contenido)
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json([
                    'mensaje' => 'Debes iniciar sesión para dar like.',
                ], 401);
            }

            // Verificar acceso
            if (!$contenido->usuarioTieneAcceso($usuario)) {
                return response()->json([
                    'mensaje' => 'Suscríbete para interactuar con este contenido exclusivo.',
                    'requiere_suscripcion' => true,
                ], 403);
            }

            // Buscar like existente
            $like = Like::where('contenido_id', $contenido->id)
                ->where('usuario_id', $usuario->id)
                ->first();

            if ($like) {
                // Quitar like
                $like->delete();
                $liked = false;
                $mensaje = 'Has quitado tu like.';
            } else {
                // Dar like
                Like::create([
                    'contenido_id' => $contenido->id,
                    'usuario_id' => $usuario->id,
                ]);
                $liked = true;
                $mensaje = 'Has dado like.';

                // 🔔 Notificar al creador dueño del contenido
                if ($contenido->creador) {
                    Notificacion::crear(
                        usuarioId: $contenido->creador->usuario_id,
                        emisorId: $usuario->id,
                        tipo: 'like',
                        mensaje: "<strong>{$usuario->nombre}</strong> le dio like a tu contenido",
                        contenidoId: $contenido->id,
                    );
                }
            }

            return response()->json([
                'ok' => true,
                'mensaje' => $mensaje,
                'liked' => $liked,
                'total_likes' => $contenido->likes()->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error en toggleLike: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ocurrió un error al procesar tu like.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /contenidos/{contenido}/comentarios
     * Lista los comentarios de un contenido.
     */
    public function comentarios(Contenido $contenido)
    {
        try {
            $usuario = Auth::user();

            // Verificar acceso
            if (!$contenido->usuarioTieneAcceso($usuario)) {
                return response()->json([
                    'mensaje' => 'Suscríbete para ver los comentarios de este contenido exclusivo.',
                    'requiere_suscripcion' => true,
                ], 403);
            }

            $comentarios = $contenido->comentarios()
                ->with('usuario')
                ->limit(50)
                ->get()
                ->map(fn (Comentario $c) => $c->toFeedPayload());

            return response()->json([
                'ok' => true,
                'comentarios' => $comentarios,
                'total_comentarios' => $contenido->comentarios()->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error en comentarios: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ocurrió un error al cargar los comentarios.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /contenidos/{contenido}/comentarios
     * Crea un comentario nuevo.
     */
    public function comentar(Request $request, Contenido $contenido)
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json([
                    'mensaje' => 'Debes iniciar sesión para comentar.',
                ], 401);
            }

            // Verificar acceso
            if (!$contenido->usuarioTieneAcceso($usuario)) {
                return response()->json([
                    'mensaje' => 'Suscríbete para comentar en este contenido exclusivo.',
                    'requiere_suscripcion' => true,
                ], 403);
            }

            // Validar
            $data = $request->validate([
                'texto' => ['required', 'string', 'max:500', 'min:1'],
            ]);

            // Crear comentario
            $comentario = Comentario::create([
                'contenido_id' => $contenido->id,
                'usuario_id' => $usuario->id,
                'texto' => trim($data['texto']),
            ]);

            // Cargar relación usuario
            $comentario->load('usuario');

            // 🔔 Notificar al creador dueño del contenido
            if ($contenido->creador) {
                Notificacion::crear(
                    usuarioId: $contenido->creador->usuario_id,
                    emisorId: $usuario->id,
                    tipo: 'comentario',
                    mensaje: "<strong>{$usuario->nombre}</strong> comentó tu contenido",
                    contenidoId: $contenido->id,
                );
            }

            return response()->json([
                'ok' => true,
                'mensaje' => 'Comentario creado exitosamente.',
                'comentario' => $comentario->toFeedPayload(),
                'total_comentarios' => $contenido->comentarios()->count(),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error de validación.',
                'errores' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en comentar: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ocurrió un error al crear el comentario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /comentarios/{comentario}
     * Solo el autor del comentario puede borrarlo.
     */
    public function eliminarComentario(Comentario $comentario)
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json([
                    'mensaje' => 'Debes iniciar sesión para eliminar comentarios.',
                ], 401);
            }

            // Verificar que sea el autor
            if ($comentario->usuario_id !== $usuario->id) {
                return response()->json([
                    'mensaje' => 'No puedes borrar comentarios de otra persona.',
                ], 403);
            }

            // Guardar el contenido para contar después
            $contenido = $comentario->contenido;

            // Eliminar
            $comentario->delete();

            return response()->json([
                'ok' => true,
                'mensaje' => 'Comentario eliminado exitosamente.',
                'total_comentarios' => $contenido->comentarios()->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error en eliminarComentario: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ocurrió un error al eliminar el comentario.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}