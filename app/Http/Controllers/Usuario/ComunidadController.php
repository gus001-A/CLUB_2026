<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Fotos;
use App\Models\Publicacion;
use App\Models\Comentario;
use App\Models\LikePublicacion;
use App\Models\Evento;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        // Datos del usuario logueado
        $avatar = '/images/shared/avatar-default.jpg';
        if ($user->foto_principal) {
            $avatar = asset('storage/' . $user->foto_principal);
        }

        $usuarioData = [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'apodo' => $user->apodo,
            'email' => $user->email,
            'avatar' => $avatar,
            'verificado' => $user->estado === 'verificado',
            'rol' => $user->rol ?? 'usuario',
            'tiene_perfil' => $user->perfil ? true : false,
        ];

        // Publicaciones con avatar de cada autor
        $publicaciones = $this->getPublicaciones($user);
        $metricas = $this->getMetricas();
        $temasTendencia = $this->getTemasTendencia();
        $creadoresSugeridos = $this->getCreadoresSugeridos($user);
        $proximosEventos = $this->getProximosEventos();

        Log::info('=== FIN ComunidadController@index ===', [
            'user_id' => $user->id,
            'avatar' => $usuarioData['avatar'],
            'publicaciones' => count($publicaciones),
        ]);

        return Inertia::render('Usuario/Comunidad', [
            'usuario' => $usuarioData,
            'metricas' => $metricas,
            'publicaciones' => $publicaciones,
            'temasTendencia' => $temasTendencia,
            'creadoresSugeridos' => $creadoresSugeridos,
            'proximosEventos' => $proximosEventos,
        ]);
    }

    /**
     * Obtiene el avatar de un usuario desde su foto_principal
     */
    private function getAvatarFromUser($user): string
    {
        if (!$user) {
            return '/images/shared/avatar-default.jpg';
        }

        if ($user->foto_principal) {
            return asset('storage/' . $user->foto_principal);
        }

        return '/images/shared/avatar-default.jpg';
    }

    /**
     * Obtiene las publicaciones del feed con los avatares de los autores
     */
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

            // Verificar si el usuario ya le dio like
            $liked = false;
            if ($user) {
                $liked = LikePublicacion::where([
                    'publicacion_id' => $pub->id,
                    'usuario_id' => $user->id,
                ])->exists();
            }

            // Obtener comentarios (ultimos 5)
            $comentarios = Comentario::with(['usuario'])
                ->where('publicacion_id', $pub->id)
                ->where('estado', 'aprobado')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($com) {
                    return [
                        'id' => $com->id,
                        'usuario' => $com->usuario ? $com->usuario->nombre : 'Usuario',
                        'avatar' => $this->getAvatarFromUser($com->usuario),
                        'texto' => $com->texto,
                        'tiempo' => $com->created_at->diffForHumans(),
                    ];
                })
                ->toArray();

            // 🔥 DETERMINAR TIPO DE MEDIA
            $tipoMedia = 'texto';
            $mediaUrl = null;
            $mediaThumbnail = null;

            // Verificar si hay imagen
            if ($pub->imagen) {
                // Verificar si es video por la extensión
                $extension = pathinfo($pub->imagen, PATHINFO_EXTENSION);
                $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v', '3gp'];
                
                if (in_array(strtolower($extension), $videoExtensions)) {
                    $tipoMedia = 'video';
                    $mediaUrl = asset('storage/' . $pub->imagen);
                    // Thumbnail para video (puedes generar uno o usar imagen por defecto)
                    $mediaThumbnail = asset('images/video-thumbnail-default.jpg');
                } else {
                    $tipoMedia = 'imagen';
                    $mediaUrl = asset('storage/' . $pub->imagen);
                }
            }

            // Si hay metadatos, sobrescribir
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
                'imagen' => $mediaUrl, // Para compatibilidad con la vista existente
                'media_url' => $mediaUrl,
                'media_type' => $tipoMedia,
                'media_thumbnail' => $mediaThumbnail,
                'likes' => $pub->likes,
                'liked' => $liked,
                'comentarios' => $pub->comentarios_count,
                'comentarios_list' => $comentarios,
                'premium' => $pub->es_premium,
                'verificado' => $pub->usuario->estado === 'verificado',
            ];
        }

        return $result;
    }

    /**
     * Obtiene las métricas de la comunidad
     */
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

    /**
     * Obtiene los temas en tendencia
     */
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

    /**
     * Obtiene creadores sugeridos
     */
    private function getCreadoresSugeridos($user): array
    {
        $creadores = User::where('rol', 'creador')
            ->where('id', '!=', $user->id)
            ->limit(3)
            ->get();

        return $creadores->map(function($creador) {
            $avatar = $this->getAvatarFromUser($creador);
            $suscriptores = $creador->suscriptores()->count() ?? 0;

            return [
                'nombre' => $creador->nombre,
                'avatar' => $avatar,
                'suscriptores' => $suscriptores > 1000 ? number_format($suscriptores / 1000, 1) . 'K' : number_format($suscriptores),
            ];
        })->toArray();
    }

    /**
     * Obtiene próximos eventos
     */
    private function getProximosEventos(): array
    {
        $eventos = Evento::where('fecha', '>=', now())
            ->where('estado', 'publicado')
            ->orderBy('fecha', 'asc')
            ->limit(3)
            ->get();

        if ($eventos->count() > 0) {
            return $eventos->map(function($evento) {
                $imagen = '/images/comunidad/evento-default.jpg';
                
                if ($evento->imagen) {
                    $imagen = asset('storage/' . $evento->imagen);
                }

                return [
                    'dia' => $evento->fecha->format('d'),
                    'mes' => strtoupper($evento->fecha->format('M')),
                    'titulo' => $evento->nombre,
                    'lugar' => $evento->ciudad,
                    'fecha' => $evento->fecha->format('D, d M · h:i A'),
                    'imagen' => $imagen,
                    'id' => $evento->id,
                ];
            })->toArray();
        }

        return [];
    }

    // ============================================================
    // METODOS PARA INTERACCIONES CON PUBLICACIONES
    // ============================================================

    /**
     * Crear una nueva publicación con soporte para imágenes y videos
     */
    public function crearPublicacion(Request $request)
    {
        Log::info('=== INICIO crearPublicacion ===', ['user_id' => Auth::id()]);

        try {
            $request->validate([
                'texto' => ['nullable', 'string', 'max:5000'],
                'imagen' => ['nullable', 'image', 'max:10240'], // 10MB máximo
                'video' => ['nullable', 'file', 'mimes:mp4,avi,mov,wmv,flv,webm,mkv,m4v,3gp', 'max:51200'], // 50MB máximo
                'es_premium' => ['boolean'],
                'tipo_media' => ['nullable', 'in:imagen,video'],
            ]);

            // Validar que al menos tenga texto o un archivo
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

            // Subir imagen si existe
            if ($request->hasFile('imagen')) {
                $path = $request->file('imagen')->store('publicaciones', 'public');
                $data['imagen'] = $path;
                $data['metadatos']['tipo_media'] = 'imagen';
                Log::info('Imagen subida', ['path' => $path]);
            }

            // Subir video si existe
            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('publicaciones/videos', 'public');
                $data['imagen'] = $path; // Usamos el mismo campo 'imagen' pero ahora puede ser video
                $data['metadatos']['tipo_media'] = 'video';
                $data['metadatos']['video_path'] = $path;
                Log::info('Video subido', ['path' => $path]);
            }

            // Si no hay imagen pero se envió tipo_media como imagen, crear placeholder
            if ($request->tipo_media === 'imagen' && !$request->hasFile('imagen')) {
                return redirect()->back()->with('error', 'Selecciona una imagen para publicar.');
            }

            // Si no hay video pero se envió tipo_media como video, crear placeholder
            if ($request->tipo_media === 'video' && !$request->hasFile('video')) {
                return redirect()->back()->with('error', 'Selecciona un video para publicar.');
            }

            $publicacion = Publicacion::create($data);

            Log::info('Publicación creada', [
                'publicacion_id' => $publicacion->id,
                'user_id' => $user->id,
                'tipo_media' => $data['metadatos']['tipo_media'] ?? 'texto',
            ]);

            return redirect()->back()->with('success', 'Publicación creada correctamente.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación al crear publicación', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->withErrors($e->errors())->with('error', 'Error en la validación de los datos.');
        } catch (\Exception $e) {
            Log::error('Error al crear publicación', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);
            return redirect()->back()->with('error', 'Error al crear la publicación: ' . $e->getMessage());
        }
    }

    /**
     * Like a una publicación - DEVUELVE JSON (ASINCRONO)
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

            $likeExistente = LikePublicacion::where([
                'publicacion_id' => $publicacionId,
                'usuario_id' => $user->id,
            ])->first();

            if ($likeExistente) {
                $likeExistente->forceDelete();
                $publicacion->decrement('likes');
                $mensaje = 'Like eliminado';
                $liked = false;
            } else {
                LikePublicacion::create([
                    'publicacion_id' => $publicacionId,
                    'usuario_id' => $user->id,
                ]);
                $publicacion->increment('likes');
                $mensaje = 'Like agregado';
                $liked = true;
            }

            Log::info('Like procesado', [
                'publicacion_id' => $publicacionId,
                'user_id' => $user->id,
                'accion' => $mensaje,
            ]);

            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes' => $publicacion->fresh()->likes,
                'message' => $mensaje,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar like', [
                'publicacion_id' => $publicacionId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el like',
            ], 500);
        }
    }

    /**
     * Obtener comentarios de una publicación
     */
    public function obtenerComentarios($publicacionId)
    {
        try {
            $comentarios = Comentario::with(['usuario'])
                ->where('publicacion_id', $publicacionId)
                ->where('estado', 'aprobado')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($comentario) {
                    $avatar = $this->getAvatarFromUser($comentario->usuario);

                    return [
                        'id' => $comentario->id,
                        'usuario' => $comentario->usuario ? $comentario->usuario->nombre : 'Usuario desconocido',
                        'avatar' => $avatar,
                        'texto' => $comentario->texto,
                        'tiempo' => $comentario->created_at->diffForHumans(),
                        'verificado' => $comentario->usuario ? $comentario->usuario->estado === 'verificado' : false,
                    ];
                });

            return response()->json([
                'success' => true,
                'comentarios' => $comentarios,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener comentarios', [
                'publicacion_id' => $publicacionId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener comentarios',
            ], 500);
        }
    }

    /**
     * Crear un comentario - DEVUELVE JSON (ASINCRONO)
     */
    public function crearComentario(Request $request, $publicacionId)
    {
        Log::info('=== INICIO crearComentario ===', [
            'publicacion_id' => $publicacionId,
            'user_id' => Auth::id(),
        ]);

        try {
            $request->validate([
                'texto' => ['required', 'string', 'max:1000'],
            ]);

            $user = Auth::user();
            $publicacion = Publicacion::findOrFail($publicacionId);

            $comentario = Comentario::create([
                'publicacion_id' => $publicacionId,
                'usuario_id' => $user->id,
                'texto' => $request->texto,
                'estado' => 'aprobado',
            ]);

            $publicacion->increment('comentarios_count');

            // Obtener el comentario con los datos del usuario
            $comentario->load('usuario');
            
            $avatar = $this->getAvatarFromUser($comentario->usuario);

            Log::info('Comentario creado', [
                'comentario_id' => $comentario->id,
                'publicacion_id' => $publicacionId,
            ]);

            return response()->json([
                'success' => true,
                'comentario' => [
                    'id' => $comentario->id,
                    'usuario' => $comentario->usuario->nombre ?? 'Usuario',
                    'avatar' => $avatar,
                    'texto' => $comentario->texto,
                    'tiempo' => $comentario->created_at->diffForHumans(),
                ],
                'total_comentarios' => $publicacion->fresh()->comentarios_count,
                'message' => 'Comentario agregado correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al crear comentario', [
                'publicacion_id' => $publicacionId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el comentario',
            ], 500);
        }
    }

    /**
     * Eliminar una publicación
     */
    public function eliminarPublicacion($publicacionId)
    {
        Log::info('=== INICIO eliminarPublicacion ===', [
            'publicacion_id' => $publicacionId,
            'user_id' => Auth::id(),
        ]);

        try {
            $user = Auth::user();
            $publicacion = Publicacion::findOrFail($publicacionId);

            if ($publicacion->usuario_id !== $user->id && $user->rol !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar esta publicación',
                ], 403);
            }

            // Eliminar imagen o video si existe
            if ($publicacion->imagen && Storage::disk('public')->exists($publicacion->imagen)) {
                Storage::disk('public')->delete($publicacion->imagen);
                Log::info('Archivo multimedia eliminado', ['path' => $publicacion->imagen]);
            }

            // Si hay video en metadatos, eliminarlo también
            if (isset($publicacion->metadatos['video_path']) && Storage::disk('public')->exists($publicacion->metadatos['video_path'])) {
                Storage::disk('public')->delete($publicacion->metadatos['video_path']);
                Log::info('Video eliminado', ['path' => $publicacion->metadatos['video_path']]);
            }

            $publicacion->delete();

            Log::info('Publicación eliminada', [
                'publicacion_id' => $publicacionId,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Publicación eliminada correctamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar publicación', [
                'publicacion_id' => $publicacionId,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la publicación',
            ], 500);
        }
    }
}