<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Evento;
use App\Models\Mensaje;
use App\Models\Coincidencia;
use App\Models\Chat;
use App\Models\Publicacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InicioController extends Controller
{
    /**
     * Muestra la página de inicio con todos los datos
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
            $perfil = Perfil::where('usuario_id', $user->id)->first();
            
            if (!$perfil) {
                Log::info('Usuario sin perfil, redirigiendo a completar perfil', [
                    'user_id' => $user->id,
                    'estado' => $user->estado
                ]);
                
                return redirect()->route('perfil.completar')->with('flash', [
                    'toast' => [
                        'type' => 'info',
                        'title' => 'Completa tu perfil',
                        'message' => 'Para acceder a todas las funcionalidades, completa tu perfil.',
                        'duration' => 5000,
                    ]
                ]);
            }

            Log::info('Usuario con perfil, accediendo al inicio', [
                'user_id' => $user->id,
                'estado' => $user->estado,
                'perfil_id' => $perfil->id
            ]);

            $usuarioData = [
                'id' => $user->id,
                'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                'apodo' => $user->apodo ?? $user->nombre ?? 'Usuario',
                'email' => $user->email ?? '',
                'avatar' => $user->avatar ?? '/images/shared/avatar-default.jpg',
                'verificado' => ($user->estado === 'verificado' || $user->email_verificado_en !== null),
                'rol' => $user->rol ?? 'usuario',
                'tiene_perfil' => true,
                'estado' => $user->estado ?? 'incompleto',
            ];

            $quickStats = $this->getQuickStats($user, $perfil);
            $panelInteligente = $this->getPanelInteligente($user, $perfil);
            $eventos = $this->getEventos($user);
            $mensajesRecientes = $this->getMensajesRecientes($user);
            $actividadReciente = $this->getActividadReciente($user);
            $publicacionesRecientes = $this->getPublicacionesRecientes($user);

            return Inertia::render('Usuario/Inicio', [
                'usuario' => $usuarioData,
                'quickStats' => $quickStats,
                'panelInteligente' => $panelInteligente,
                'eventos' => $eventos,
                'mensajesRecientes' => $mensajesRecientes,
                'actividadReciente' => $actividadReciente,
                'publicacionesRecientes' => $publicacionesRecientes,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar página de inicio', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null
            ]);

            return redirect()->route('login')->with('flash', [
                'toast' => [
                    'type' => 'error',
                    'title' => 'Error al cargar datos',
                    'message' => 'Error: ' . $e->getMessage(),
                    'duration' => 5000,
                ]
            ]);
        }
    }

    /**
     * Obtiene las estadísticas rápidas del usuario
     */
    protected function getQuickStats($user, $perfil)
    {
        try {
            $coincidenciasPendientes = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->where('estado', 'pendiente')
                ->count();

            $coincidenciasActivas = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->where('estado', 'coincidencia')
                ->count();

            $eventosCercanos = Evento::where('fecha', '>=', Carbon::now()->toDateString())
                ->where('ciudad', $user->ciudad)
                ->where('estado', 'publicado')
                ->count();

            $perfilVerificado = $perfil->esta_verificado ?? false;

            return [
                [
                    'icon' => 'pi-bolt',
                    'titulo' => 'Modo activo',
                    'desc' => 'Aumenta tu visibilidad y recibe más conexiones.',
                    'toggle' => true,
                    'activo' => $user->modo_activo ?? false,
                ],
                [
                    'icon' => 'pi-users',
                    'titulo' => 'Coincidencias',
                    'desc' => "Tienes {$coincidenciasPendientes} coincidencias nuevas y {$coincidenciasActivas} activas.",
                    'badge' => $coincidenciasPendientes > 0 ? $coincidenciasPendientes : null,
                ],
                [
                    'icon' => 'pi-calendar',
                    'titulo' => 'Eventos cercanos',
                    'desc' => $eventosCercanos > 0 
                        ? "{$eventosCercanos} eventos exclusivos disponibles cerca de ti."
                        : 'No hay eventos cercanos disponibles.',
                ],
                [
                    'icon' => 'pi-shield',
                    'titulo' => 'Perfil verificado',
                    'desc' => $perfilVerificado 
                        ? 'Tu perfil está verificado y tiene alta visibilidad.' 
                        : 'Verifica tu perfil para mayor visibilidad.',
                    'verificado' => $perfilVerificado,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error en getQuickStats', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                [
                    'icon' => 'pi-bolt',
                    'titulo' => 'Modo activo',
                    'desc' => 'Aumenta tu visibilidad y recibe más conexiones.',
                    'toggle' => true,
                    'activo' => false,
                ],
                [
                    'icon' => 'pi-users',
                    'titulo' => 'Coincidencias',
                    'desc' => 'Cargando coincidencias...',
                    'badge' => null,
                ],
                [
                    'icon' => 'pi-calendar',
                    'titulo' => 'Eventos cercanos',
                    'desc' => 'Cargando eventos...',
                ],
                [
                    'icon' => 'pi-shield',
                    'titulo' => 'Perfil verificado',
                    'desc' => 'Verifica tu perfil para mayor visibilidad.',
                    'verificado' => false,
                ],
            ];
        }
    }

    /**
     * Obtiene los datos del panel inteligente
     */
    protected function getPanelInteligente($user, $perfil)
    {
        try {
            $tokens = $user->tokens ?? 0;
            $nivelConfianza = $perfil->puntuacion_compatibilidad ?? 0;
            
            $nivelTexto = $nivelConfianza >= 90 ? 'Excelente' 
                        : ($nivelConfianza >= 75 ? 'Alta' 
                        : ($nivelConfianza >= 60 ? 'Media' : 'Baja'));

            return [
                [
                    'imagen' => '/images/match_inteligente.png',
                    'titulo' => 'Match inteligente',
                    'desc' => 'Perfiles compatibles basados en tus preferencias y ubicación.',
                    'link' => '#',
                ],
                [
                    'imagen' => '/images/geo.png',
                    'titulo' => 'Geolocalización discreta',
                    'desc' => 'Explora perfiles en tu zona aproximada con total privacidad.',
                    'link' => '#',
                ],
                [
                    'imagen' => '/images/tokens.png',
                    'titulo' => 'Fantasy Tokens',
                    'desc' => 'Tu saldo disponible para acceder a funciones premium y eventos.',
                    'link' => '#',
                    'extra' => "{$tokens} FT",
                ],
                [
                    'imagen' => '/images/confianza.png',
                    'titulo' => 'Nivel de confianza',
                    'desc' => "Tu perfil tiene nivel de confianza {$nivelTexto} ({$nivelConfianza}%).",
                    'link' => '#',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Error en getPanelInteligente', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [
                [
                    'imagen' => '/images/match_inteligente.png',
                    'titulo' => 'Match inteligente',
                    'desc' => 'Perfiles compatibles basados en tus preferencias y ubicación.',
                    'link' => '#',
                ],
                [
                    'imagen' => '/images/geo.png',
                    'titulo' => 'Geolocalización discreta',
                    'desc' => 'Explora perfiles en tu zona aproximada con total privacidad.',
                    'link' => '#',
                ],
                [
                    'imagen' => '/images/tokens.png',
                    'titulo' => 'Fantasy Tokens',
                    'desc' => 'Tu saldo disponible para acceder a funciones premium y eventos.',
                    'link' => '#',
                    'extra' => '0 FT',
                ],
                [
                    'imagen' => '/images/confianza.png',
                    'titulo' => 'Nivel de confianza',
                    'desc' => 'Cargando nivel de confianza...',
                    'link' => '#',
                ],
            ];
        }
    }

    /**
     * Obtiene eventos para el usuario (MÁXIMO 5, CON FECHAS EN ESPAÑOL)
     */
    protected function getEventos($user)
    {
        try {
            // Obtener los 5 eventos más próximos
            $eventos = Evento::where('fecha', '>=', Carbon::now()->toDateString())
                ->where('estado', 'publicado')
                ->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->limit(5)
                ->get();

            $resultados = [];

            foreach ($eventos as $evento) {
                $fecha = Carbon::parse($evento->fecha);
                $hora = $evento->hora ? Carbon::parse($evento->hora) : null;
                
                // Obtener mes en español
                $meses = [
                    'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
                    'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
                    'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
                    'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
                ];
                
                $mesNombre = $meses[$fecha->format('F')] ?? $fecha->format('F');
                $mesAbreviado = substr($mesNombre, 0, 3);
                
                // Obtener día de la semana en español
                $dias = [
                    0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                    4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
                ];
                $diaSemana = $dias[(int)$fecha->format('w')] ?? 'Día';
                
                // Calcular disponibilidad
                $reservasConfirmadas = $evento->reservas()->whereIn('estado', ['pendiente', 'confirmada'])->count();
                $disponible = $evento->capacidad - $reservasConfirmadas;
                $porcentajeOcupado = $evento->capacidad > 0 ? round(($reservasConfirmadas / $evento->capacidad) * 100) : 0;
                
                // Determinar si está casi lleno (menos del 15% disponible)
                $casiLleno = $disponible > 0 && $disponible <= ($evento->capacidad * 0.15);
                $estaLleno = $disponible <= 0;

                $resultados[] = [
                    'id' => $evento->id,
                    'nombre' => $evento->nombre,
                    'imagen' => $this->getImagenUrl($evento),
                    'ciudad' => $evento->ciudad,
                    'fecha' => $fecha->format('Y-m-d'),
                    'fecha_completa' => "{$diaSemana} {$fecha->format('d')} de {$mesNombre}",
                    'fecha_corta' => $fecha->format('d/m/Y'),
                    'mes_abreviado' => strtoupper($mesAbreviado),
                    'dia' => $fecha->format('d'),
                    'hora_formateada' => $hora ? $hora->format('g:i A') : 'Horario por definir',
                    'precio' => (float) $evento->precio,
                    'precio_formateado' => $evento->precio > 0 ? '$' . number_format($evento->precio, 0, ',', '.') : 'GRATIS',
                    'es_gratis' => $evento->precio <= 0,
                    'disponibles' => max(0, $disponible),
                    'porcentaje_ocupado' => $porcentajeOcupado,
                    'casi_lleno' => $casiLleno,
                    'esta_lleno' => $estaLleno,
                    'tipo' => $evento->tipo ?? 'evento',
                ];
            }

            return $resultados;
        } catch (\Exception $e) {
            Log::error('Error en getEventos', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [];
        }
    }

    /**
     * Obtiene la URL de la imagen del evento
     */
    protected function getImagenUrl($evento)
    {
        if (empty($evento->imagen)) {
            return '/images/eventos/evento-default.jpg';
        }

        // Si ya es una URL completa
        if (filter_var($evento->imagen, FILTER_VALIDATE_URL)) {
            return $evento->imagen;
        }

        // Si ya tiene /storage/
        if (strpos($evento->imagen, '/storage/') === 0) {
            return $evento->imagen;
        }

        // Si tiene storage/ sin slash inicial
        if (strpos($evento->imagen, 'storage/') === 0) {
            return '/' . $evento->imagen;
        }

        // Caso por defecto
        return asset('storage/' . ltrim($evento->imagen, '/'));
    }

    /**
     * Obtiene mensajes recientes del usuario
     */
    protected function getMensajesRecientes($user)
    {
        try {
            $chats = Chat::whereHas('coincidencia', function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->with(['coincidencia.usuarioA', 'coincidencia.usuarioB', 'coincidencia.usuarioA.perfil', 'coincidencia.usuarioB.perfil'])
                ->orderBy('ultimo_mensaje_en', 'desc')
                ->limit(4)
                ->get();

            $resultados = [];

            foreach ($chats as $chat) {
                $coincidencia = $chat->coincidencia;
                if (!$coincidencia) continue;

                $otroUsuario = $coincidencia->usuario_a_id === $user->id 
                    ? $coincidencia->usuarioB 
                    : $coincidencia->usuarioA;

                if (!$otroUsuario) continue;

                $ultimoMensaje = $chat->ultimo_mensaje;
                
                $noLeidos = Mensaje::where('chat_id', $chat->id)
                    ->where('remitente_id', '!=', $user->id)
                    ->where('leido', false)
                    ->count();

                $perfil = $otroUsuario->perfil;
                $nombre = $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario';
                
                if ($perfil && $perfil->tipo === 'pareja') {
                    $nombre = $nombre . ' & Pareja';
                }

                // Obtener avatar del otro usuario
                $avatar = '/images/inicio/avatar-default.jpg';
                if ($otroUsuario->foto_principal) {
                    $avatar = $this->getImagenUrlFromPath($otroUsuario->foto_principal);
                } elseif ($perfil && $perfil->foto_principal) {
                    $avatar = $this->getImagenUrlFromPath($perfil->foto_principal);
                }

                $resultados[] = [
                    'avatar' => $avatar,
                    'nombre' => $nombre,
                    'preview' => $ultimoMensaje ? $this->truncarTexto($ultimoMensaje->texto ?? 'Mensaje sin contenido', 40) : 'Sin mensajes',
                    'hora' => $ultimoMensaje ? $this->formatearTiempo($ultimoMensaje->created_at) : 'Recién',
                    'noLeidos' => $noLeidos > 0 ? $noLeidos : null,
                ];
            }

            return $resultados;
        } catch (\Exception $e) {
            Log::error('Error en getMensajesRecientes', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [];
        }
    }

    /**
     * Obtiene la URL pública de una ruta de imagen
     */
    protected function getImagenUrlFromPath($path)
    {
        if (empty($path)) {
            return '/images/inicio/avatar-default.jpg';
        }
        
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        if (strpos($path, '/storage/') === 0) {
            return $path;
        }
        
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Obtiene actividad reciente
     */
    protected function getActividadReciente($user)
    {
        try {
            $actividad = [];

            $nuevasCoincidencias = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->where('estado', 'coincidencia')
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->with(['usuarioA', 'usuarioB'])
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();

            foreach ($nuevasCoincidencias as $match) {
                $otroUsuario = $match->usuario_a_id === $user->id 
                    ? $match->usuarioB 
                    : $match->usuarioA;

                if ($otroUsuario) {
                    $nombre = $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario';
                    $actividad[] = [
                        'icon' => 'pi-heart-fill',
                        'titulo' => "Nueva coincidencia con {$nombre}",
                        'desc' => "Tienes una nueva coincidencia activa.",
                        'tiempo' => $this->formatearTiempo($match->created_at),
                    ];
                }
            }

            $nuevosMensajes = Mensaje::whereHas('chat.coincidencia', function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->where('remitente_id', '!=', $user->id)
                ->where('leido', false)
                ->with('remitente')
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();

            foreach ($nuevosMensajes as $mensaje) {
                $remitente = $mensaje->remitente;
                if ($remitente) {
                    $nombre = $remitente->nombre ?? $remitente->apodo ?? 'Usuario';
                    $actividad[] = [
                        'icon' => 'pi-comment',
                        'titulo' => "Nuevo mensaje de {$nombre}",
                        'desc' => $this->truncarTexto($mensaje->texto ?? 'Nuevo mensaje', 50),
                        'tiempo' => $this->formatearTiempo($mensaje->created_at),
                    ];
                }
            }

            if ($user->ciudad) {
                $eventosProximos = Evento::where('fecha', '>=', Carbon::now()->toDateString())
                    ->where('estado', 'publicado')
                    ->where('ciudad', $user->ciudad)
                    ->orderBy('fecha', 'asc')
                    ->limit(1)
                    ->get();

                foreach ($eventosProximos as $evento) {
                    $actividad[] = [
                        'icon' => 'pi-calendar',
                        'titulo' => "Evento cerca de ti: {$evento->nombre}",
                        'desc' => "Se llevará a cabo en {$evento->ciudad} el " . Carbon::parse($evento->fecha)->format('d/m/Y'),
                        'tiempo' => 'Próximo',
                    ];
                }
            }

            usort($actividad, function($a, $b) {
                if ($a['tiempo'] === 'Próximo') return 1;
                if ($b['tiempo'] === 'Próximo') return -1;
                return strcmp($a['tiempo'], $b['tiempo']);
            });

            return array_slice($actividad, 0, 4);
        } catch (\Exception $e) {
            Log::error('Error en getActividadReciente', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [];
        }
    }

    /**
     * Obtiene publicaciones recientes de la comunidad (SOLO VISUALIZACIÓN - ÚLTIMAS 6)
     */
    protected function getPublicacionesRecientes($user)
    {
        try {
            $publicaciones = Publicacion::with(['usuario'])
                ->where('estado', 'publicado')
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();

            $resultados = [];

            foreach ($publicaciones as $publicacion) {
                $usuario = $publicacion->usuario;
                
                // OBTENER AVATAR DEL USUARIO
                $avatar = '/images/shared/avatar-default.jpg';
                if ($usuario) {
                    $avatar = $usuario->avatar ?? '/images/shared/avatar-default.jpg';
                    if ($avatar && !str_starts_with($avatar, 'http') && !str_starts_with($avatar, '/')) {
                        $avatar = '/storage/' . $avatar;
                    }
                }

                // Obtener la URL de la imagen del post
                $mediaUrl = $publicacion->imagen ?? null;
                if ($mediaUrl && !str_starts_with($mediaUrl, 'http') && !str_starts_with($mediaUrl, '/')) {
                    $mediaUrl = '/storage/' . $mediaUrl;
                }

                // Detectar si es imagen o video
                $esVideo = false;
                $esImagen = false;
                
                if ($mediaUrl) {
                    $videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv', 'flv', 'mkv'];
                    $extension = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));
                    $esVideo = in_array($extension, $videoExtensions);
                    $esImagen = !$esVideo;
                }

                $resultados[] = [
                    'id' => $publicacion->id,
                    'usuario' => [
                        'id' => $usuario?->id,
                        'nombre' => $usuario?->nombre ?? $usuario?->apodo ?? 'Usuario desconocido',
                        'apodo' => $usuario?->apodo ?? $usuario?->nombre ?? 'Usuario',
                        'avatar' => $avatar,
                        'verificado' => ($usuario?->estado === 'verificado' || $usuario?->email_verificado_en !== null),
                        'es_creador' => ($usuario?->rol ?? '') === 'creador',
                    ],
                    'texto' => $publicacion->texto ?? '',
                    'imagen' => $mediaUrl,
                    'es_video' => $esVideo,
                    'es_imagen' => $esImagen,
                    'es_premium' => $publicacion->es_premium ?? false,
                    'likes' => $publicacion->likes ?? 0,
                    'comentarios_count' => $publicacion->comentarios_count ?? 0,
                    'tiempo' => $this->formatearTiempo($publicacion->created_at),
                    'created_at' => $publicacion->created_at->toISOString(),
                ];
            }

            return $resultados;
        } catch (\Exception $e) {
            Log::error('Error en getPublicacionesRecientes', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return [];
        }
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    protected function truncarTexto($texto, $limite)
    {
        if (empty($texto)) return '';
        if (strlen($texto) <= $limite) {
            return $texto;
        }
        return substr($texto, 0, $limite) . '…';
    }

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
}