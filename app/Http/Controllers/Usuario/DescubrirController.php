<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Coincidencia;
use App\Models\Chat;
use App\Models\Mensaje;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DescubrirController extends Controller
{
    /**
     * Muestra la página de descubrimiento
     */
    public function index()
    {
        Log::info('=== DESCUBRIR INDEX INICIO ===');
        
        if (!Auth::check()) {
            Log::warning('Usuario no autenticado, redirigiendo a login');
            return redirect()->route('login');
        }

        $user = Auth::user();
        Log::info('Usuario autenticado:', ['id' => $user->id, 'nombre' => $user->nombre, 'rol' => $user->rol]);

        if (!$user) {
            Auth::logout();
            return redirect()->route('login');
        }

        try {
            $perfil = Perfil::where('usuario_id', $user->id)->first();
            
            if (!$perfil) {
                Log::warning('Usuario sin perfil, redirigiendo a completar perfil');
                return redirect()->route('perfil.completar')->with('flash', [
                    'toast' => [
                        'type' => 'info',
                        'title' => 'Completa tu perfil',
                        'message' => 'Para descubrir otros perfiles, primero completa tu perfil.',
                        'duration' => 5000,
                    ]
                ]);
            }

            // Obtener sugerencias de perfiles
            $sugerencias = $this->getTodosLosPerfiles($user);
            Log::info('Sugerencias encontradas:', ['count' => count($sugerencias)]);

            // OBTENER QUIENES ME HAN DADO LIKE (sin match todavía)
            $likesRecibidos = $this->getLikesRecibidos($user);
            Log::info('Likes recibidos:', ['count' => count($likesRecibidos)]);

            // OBTENER MATCHES RECIENTES
            $matchesRecientes = $this->getMatchesRecientes($user);
            Log::info('Matches recientes:', ['count' => count($matchesRecientes)]);

            $usuarioData = [
                'id' => $user->id,
                'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                'apodo' => $user->apodo ?? $user->nombre ?? 'Usuario',
                'avatar' => $user->avatar,
                'verificado' => ($user->estado === 'verificado' || $user->estado === 'pendiente'),
                'rol' => $user->rol ?? 'usuario',
                'estado' => $user->estado ?? 'incompleto',
            ];

            $interesesDisponibles = $this->getInteresesDisponibles();

            return Inertia::render('Usuario/Match/Index', [
                'usuario' => $usuarioData,
                'matches' => [],
                'solicitudesPendientes' => [],
                'estadisticas' => [
                    'total_matches' => 0,
                    'matches_este_mes' => 0,
                    'pendientes' => 0,
                    'compatibilidad_promedio' => 0,
                ],
                'sugerencias' => $sugerencias,
                'interesesDisponibles' => $interesesDisponibles,
                'matchesRecientes' => $matchesRecientes,
                'filtrosActivos' => [
                    'distancia_max' => null,
                    'intereses' => [],
                ],
                'likesRecibidos' => $likesRecibidos,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar página de descubrimiento', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $user->id ?? null
            ]);

            return redirect()->route('inicio')->with('flash', [
                'toast' => [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'No se pudieron cargar los perfiles. Por favor, intenta nuevamente.',
                    'duration' => 5000,
                ]
            ]);
        }
    }

    /**
     * OBTENER MATCHES RECIENTES
     */
    protected function getMatchesRecientes($user)
    {
        try {
            // Buscar coincidencias donde el usuario es parte y el estado es 'coincidencia'
            $matches = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->where('estado', 'coincidencia')
                ->with(['usuarioA', 'usuarioA.perfil', 'usuarioA.perfil.fotos', 
                        'usuarioB', 'usuarioB.perfil', 'usuarioB.perfil.fotos'])
                ->orderBy('coincidio_en', 'desc')
                ->orderBy('updated_at', 'desc')
                ->take(8)
                ->get();

            $resultados = [];

            foreach ($matches as $match) {
                // Determinar quién es el otro usuario
                $otroUsuario = $match->usuario_a_id === $user->id 
                    ? $match->usuarioB 
                    : $match->usuarioA;

                if (!$otroUsuario) continue;

                $perfil = $otroUsuario->perfil;
                $nombre = $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario';
                
                $fotoPrincipal = $this->getFotoPrincipal($perfil);
                
                // Buscar el chat asociado
                $chat = Chat::where('coincidencia_id', $match->id)->first();

                $resultados[] = [
                    'usuario_id' => $otroUsuario->id,
                    'nombre' => $nombre,
                    'imagen' => $fotoPrincipal,
                    'distancia' => 'Cerca de ti',
                    'chat_id' => $chat?->id,
                    'match_id' => $match->id,
                    'compatibilidad' => $match->compatibilidad ?? 0,
                    'coincidio_en' => $match->coincidio_en,
                    'verificado' => $perfil?->esta_verificado ?? false,
                    'ciudad' => $perfil?->ubicacion_ciudad ?? $otroUsuario->ciudad ?? 'Ciudad no especificada',
                    'descripcion' => $perfil?->descripcion ?? 'Sin descripción',
                    'edad' => $otroUsuario->fecha_nacimiento ? Carbon::parse($otroUsuario->fecha_nacimiento)->age : null,
                    'intereses' => $this->normalizarIntereses($perfil?->intereses ?? []),
                ];
            }

            return $resultados;

        } catch (\Exception $e) {
            Log::error('Error al obtener matches recientes:', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return [];
        }
    }

    /**
     * OBTENER QUIENES ME HAN DADO LIKE (sin match todavía)
     */
    protected function getLikesRecibidos($user)
    {
        try {
            $likes = Coincidencia::where('usuario_b_id', $user->id)
                ->where('estado', 'pendiente')
                ->where('origen', 'like')
                ->with(['usuarioA', 'usuarioA.perfil', 'usuarioA.perfil.fotos'])
                ->orderBy('created_at', 'desc')
                ->get();

            $resultados = [];

            foreach ($likes as $coincidencia) {
                $otroUsuario = $coincidencia->usuarioA;
                if (!$otroUsuario) continue;

                $perfil = $otroUsuario->perfil;
                $nombre = $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario';
                $edad = $otroUsuario->fecha_nacimiento ? Carbon::parse($otroUsuario->fecha_nacimiento)->age : null;
                
                $fotoPrincipal = $this->getFotoPrincipal($perfil);
                
                $misIntereses = $user->perfil ? ($user->perfil->intereses ?? []) : [];
                $susIntereses = $perfil ? ($perfil->intereses ?? []) : [];
                $compatibilidad = $this->calcularCompatibilidad($misIntereses, $susIntereses);

                $resultados[] = [
                    'id' => $coincidencia->id,
                    'usuario_id' => $otroUsuario->id,
                    'nombre' => $nombre,
                    'apodo' => $otroUsuario->apodo,
                    'edad' => $edad,
                    'imagen' => $fotoPrincipal,
                    'tipo' => $perfil?->tipo ?? 'personal',
                    'verificado' => $perfil?->esta_verificado ?? false,
                    'compatibilidad' => $compatibilidad,
                    'created_at' => $coincidencia->created_at,
                ];
            }

            return $resultados;

        } catch (\Exception $e) {
            Log::error('Error al obtener likes recibidos:', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return [];
        }
    }

    /**
     * ACEPTAR UN LIKE (responder a un like recibido)
     */
    public function aceptarLike(Request $request)
    {
        try {
            $user = Auth::user();
            $coincidenciaId = $request->input('coincidencia_id');

            if (!$coincidenciaId) {
                return response()->json(['success' => false, 'message' => 'Coincidencia no especificada'], 422);
            }

            $coincidencia = Coincidencia::where('id', $coincidenciaId)
                ->where('usuario_b_id', $user->id)
                ->where('estado', 'pendiente')
                ->where('origen', 'like')
                ->with(['usuarioA', 'usuarioB'])
                ->first();

            if (!$coincidencia) {
                return response()->json(['success' => false, 'message' => 'Like no encontrado o ya procesado'], 404);
            }

            $result = DB::transaction(function () use ($coincidencia, $user) {
                $ahora = now();
                
                $misIntereses = $user->perfil ? ($user->perfil->intereses ?? []) : [];
                $susIntereses = $coincidencia->usuarioA->perfil ? ($coincidencia->usuarioA->perfil->intereses ?? []) : [];
                $compatibilidad = $this->calcularCompatibilidad($misIntereses, $susIntereses);
                
                $coincidencia->update([
                    'estado' => 'coincidencia',
                    'coincidio_en' => $ahora,
                    'compatibilidad' => $compatibilidad,
                ]);

                $inversa = Coincidencia::firstOrCreate(
                    ['usuario_a_id' => $coincidencia->usuario_a_id, 'usuario_b_id' => $user->id],
                    [
                        'estado' => 'coincidencia',
                        'coincidio_en' => $ahora,
                        'origen' => 'match',
                        'compatibilidad' => $compatibilidad,
                    ]
                );

                if (!$inversa->wasRecentlyCreated && $inversa->estado !== 'coincidencia') {
                    $inversa->update([
                        'estado' => 'coincidencia',
                        'coincidio_en' => $ahora,
                        'compatibilidad' => $compatibilidad,
                    ]);
                }

                $chat = Chat::firstOrCreate(
                    ['coincidencia_id' => $coincidencia->id],
                    ['estado' => 'activo', 'ultimo_mensaje_en' => $ahora]
                );

                Log::info('Like aceptado - ¡Match!', [
                    'usuario_a' => $coincidencia->usuario_a_id,
                    'usuario_b' => $user->id,
                    'chat_id' => $chat->id,
                    'compatibilidad' => $compatibilidad,
                ]);

                return [
                    'chat_id' => $chat->id,
                    'coincidencia_id' => $coincidencia->id,
                    'perfil' => [
                        'id' => $coincidencia->usuarioA->id,
                        'nombre' => $coincidencia->usuarioA->nombre ?? $coincidencia->usuarioA->apodo ?? 'Usuario',
                        'avatar' => $coincidencia->usuarioA->avatar,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'message' => '¡Es un match!',
                'chat_id' => $result['chat_id'],
                'coincidencia_id' => $result['coincidencia_id'],
                'perfil' => $result['perfil'],
            ]);

        } catch (\Exception $e) {
            Log::error('Error al aceptar like:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $user->id ?? null,
                'coincidencia_id' => $request->input('coincidencia_id')
            ]);
            return response()->json(['success' => false, 'message' => 'No se pudo aceptar el like: ' . $e->getMessage()], 500);
        }
    }

    /**
     * RECHAZAR UN LIKE (ignorar un like recibido)
     */
    public function rechazarLike(Request $request)
    {
        try {
            $user = Auth::user();
            $coincidenciaId = $request->input('coincidencia_id');

            if (!$coincidenciaId) {
                return response()->json(['success' => false, 'message' => 'Coincidencia no especificada'], 422);
            }

            $coincidencia = Coincidencia::where('id', $coincidenciaId)
                ->where('usuario_b_id', $user->id)
                ->where('estado', 'pendiente')
                ->where('origen', 'like')
                ->first();

            if (!$coincidencia) {
                return response()->json(['success' => false, 'message' => 'Like no encontrado'], 404);
            }

            $coincidencia->update(['estado' => 'rechazado']);

            Log::info('Like rechazado', [
                'user_id' => $user->id,
                'coincidencia_id' => $coincidenciaId,
            ]);

            return response()->json(['success' => true, 'message' => 'Like rechazado']);

        } catch (\Exception $e) {
            Log::error('Error al rechazar like:', [
                'message' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'coincidencia_id' => $request->input('coincidencia_id')
            ]);
            return response()->json(['success' => false, 'message' => 'No se pudo rechazar el like: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene TODOS los perfiles disponibles
     */
    protected function getTodosLosPerfiles($user)
    {
        Log::info('=== getTodosLosPerfiles INICIO ===', ['user_id' => $user->id]);
        
        try {
            $perfilUsuario = Perfil::where('usuario_id', $user->id)->first();
            
            if (!$perfilUsuario) {
                Log::warning('Usuario sin perfil');
                return [];
            }

            $misIntereses = $perfilUsuario->intereses ?? [];
            Log::info('Intereses del usuario actual:', ['intereses' => $misIntereses]);

            $idsExcluir = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->get()
                ->map(function($coincidencia) use ($user) {
                    if ($coincidencia->usuario_a_id == $user->id) {
                        return $coincidencia->usuario_b_id;
                    }
                    return $coincidencia->usuario_a_id;
                })
                ->toArray();

            $idsExcluir[] = $user->id;
            $idsExcluir = array_unique($idsExcluir);
            
            Log::info('IDs a excluir:', ['count' => count($idsExcluir), 'ids' => $idsExcluir]);

            $perfiles = Perfil::with(['usuario', 'fotos' => function($query) {
                    $query->where('es_principal', true);
                }])
                ->whereNotIn('usuario_id', $idsExcluir)
                ->whereHas('usuario', function($query) {
                    $query->where('estado', '!=', 'bloqueado')
                          ->where('estado', '!=', 'inactivo');
                })
                ->get();

            Log::info('Perfiles encontrados:', ['count' => $perfiles->count()]);

            $resultados = [];

            foreach ($perfiles as $p) {
                $usuario = $p->usuario;
                if (!$usuario) continue;

                $susIntereses = $p->intereses ?? [];
                $interesesEnComun = $this->contarInteresesEnComun($misIntereses, $susIntereses);
                
                $compatibilidad = $this->calcularCompatibilidad($misIntereses, $susIntereses);
                
                $fotoPrincipal = $this->getFotoPrincipal($p);
                $interesesNormalizados = $this->normalizarIntereses($susIntereses);

                $resultados[] = [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre ?? $usuario->apodo ?? 'Usuario',
                    'apodo' => $usuario->apodo,
                    'edad' => $usuario->fecha_nacimiento ? Carbon::parse($usuario->fecha_nacimiento)->age : null,
                    'imagen' => $fotoPrincipal,
                    'tipo' => $p->tipo ?? 'personal',
                    'verificado' => $p->esta_verificado ?? false,
                    'compatibilidad' => $compatibilidad,
                    'intereses' => array_slice($interesesNormalizados, 0, 3),
                    'interesesExtra' => count($interesesNormalizados) > 3 ? array_slice($interesesNormalizados, 3) : [],
                    'enLinea' => $usuario->esta_activo ?? false,
                    'ciudad' => $p->ubicacion_ciudad ?? $usuario->ciudad ?? 'Ciudad no especificada',
                    'descripcion' => $p->descripcion ?? 'Sin descripción',
                ];
            }

            usort($resultados, function($a, $b) {
                return $b['compatibilidad'] - $a['compatibilidad'];
            });

            Log::info('Resultados finales:', ['count' => count($resultados)]);

            return $resultados;

        } catch (\Exception $e) {
            Log::error('Error en getTodosLosPerfiles:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $user->id
            ]);
            return [];
        }
    }

    /**
     * Cuenta intereses en común
     */
    private function contarInteresesEnComun($intereses1, $intereses2)
    {
        if (empty($intereses1) || empty($intereses2)) {
            return 0;
        }

        $labels1 = array_map(function($item) {
            return strtolower(trim(is_array($item) ? ($item['label'] ?? $item['nombre'] ?? $item['valor'] ?? '') : $item));
        }, $intereses1);

        $labels2 = array_map(function($item) {
            return strtolower(trim(is_array($item) ? ($item['label'] ?? $item['nombre'] ?? $item['valor'] ?? '') : $item));
        }, $intereses2);

        return count(array_intersect($labels1, $labels2));
    }

    /**
     * Calcula compatibilidad
     */
    private function calcularCompatibilidad($misIntereses, $susIntereses)
    {
        $interesesEnComun = $this->contarInteresesEnComun($misIntereses, $susIntereses);

        if ($interesesEnComun === 0) {
            return 0;
        }

        $misLabels = $this->extraerLabels($misIntereses);
        $susLabels = $this->extraerLabels($susIntereses);

        if (empty($misLabels) || empty($susLabels)) {
            return 0;
        }

        $union = count(array_unique(array_merge($misLabels, $susLabels)));
        $jaccard = $union > 0 ? $interesesEnComun / $union : 0;
        
        return (int) round(10 + ($jaccard * 88));
    }

    /**
     * Extrae labels de intereses
     */
    private function extraerLabels($intereses)
    {
        if (empty($intereses)) return [];

        $labels = [];
        foreach ($intereses as $interes) {
            if (is_array($interes)) {
                $label = $interes['label'] ?? $interes['nombre'] ?? $interes['valor'] ?? null;
            } else {
                $label = (string) $interes;
            }
            if ($label) {
                $labels[] = strtolower(trim($label));
            }
        }

        return array_unique($labels);
    }

    /**
     * Normaliza intereses a formato {icon, label}
     */
    private function normalizarIntereses($intereses)
    {
        if (empty($intereses)) return [];

        $iconos = [
            'viajes' => 'pi-send',
            'viajar' => 'pi-send',
            'viaje' => 'pi-send',
            'fiestas privadas' => 'pi-bolt',
            'fiestas' => 'pi-bolt',
            'cenas' => 'pi-star',
            'comida' => 'pi-star',
            'gastronomía' => 'pi-star',
            'gastronomia' => 'pi-star',
            'conexiones reales' => 'pi-link',
            'conexiones' => 'pi-link',
            'eventos vip' => 'pi-star-fill',
            'eventos' => 'pi-star-fill',
            'vip' => 'pi-star-fill',
            'música' => 'pi-volume-up',
            'musica' => 'pi-volume-up',
            'conciertos' => 'pi-volume-up',
            'wellness' => 'pi-heart',
            'bienestar' => 'pi-heart',
            'salud' => 'pi-heart',
            'fitness' => 'pi-heart',
            'streaming' => 'pi-play',
            'series' => 'pi-play',
            'cine' => 'pi-play',
            'socializar' => 'pi-users',
            'amigos' => 'pi-users',
        ];

        $resultados = [];
        foreach ($intereses as $interes) {
            $label = is_array($interes) ? ($interes['label'] ?? $interes['nombre'] ?? $interes['valor'] ?? 'Interés') : (string) $interes;
            $icono = $iconos[strtolower(trim($label))] ?? 'pi-tag';
            $resultados[] = [
                'icon' => $icono,
                'label' => $label
            ];
        }

        return $resultados;
    }

    /**
     * Obtiene la foto principal de un perfil
     */
    private function getFotoPrincipal($perfil)
    {
        if (!$perfil) return '/images/shared/avatar-default.jpg';

        // Intentar obtener la foto principal de la relación
        $foto = $perfil->fotos()->where('es_principal', true)->first();
        if ($foto) {
            $url = $foto->url ?? $foto->ruta_foto ?? null;
            if ($url) {
                if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
                    return '/storage/' . $url;
                }
                return $url;
            }
        }

        // Si no hay foto principal, intentar obtener la primera foto
        $foto = $perfil->fotos->first();
        if ($foto) {
            $url = $foto->url ?? $foto->ruta_foto ?? null;
            if ($url) {
                if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
                    return '/storage/' . $url;
                }
                return $url;
            }
        }

        return '/images/shared/avatar-default.jpg';
    }

    /**
     * Obtiene intereses disponibles para filtros
     */
    private function getInteresesDisponibles()
    {
        return [
            ['icon' => 'pi-send', 'label' => 'Viajes'],
            ['icon' => 'pi-bolt', 'label' => 'Fiestas privadas'],
            ['icon' => 'pi-star', 'label' => 'Cenas'],
            ['icon' => 'pi-link', 'label' => 'Conexiones reales'],
            ['icon' => 'pi-star-fill', 'label' => 'Eventos VIP'],
            ['icon' => 'pi-volume-up', 'label' => 'Música'],
            ['icon' => 'pi-heart', 'label' => 'Wellness'],
            ['icon' => 'pi-play', 'label' => 'Streaming'],
            ['icon' => 'pi-users', 'label' => 'Socializar'],
        ];
    }

    // ============================================================
    // ACCIONES DEL SWIPE
    // ============================================================

    /**
     * Pasar al siguiente perfil
     */
    public function pasar(Request $request)
    {
        try {
            $user = Auth::user();
            $otroId = (int) $request->input('perfil_id');

            Log::info('=== PASAR INICIO ===', [
                'user_id' => $user->id,
                'perfil_id' => $otroId
            ]);

            if (!$otroId || $otroId === $user->id) {
                return response()->json(['success' => false, 'message' => 'Perfil inválido.'], 422);
            }

            $otroUsuario = User::find($otroId);
            if (!$otroUsuario) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
            }

            Coincidencia::updateOrCreate(
                ['usuario_a_id' => $user->id, 'usuario_b_id' => $otroId],
                ['estado' => 'rechazado', 'origen' => 'pass']
            );

            Log::info('Usuario pasó un perfil', ['user_id' => $user->id, 'perfil_id' => $otroId]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Error en pasar:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Conectar (dar like) a un perfil
     */
    public function conectar(Request $request)
    {
        return $this->procesarLike($request, 'like');
    }

    /**
     * Destacar (super-like) a un perfil
     */
    public function destacar(Request $request)
    {
        return $this->procesarLike($request, 'super');
    }

    /**
     * Procesa un like (lógica compartida entre conectar y destacar)
     */
    private function procesarLike(Request $request, string $origen)
    {
        try {
            $user = Auth::user();
            $otroId = (int) $request->input('perfil_id');

            Log::info('=== PROCESAR LIKE INICIO ===', [
                'user_id' => $user->id,
                'perfil_id' => $otroId,
                'origen' => $origen
            ]);

            if (!$otroId || $otroId === $user->id) {
                return response()->json(['success' => false, 'message' => 'Perfil inválido.'], 422);
            }

            $otroUsuario = User::find($otroId);
            if (!$otroUsuario) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
            }

            return DB::transaction(function () use ($user, $otroId, $otroUsuario, $origen) {
                $misIntereses = $user->perfil ? ($user->perfil->intereses ?? []) : [];
                $susIntereses = $otroUsuario->perfil ? ($otroUsuario->perfil->intereses ?? []) : [];
                $compatibilidad = $this->calcularCompatibilidad($misIntereses, $susIntereses);

                $miCoincidencia = Coincidencia::firstOrCreate(
                    ['usuario_a_id' => $user->id, 'usuario_b_id' => $otroId],
                    [
                        'estado' => 'pendiente',
                        'origen' => $origen,
                        'compatibilidad' => $compatibilidad,
                    ]
                );

                Log::info('Mi coincidencia:', [
                    'id' => $miCoincidencia->id,
                    'wasRecentlyCreated' => $miCoincidencia->wasRecentlyCreated,
                    'estado' => $miCoincidencia->estado,
                    'origen' => $miCoincidencia->origen,
                    'compatibilidad' => $miCoincidencia->compatibilidad,
                ]);

                if (!$miCoincidencia->wasRecentlyCreated && $miCoincidencia->estado !== 'coincidencia') {
                    $miCoincidencia->update([
                        'estado' => 'pendiente',
                        'origen' => $origen,
                        'compatibilidad' => $compatibilidad,
                    ]);
                    Log::info('Coincidencia actualizada a pendiente con compatibilidad: ' . $compatibilidad);
                }

                $suCoincidencia = Coincidencia::where('usuario_a_id', $otroId)
                    ->where('usuario_b_id', $user->id)
                    ->whereIn('estado', ['pendiente', 'coincidencia'])
                    ->first();

                Log::info('Coincidencia del otro usuario:', [
                    'exists' => $suCoincidencia ? 'si' : 'no',
                    'estado' => $suCoincidencia ? $suCoincidencia->estado : 'null'
                ]);

                $esMatch = (bool) $suCoincidencia;

                if (!$esMatch) {
                    Log::info('Like registrado (sin match todavía)');
                    return response()->json(['success' => true, 'match' => false]);
                }

                $ahora = now();
                $miCoincidencia->update([
                    'estado' => 'coincidencia',
                    'coincidio_en' => $ahora,
                    'compatibilidad' => $compatibilidad,
                ]);
                $suCoincidencia->update([
                    'estado' => 'coincidencia',
                    'coincidio_en' => $ahora,
                    'compatibilidad' => $compatibilidad,
                ]);

                $chat = Chat::firstOrCreate(
                    ['coincidencia_id' => $miCoincidencia->id],
                    ['estado' => 'activo', 'ultimo_mensaje_en' => $ahora]
                );

                Log::info('¡Match creado!', [
                    'chat_id' => $chat->id,
                    'compatibilidad' => $compatibilidad,
                ]);

                // 🔔 Notificar a AMBOS usuarios del match
                Notificacion::crear(
                    usuarioId: $otroId,
                    emisorId: $user->id,
                    tipo: 'match',
                    mensaje: "¡Tienes un nuevo match con <strong>{$user->nombre}</strong>!",
                    link: '/mensajes',
                );
                Notificacion::crear(
                    usuarioId: $user->id,
                    emisorId: $otroId,
                    tipo: 'match',
                    mensaje: "¡Tienes un nuevo match con <strong>{$otroUsuario->nombre}</strong>!",
                    link: '/mensajes',
                );

                return response()->json([
                    'success' => true,
                    'match' => true,
                    'chat_id' => $chat->id,
                    'coincidencia_id' => $miCoincidencia->id,
                    'compatibilidad' => $compatibilidad,
                    'perfil' => [
                        'id' => $otroUsuario->id,
                        'nombre' => $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario',
                        'avatar' => $otroUsuario->avatar,
                    ],
                ]);
            });

        } catch (\Exception $e) {
            Log::error('Error en procesarLike:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error al procesar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Envía mensaje flash después de un match
     */
    public function enviarMensajeFlash(Request $request)
    {
        try {
            $data = $request->validate([
                'chat_id' => ['required', 'integer', 'exists:chats,id'],
                'texto' => ['required', 'string', 'max:300'],
            ]);

            $user = Auth::user();
            Log::info('=== ENVIAR MENSAJE FLASH ===', [
                'user_id' => $user->id,
                'chat_id' => $data['chat_id']
            ]);

            $chat = Chat::with('coincidencia')->find($data['chat_id']);

            if (!$chat || !$chat->coincidencia) {
                return response()->json(['success' => false, 'message' => 'Conversación no encontrada.'], 404);
            }

            $coincidencia = $chat->coincidencia;
            $esParticipante = in_array($user->id, [$coincidencia->usuario_a_id, $coincidencia->usuario_b_id]);

            if (!$esParticipante) {
                return response()->json(['success' => false, 'message' => 'No tienes acceso a esta conversación.'], 403);
            }

            $mensaje = Mensaje::create([
                'chat_id' => $chat->id,
                'remitente_id' => $user->id,
                'texto' => $data['texto'],
                'leido' => false,
                'estado' => 'enviado',
            ]);

            $chat->update(['ultimo_mensaje_en' => now()]);

            Log::info('Mensaje flash enviado', ['mensaje_id' => $mensaje->id]);

            return response()->json([
                'success' => true,
                'mensaje_id' => $mensaje->id,
                'chat_id' => $chat->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en enviarMensajeFlash:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json(['success' => false, 'message' => 'No se pudo enviar el mensaje: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Obtener información de un perfil para el modal
     */
    public function obtenerPerfil(Request $request)
    {
        try {
            $userId = $request->input('usuario_id');
            $chatId = $request->input('chat_id');
            
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Usuario no especificado'], 422);
            }

            // Buscar el usuario con su perfil y fotos
            $user = User::with(['perfil', 'perfil.fotos'])->find($userId);
            
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            }

            $perfil = $user->perfil;
            
            // Obtener la foto principal
            $fotoPrincipal = $this->getFotoPrincipal($perfil);
            
            // Normalizar intereses
            $intereses = $this->normalizarIntereses($perfil->intereses ?? []);
            
            // Si no se proporcionó chat_id, buscar el chat
            if (!$chatId && $perfil) {
                $coincidencia = Coincidencia::where(function($query) use ($userId, $user) {
                        $query->where('usuario_a_id', $userId)
                              ->where('usuario_b_id', $user->id);
                    })
                    ->orWhere(function($query) use ($userId, $user) {
                        $query->where('usuario_a_id', $user->id)
                              ->where('usuario_b_id', $userId);
                    })
                    ->where('estado', 'coincidencia')
                    ->first();
                    
                if ($coincidencia) {
                    $chat = Chat::where('coincidencia_id', $coincidencia->id)->first();
                    $chatId = $chat?->id;
                }
            }

            return response()->json([
                'success' => true,
                'perfil' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                    'apodo' => $user->apodo,
                    'edad' => $user->fecha_nacimiento ? Carbon::parse($user->fecha_nacimiento)->age : null,
                    'imagen' => $fotoPrincipal,
                    'tipo' => $perfil?->tipo ?? 'personal',
                    'verificado' => $perfil?->esta_verificado ?? false,
                    'ciudad' => $perfil?->ubicacion_ciudad ?? $user->ciudad ?? 'Ciudad no especificada',
                    'descripcion' => $perfil?->descripcion ?? 'Sin descripción',
                    'intereses' => $intereses,
                    'chat_id' => $chatId,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener perfil:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->input('usuario_id')
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Error al obtener perfil: ' . $e->getMessage()
            ], 500);
        }
    }
}