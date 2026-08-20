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
        $proximosEventos = $this->getProximosEventos();

        Log::info('=== FIN ComunidadController@index ===', [
            'user_id' => $user->id,
            'avatar' => $usuarioData['avatar'],
            'publicaciones' => count($publicaciones),
            'eventos' => count($proximosEventos),
        ]);

        return Inertia::render('Usuario/Comunidad', [
            'usuario' => $usuarioData,
            'metricas' => $metricas,
            'publicaciones' => $publicaciones,
            'temasTendencia' => $temasTendencia,
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
                        'usuario_id' => $com->usuario_id,
                    ];
                })
                ->toArray();

            // DETERMINAR TIPO DE MEDIA
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
                'imagen' => $mediaUrl,
                'media_url' => $mediaUrl,
                'media_type' => $tipoMedia,
                'media_thumbnail' => $mediaThumbnail,
                'likes' => $pub->likes,
                'liked' => $liked,
                'comentarios' => $pub->comentarios_count,
                'comentarios_list' => $comentarios,
                'premium' => $pub->es_premium,
                'verificado' => $pub->usuario->estado === 'verificado',
                'usuario_id' => $pub->usuario_id,
                'created_at' => $pub->created_at,
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
     * Obtiene próximos eventos con formato en español
     */
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
                // Obtener la imagen del evento
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

                // Formatear fecha en español
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

                // Calcular asistentes usando el modelo Reserva
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

        // Eventos de muestra si no hay en la base de datos
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
                'imagen' => ['nullable', 'image', 'max:10240'],
                'video' => ['nullable', 'file', 'mimes:mp4,avi,mov,wmv,flv,webm,mkv,m4v,3gp', 'max:51200'],
                'es_premium' => ['boolean'],
                'tipo_media' => ['nullable', 'in:imagen,video'],
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
                Log::info('Imagen subida', ['path' => $path]);
            }

            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('publicaciones/videos', 'public');
                $data['imagen'] = $path;
                $data['metadatos']['tipo_media'] = 'video';
                $data['metadatos']['video_path'] = $path;
                Log::info('Video subido', ['path' => $path]);
            }

            if ($request->tipo_media === 'imagen' && !$request->hasFile('imagen')) {
                return redirect()->back()->with('error', 'Selecciona una imagen para publicar.');
            }

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
                    'usuario_id' => $comentario->usuario_id,
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

            if ($publicacion->imagen && Storage::disk('public')->exists($publicacion->imagen)) {
                Storage::disk('public')->delete($publicacion->imagen);
                Log::info('Archivo multimedia eliminado', ['path' => $publicacion->imagen]);
            }

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