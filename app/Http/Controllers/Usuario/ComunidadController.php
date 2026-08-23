<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Publicacion;
use App\Models\Interaccion;
use App\Models\LikePublicacion;
use App\Models\Notificacion;
use App\Models\Evento;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ComunidadController extends Controller
{
    public function index(): Response
    {
        Log::info('=== INICIO ComunidadController@index ===');
        
        $user = Auth::user();
        
        if (!$user) {
            Log::warning('Usuario no autenticado en comunidad');
            return redirect()->route('login');
        }

        $avatar = $user->avatar;

        $usuarioData = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'apodo' => $user->apodo,
            'email' => $user->email,
            'avatar' => $avatar,
            'verificado' => $user->estado === 'verificado',
            'rol' => $user->rol ?? 'usuario',
            'tiene_perfil' => $user->perfil ? true : false,
            'foto_principal' => $user->foto_principal,
        ];

        $publicaciones = $this->getPublicaciones($user);
        $metricas = $this->getMetricas();
        $temasTendencia = $this->getTemasTendencia();
        $proximosEventos = $this->getProximosEventos();

        return Inertia::render('Usuario/Comunidad', [
            'usuario' => $usuarioData,
            'metricas' => $metricas,
            'publicaciones' => $publicaciones,
            'temasTendencia' => $temasTendencia,
            'proximosEventos' => $proximosEventos,
        ]);
    }

    private function getAvatarFromUser($user): string
    {
        if (!$user) {
            return '/images/shared/avatar-default.jpg';
        }
        return $user->avatar;
    }

    private function getPublicaciones($user): array
    {
        $publicaciones = Publicacion::with(['usuario'])
            ->where('estado', 'publicado')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $result = [];

        foreach ($publicaciones as $pub) {
            $avatarAutor = $this->getAvatarFromUser($pub->usuario);

            // ✅ Verificar like - incluye registros con soft delete
            $liked = false;
            if ($user) {
                $liked = LikePublicacion::where([
                    'publicacion_id' => $pub->id,
                    'usuario_id' => $user->id,
                ])->exists();
            }

            $comentarios = Interaccion::with(['usuario'])
                ->where('contenido_id', $pub->id)
                ->where('tipo', 'comentario')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($com) {
                    $avatar = $this->getAvatarFromUser($com->usuario);
                    return [
                        'id' => $com->id,
                        'usuario' => $com->usuario ? $com->usuario->nombre : 'Usuario',
                        'avatar' => $avatar,
                        'texto' => $com->comentario,
                        'tiempo' => $com->created_at->diffForHumans(),
                        'usuario_id' => $com->usuario_id,
                    ];
                })
                ->toArray();

            $totalLikes = LikePublicacion::where('publicacion_id', $pub->id)->count();
            $totalComentarios = Interaccion::where('contenido_id', $pub->id)
                ->where('tipo', 'comentario')
                ->count();

            $tipoMedia = 'texto';
            $mediaUrl = null;
            $mediaThumbnail = null;

            if ($pub->imagen) {
                $extension = pathinfo($pub->imagen, PATHINFO_EXTENSION);
                $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v', '3gp'];
                
                if (in_array(strtolower($extension), $videoExtensions)) {
                    $tipoMedia = 'video';
                    $mediaUrl = asset('storage/' . $pub->imagen);
                    $mediaThumbnail = asset('images/video-thumbnail-default.jpg');
                } else {
                    $tipoMedia = 'imagen';
                    $mediaUrl = asset('storage/' . $pub->imagen);
                }
            }

            if (isset($pub->metadatos) && is_array($pub->metadatos)) {
                if (isset($pub->metadatos['tipo_media'])) {
                    $tipoMedia = $pub->metadatos['tipo_media'];
                    if ($tipoMedia === 'video' && isset($pub->metadatos['video_path'])) {
                        $mediaUrl = asset('storage/' . $pub->metadatos['video_path']);
                    }
                }
            }

            $result[] = [
                'id' => $pub->id,
                'autor' => $pub->usuario->nombre ?? 'Usuario',
                'rol' => $pub->usuario->rol === 'creador' ? 'Creador' : 'Usuario',
                'avatar' => $avatarAutor,
                'tiempo' => $pub->tiempo,
                'texto' => $pub->texto,
                'imagen' => $mediaUrl,
                'media_url' => $mediaUrl,
                'media_type' => $tipoMedia,
                'media_thumbnail' => $mediaThumbnail,
                'likes' => $totalLikes,
                'liked' => $liked,
                'comentarios' => $totalComentarios,
                'comentarios_list' => $comentarios,
                'premium' => $pub->es_premium,
                'verificado' => $pub->usuario->estado === 'verificado',
                'usuario_id' => $pub->usuario_id,
                'created_at' => $pub->created_at,
            ];
        }

        return $result;
    }

    private function getMetricas(): array
    {
        $usuariosActivos = User::whereHas('publicaciones', function($query) {
            $query->where('created_at', '>=', now()->subDays(7));
        })->count();

        $creadoresActivos = User::where('rol', 'creador')
            ->whereHas('publicaciones', function($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })
            ->count();

        $eventosProximos = Evento::where('fecha', '>=', now())
            ->where('estado', 'publicado')
            ->count();

        return [
            [
                'icon' => 'pi-wave-pulse',
                'titulo' => 'Feed activo',
                'desc' => 'Publicaciones, fotos y conversaciones nuevas cada minuto.',
                'valor' => number_format($usuariosActivos + $creadoresActivos),
                'etiqueta' => 'usuarios activos',
            ],
            [
                'icon' => 'pi-users',
                'titulo' => 'Creadores',
                'desc' => 'Comparte y accede a contenido exclusivo de creadores verificados.',
                'valor' => number_format($creadoresActivos),
                'etiqueta' => 'creadores activos',
            ],
            [
                'icon' => 'pi-calendar',
                'titulo' => 'Eventos próximos',
                'desc' => 'Eventos exclusivos para la comunidad.',
                'valor' => number_format($eventosProximos),
                'etiqueta' => 'eventos disponibles',
            ],
        ];
    }

    private function getTemasTendencia(): array
    {
        return [
            ['texto' => 'ConexionesReales', 'fuego' => true],
            ['texto' => 'PlanesDelFin', 'fuego' => false],
            ['texto' => 'ViajesYExperiencias', 'fuego' => false],
            ['texto' => 'CharlasSinFiltro', 'fuego' => false],
            ['texto' => 'EventosCD', 'fuego' => false],
            ['texto' => 'Lifestyle', 'fuego' => false],
            ['texto' => 'NuevosAmigos', 'fuego' => false],
            ['texto' => 'MomentosVip', 'fuego' => false],
            ['texto' => 'Recomendaciones', 'fuego' => false],
            ['texto' => 'HistoriasReales', 'fuego' => false],
        ];
    }

    private function getProximosEventos(): array
    {
        Carbon::setLocale('es');
        
        $eventos = Evento::where('fecha', '>=', now())
            ->where('estado', 'publicado')
            ->orderBy('fecha', 'asc')
            ->limit(6)
            ->get();

        if ($eventos->count() > 0) {
            return $eventos->map(function($evento) {
                $imagen = '/images/comunidad/evento-default.jpg';
                if ($evento->imagen) {
                    if (filter_var($evento->imagen, FILTER_VALIDATE_URL)) {
                        $imagen = $evento->imagen;
                    } elseif (Storage::disk('public')->exists($evento->imagen)) {
                        $imagen = asset('storage/' . $evento->imagen);
                    } elseif (Storage::disk('public')->exists('eventos/' . $evento->imagen)) {
                        $imagen = asset('storage/eventos/' . $evento->imagen);
                    }
                }

                $fecha = Carbon::parse($evento->fecha);
                
                $meses = [
                    'January' => 'Enero',
                    'February' => 'Febrero',
                    'March' => 'Marzo',
                    'April' => 'Abril',
                    'May' => 'Mayo',
                    'June' => 'Junio',
                    'July' => 'Julio',
                    'August' => 'Agosto',
                    'September' => 'Septiembre',
                    'October' => 'Octubre',
                    'November' => 'Noviembre',
                    'December' => 'Diciembre',
                ];
                
                $dias = [
                    'Monday' => 'Lunes',
                    'Tuesday' => 'Martes',
                    'Wednesday' => 'Miércoles',
                    'Thursday' => 'Jueves',
                    'Friday' => 'Viernes',
                    'Saturday' => 'Sábado',
                    'Sunday' => 'Domingo',
                ];

                $nombreMes = $meses[$fecha->format('F')] ?? $fecha->format('F');
                $nombreDia = $dias[$fecha->format('l')] ?? $fecha->format('l');

                $asistentesCount = 0;
                try {
                    $asistentesCount = Reserva::where('evento_id', $evento->id)
                        ->where('estado', 'aprobada')
                        ->sum('asistentes') ?? 0;
                } catch (\Exception $e) {
                    Log::warning('Error al contar asistentes', [
                        'evento_id' => $evento->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                $capacidad = $evento->capacidad ?? 0;
                $disponible = $capacidad === 0 || $asistentesCount < $capacidad;

                return [
                    'id' => $evento->id,
                    'dia' => $fecha->format('d'),
                    'mes' => strtoupper(substr($nombreMes, 0, 3)),
                    'nombre_mes' => $nombreMes,
                    'nombre_dia' => $nombreDia,
                    'titulo' => $evento->nombre,
                    'lugar' => $evento->ciudad ?? 'Por definir',
                    'direccion' => $evento->zona_ubicacion ?? '',
                    'fecha_completa' => $fecha->format('d/m/Y'),
                    'fecha_hora' => $evento->hora ? Carbon::parse($evento->hora)->format('H:i') : 'Por definir',
                    'descripcion' => $evento->descripcion ?? '',
                    'imagen' => $imagen,
                    'disponible' => $disponible,
                    'asistentes' => $asistentesCount,
                    'capacidad' => $capacidad,
                    'precio' => $evento->precio ?? 0,
                ];
            })->toArray();
        }

        return [
            [
                'id' => 1,
                'dia' => '28',
                'mes' => 'DIC',
                'nombre_mes' => 'Diciembre',
                'nombre_dia' => 'Sábado',
                'titulo' => 'Noche de Gala Fin de Año',
                'lugar' => 'Salón Principal',
                'direccion' => 'Av. Reforma 123',
                'fecha_completa' => '28/12/2025',
                'fecha_hora' => '20:00',
                'descripcion' => 'Celebración especial con cena, baile y sorpresas.',
                'imagen' => '/images/comunidad/evento-gala.jpg',
                'disponible' => true,
                'asistentes' => 45,
                'capacidad' => 100,
                'precio' => 500,
            ],
            [
                'id' => 2,
                'dia' => '15',
                'mes' => 'ENE',
                'nombre_mes' => 'Enero',
                'nombre_dia' => 'Miércoles',
                'titulo' => 'Networking Creativo',
                'lugar' => 'Espacio Coworking',
                'direccion' => 'Calle Creativa 45',
                'fecha_completa' => '15/01/2026',
                'fecha_hora' => '18:30',
                'descripcion' => 'Conecta con otros creadores y expande tu red.',
                'imagen' => '/images/comunidad/evento-networking.jpg',
                'disponible' => true,
                'asistentes' => 12,
                'capacidad' => 50,
                'precio' => 150,
            ],
            [
                'id' => 3,
                'dia' => '22',
                'mes' => 'ENE',
                'nombre_mes' => 'Enero',
                'nombre_dia' => 'Jueves',
                'titulo' => 'Taller de Fotografía',
                'lugar' => 'Estudio Creativo',
                'direccion' => 'Av. Arte 78',
                'fecha_completa' => '22/01/2026',
                'fecha_hora' => '10:00',
                'descripcion' => 'Aprende técnicas avanzadas de fotografía con expertos.',
                'imagen' => '/images/comunidad/evento-foto.jpg',
                'disponible' => true,
                'asistentes' => 8,
                'capacidad' => 20,
                'precio' => 250,
            ],
            [
                'id' => 4,
                'dia' => '10',
                'mes' => 'FEB',
                'nombre_mes' => 'Febrero',
                'nombre_dia' => 'Martes',
                'titulo' => 'Festival de Música Independiente',
                'lugar' => 'Parque Central',
                'direccion' => 'Av. Principal 100',
                'fecha_completa' => '10/02/2026',
                'fecha_hora' => '14:00',
                'descripcion' => 'Disfruta de bandas emergentes y ambiente festivo.',
                'imagen' => '/images/comunidad/evento-musica.jpg',
                'disponible' => true,
                'asistentes' => 30,
                'capacidad' => 200,
                'precio' => 100,
            ],
        ];
    }

    // ============================================================
    // MÉTODOS DE INTERACCIÓN
    // ============================================================

    public function crearPublicacion(Request $request)
    {
        Log::info('=== INICIO crearPublicacion ===', ['user_id' => Auth::id()]);

        try {
            $request->validate([
                'texto' => ['nullable', 'string', 'max:5000'],
                'imagen' => ['nullable', 'image', 'max:10240'],
                'video' => ['nullable', 'file', 'mimes:mp4,avi,mov,wmv,flv,webm,mkv,m4v,3gp', 'max:51200'],
                'es_premium' => ['boolean'],
            ]);

            if (!$request->texto && !$request->hasFile('imagen') && !$request->hasFile('video')) {
                return redirect()->back()->with('error', 'Debes escribir algo o adjuntar un archivo para publicar.');
            }

            $user = Auth::user();

            $data = [
                'usuario_id' => $user->id,
                'texto' => $request->texto ?? '',
                'es_premium' => $request->es_premium ?? false,
                'estado' => 'publicado',
                'metadatos' => [],
            ];

            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('publicaciones', 'public');
                $data['imagen'] = $path;
                $data['metadatos']['tipo_media'] = 'imagen';
            }

            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('publicaciones/videos', 'public');
                $data['imagen'] = $path;
                $data['metadatos']['tipo_media'] = 'video';
                $data['metadatos']['video_path'] = $path;
            }

            $publicacion = Publicacion::create($data);

            return redirect()->back()->with('success', 'Publicación creada correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al crear publicación', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Error al crear la publicación: ' . $e->getMessage());
        }
    }

    /**
     * ✅ LIKE / QUITAR LIKE - CORREGIDO PARA SoftDeletes
     * Usa forceDelete() para eliminar físicamente y evitar duplicados
     */
    public function likePublicacion($publicacionId)
    {
        Log::info('=== INICIO likePublicacion ===', [
            'publicacion_id' => $publicacionId,
            'user_id' => Auth::id(),
        ]);

        try {
            $user = Auth::user();
            $publicacion = Publicacion::findOrFail($publicacionId);

            // ✅ Buscar el like (incluye eliminados con soft delete)
            $like = LikePublicacion::where([
                'publicacion_id' => $publicacionId,
                'usuario_id' => $user->id,
            ])->first();

            if ($like) {
                // ✅ Si existe, lo ELIMINAMOS FÍSICAMENTE (forceDelete)
                // Esto evita el problema de duplicados con soft deletes
                $like->forceDelete();
                $liked = false;
                $mensaje = 'Like eliminado';
                Log::info('Like eliminado (forceDelete)', [
                    'publicacion_id' => $publicacionId,
                    'user_id' => $user->id,
                    'like_id' => $like->id
                ]);
            } else {
                // ✅ Verificar si hay un registro eliminado (soft delete)
                // y restaurarlo en lugar de crear uno nuevo
                $likeTrashed = LikePublicacion::withTrashed()
                    ->where([
                        'publicacion_id' => $publicacionId,
                        'usuario_id' => $user->id,
                    ])->first();

                if ($likeTrashed) {
                    // ✅ Restaurar el registro eliminado
                    $likeTrashed->restore();
                    $liked = true;
                    $mensaje = 'Like restaurado';
                    Log::info('Like restaurado desde soft delete', [
                        'publicacion_id' => $publicacionId,
                        'user_id' => $user->id,
                        'like_id' => $likeTrashed->id
                    ]);
                } else {
                    // ✅ Crear nuevo like
                    LikePublicacion::create([
                        'publicacion_id' => $publicacionId,
                        'usuario_id' => $user->id,
                    ]);
                    $liked = true;
                    $mensaje = 'Like agregado';
                    Log::info('Like agregado', [
                        'publicacion_id' => $publicacionId,
                        'user_id' => $user->id
                    ]);
                }
            }

            // ✅ Contar likes totales y actualizar la publicación
            $totalLikes = LikePublicacion::where('publicacion_id', $publicacionId)->count();
            $publicacion->update(['likes' => $totalLikes]);

            // 🔔 Notificar al dueño de la publicación (solo cuando SE DA el
            // like, no cuando se quita) — cubre tanto "Like agregado" como
            // "Like restaurado", ambos casos dejan $liked = true.
            if ($liked) {
                Notificacion::crear(
                    usuarioId: $publicacion->usuario_id,
                    emisorId: $user->id,
                    tipo: 'like',
                    mensaje: "<strong>{$user->nombre}</strong> le dio like a tu publicación",
                    contenidoId: $publicacion->id,
                );
            }

            Log::info('Like procesado correctamente', [
                'publicacion_id' => $publicacionId,
                'total_likes' => $totalLikes,
                'liked' => $liked,
            ]);

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes' => $totalLikes,
                'message' => $mensaje,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar like', [
                'publicacion_id' => $publicacionId,
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el like: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function obtenerComentarios($publicacionId)
    {
        try {
            $comentarios = Interaccion::with(['usuario'])
                ->where('contenido_id', $publicacionId)
                ->where('tipo', 'comentario')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($comentario) {
                    $avatar = $this->getAvatarFromUser($comentario->usuario);
                    return [
                        'id' => $comentario->id,
                        'usuario' => $comentario->usuario ? $comentario->usuario->nombre : 'Usuario desconocido',
                        'avatar' => $avatar,
                        'texto' => $comentario->comentario,
                        'tiempo' => $comentario->created_at->diffForHumans(),
                        'verificado' => $comentario->usuario ? $comentario->usuario->estado === 'verificado' : false,
                        'usuario_id' => $comentario->usuario_id,
                    ];
                });

            return response()->json([
                'success' => true,
                'comentarios' => $comentarios,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener comentarios', [
                'publicacion_id' => $publicacionId,
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener comentarios',
            ], 500);
        }
    }

    public function crearComentario(Request $request, $publicacionId)
    {
        try {
            $request->validate([
                'texto' => ['required', 'string', 'max:1000'],
            ]);

            $user = Auth::user();
            $publicacion = Publicacion::findOrFail($publicacionId);

            $comentario = Interaccion::create([
                'contenido_id' => $publicacionId,
                'usuario_id' => $user->id,
                'tipo' => 'comentario',
                'comentario' => $request->texto,
            ]);

            $comentario->load('usuario');

            $totalComentarios = Interaccion::where('contenido_id', $publicacionId)
                ->where('tipo', 'comentario')
                ->count();

            $publicacion->update(['comentarios_count' => $totalComentarios]);

            // 🔔 Notificar al dueño de la publicación
            Notificacion::crear(
                usuarioId: $publicacion->usuario_id,
                emisorId: $user->id,
                tipo: 'comentario',
                mensaje: "<strong>{$user->nombre}</strong> comentó tu publicación",
                contenidoId: $publicacion->id,
            );

            $avatar = $this->getAvatarFromUser($comentario->usuario);

            return response()->json([
                'success' => true,
                'comentario' => [
                    'id' => $comentario->id,
                    'usuario' => $comentario->usuario->nombre ?? 'Usuario',
                    'avatar' => $avatar,
                    'texto' => $comentario->comentario,
                    'tiempo' => $comentario->created_at->diffForHumans(),
                    'usuario_id' => $comentario->usuario_id,
                ],
                'total_comentarios' => $totalComentarios,
                'message' => 'Comentario agregado correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear comentario', [
                'publicacion_id' => $publicacionId,
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el comentario: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarComentario($comentarioId)
    {
        try {
            $user = Auth::user();
            $comentario = Interaccion::findOrFail($comentarioId);

            if ($comentario->usuario_id !== $user->id && $user->rol !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este comentario',
                ], 403);
            }

            $publicacionId = $comentario->contenido_id;
            $comentario->delete();

            $totalComentarios = Interaccion::where('contenido_id', $publicacionId)
                ->where('tipo', 'comentario')
                ->count();

            Publicacion::where('id', $publicacionId)->update(['comentarios_count' => $totalComentarios]);

            return response()->json([
                'success' => true,
                'message' => 'Comentario eliminado correctamente',
                'total_comentarios' => $totalComentarios,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar comentario', [
                'comentario_id' => $comentarioId,
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el comentario',
            ], 500);
        }
    }

    public function eliminarPublicacion($publicacionId)
    {
        try {
            $user = Auth::user();
            $publicacion = Publicacion::findOrFail($publicacionId);

            if ($publicacion->usuario_id !== $user->id && $user->rol !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar esta publicación',
                ], 403);
            }

            // ✅ Eliminar likes (forceDelete para evitar problemas)
            LikePublicacion::where('publicacion_id', $publicacionId)->forceDelete();

            // ✅ Eliminar comentarios
            Interaccion::where('contenido_id', $publicacionId)
                ->where('tipo', 'comentario')
                ->delete();

            if ($publicacion->imagen && Storage::disk('public')->exists($publicacion->imagen)) {
                Storage::disk('public')->delete($publicacion->imagen);
            }

            if (isset($publicacion->metadatos['video_path']) && Storage::disk('public')->exists($publicacion->metadatos['video_path'])) {
                Storage::disk('public')->delete($publicacion->metadatos['video_path']);
            }

            $publicacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Publicación eliminada correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar publicación', [
                'publicacion_id' => $publicacionId,
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la publicación',
            ], 500);
        }
    }
}