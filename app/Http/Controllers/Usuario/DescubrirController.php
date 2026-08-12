<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Coincidencia;
use App\Models\Fotos;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DescubrirController extends Controller
{
    /**
     * Muestra la página de descubrimiento
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
                        'message' => 'Para descubrir otros perfiles, primero completa tu perfil.',
                        'duration' => 5000,
                    ]
                ]);
            }

            // Obtener perfiles para descubrir
            $perfiles = $this->getPerfilesParaDescubrir($user, $perfil);
            
            // Obtener matches recientes (5)
            $matchesRecientes = $this->getMatchesRecientes($user);
            
            // Obtener estadísticas de actividad
            $actividadZona = $this->getActividadZona($user, $perfil);

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

            Log::info('Cargando página de descubrimiento', [
                'user_id' => $user->id,
                'perfiles_encontrados' => count($perfiles),
                'matches_encontrados' => count($matchesRecientes)
            ]);

            return Inertia::render('Usuario/Match', [
                'usuario' => $usuarioData,
                'perfiles' => $perfiles,
                'matchesRecientes' => $matchesRecientes,
                'actividadZona' => $actividadZona,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar página de descubrimiento', [
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
                    'message' => 'No se pudieron cargar los perfiles. Por favor, intenta nuevamente.',
                    'duration' => 5000,
                ]
            ]);
        }
    }

    /**
     * Obtiene perfiles para descubrir
     */
    protected function getPerfilesParaDescubrir($user, $perfil)
    {
        try {
            // Obtener IDs de usuarios ya vistos o con los que ya hay coincidencia
            $vistos = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->pluck('usuario_a_id', 'usuario_b_id')
                ->flatten()
                ->unique()
                ->toArray();

            // Obtener IDs de usuarios que ya han interactuado
            $excluirIds = array_merge([$user->id], $vistos);

            // Obtener perfiles de otros usuarios
            $perfilesQuery = Perfil::with(['usuario', 'fotos' => function($query) {
                    $query->where('es_principal', true);
                }])
                ->where('usuario_id', '!=', $user->id)
                ->whereNotIn('usuario_id', $excluirIds)
                ->whereHas('usuario', function($query) {
                    $query->whereIn('estado', ['verificado', 'pendiente', 'activo']);
                });

            // Aplicar filtros según el perfil del usuario
            if ($perfil->tipo === 'pareja') {
                $perfilesQuery->whereIn('tipo', ['personal', 'pareja']);
            } else {
                if (isset($perfil->metadatos['busca']) && $perfil->metadatos['busca'] === 'parejas') {
                    $perfilesQuery->where('tipo', 'pareja');
                } else {
                    $perfilesQuery->whereIn('tipo', ['personal', 'pareja']);
                }
            }

            $perfiles = $perfilesQuery->limit(10)->get();

            $resultados = [];

            foreach ($perfiles as $p) {
                $usuario = $p->usuario;
                if (!$usuario) continue;

                $edad = $usuario->fecha_nacimiento ? Carbon::parse($usuario->fecha_nacimiento)->age : null;
                $nombreCompleto = $usuario->nombre ?? $usuario->apodo ?? 'Usuario';
                
                $fotoPrincipal = null;
                $foto = $p->fotos->first();
                if ($foto) {
                    $fotoPrincipal = $foto->url ?? $foto->ruta_foto ?? null;
                }

                if ($fotoPrincipal) {
                    if (!str_starts_with($fotoPrincipal, 'http') && !str_starts_with($fotoPrincipal, '/')) {
                        $fotoPrincipal = '/storage/' . $fotoPrincipal;
                    }
                }

                $compatibilidad = rand(70, 95);
                $distancia = $this->calcularDistancia($user, $p);

                $resultados[] = [
                    'id' => $usuario->id,
                    'nombre' => $nombreCompleto,
                    'ciudad' => $p->ubicacion_ciudad ?? $usuario->ciudad ?? 'Ciudad no especificada',
                    'tipo' => $p->tipo === 'pareja' ? 'Pareja' : 'Personal',
                    'imagen' => $fotoPrincipal ?? '/images/descubrir/avatar-default.jpg',
                    'descripcion' => $p->descripcion ?? 'Sin descripción',
                    'intereses' => $this->formatearIntereses($p->intereses ?? []),
                    'interesesExtra' => $this->formatearInteresesExtra($p->intereses ?? []),
                    'distancia' => $distancia,
                    'compatibilidad' => $compatibilidad,
                    'enLinea' => $usuario->esta_activo ?? false,
                    'verificado' => $p->esta_verificado ?? false,
                ];
            }

            if (empty($resultados)) {
                $resultados = $this->getPerfilesFallback($user);
            }

            return $resultados;

        } catch (\Exception $e) {
            Log::error('Error al obtener perfiles para descubrir', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return $this->getPerfilesFallback($user);
        }
    }

    /**
     * Obtiene perfiles de fallback cuando no hay resultados
     */
    protected function getPerfilesFallback($user)
    {
        try {
            $perfiles = Perfil::with(['usuario', 'fotos' => function($query) {
                    $query->where('es_principal', true);
                }])
                ->where('usuario_id', '!=', $user->id)
                ->whereHas('usuario', function($query) {
                    $query->whereIn('estado', ['verificado', 'pendiente', 'activo']);
                })
                ->limit(5)
                ->get();

            $resultados = [];

            foreach ($perfiles as $p) {
                $usuario = $p->usuario;
                if (!$usuario) continue;

                $nombreCompleto = $usuario->nombre ?? $usuario->apodo ?? 'Usuario';
                
                $fotoPrincipal = null;
                $foto = $p->fotos->first();
                if ($foto) {
                    $fotoPrincipal = $foto->url ?? $foto->ruta_foto ?? null;
                }

                if ($fotoPrincipal && !str_starts_with($fotoPrincipal, 'http') && !str_starts_with($fotoPrincipal, '/')) {
                    $fotoPrincipal = '/storage/' . $fotoPrincipal;
                }

                $resultados[] = [
                    'id' => $usuario->id,
                    'nombre' => $nombreCompleto,
                    'ciudad' => $p->ubicacion_ciudad ?? $usuario->ciudad ?? 'Ciudad no especificada',
                    'tipo' => $p->tipo === 'pareja' ? 'Pareja' : 'Personal',
                    'imagen' => $fotoPrincipal ?? '/images/descubrir/avatar-default.jpg',
                    'descripcion' => $p->descripcion ?? 'Sin descripción',
                    'intereses' => [],
                    'interesesExtra' => [],
                    'distancia' => 'A ' . rand(1, 25) . ' km de ti',
                    'compatibilidad' => rand(50, 85),
                    'enLinea' => false,
                    'verificado' => $p->esta_verificado ?? false,
                ];
            }

            return $resultados;
        } catch (\Exception $e) {
            Log::error('Error en perfiles fallback', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            return [];
        }
    }

    /**
     * Obtiene matches recientes del usuario (los 5 más recientes)
     * ✅ SIN FALLBACK - Si no hay matches, devuelve array vacío
     */
    protected function getMatchesRecientes($user)
    {
        try {
            $coincidencias = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->where('estado', 'coincidencia')
                ->with(['usuarioA', 'usuarioB', 'usuarioA.perfil', 'usuarioB.perfil', 'usuarioA.perfil.fotos'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $resultados = [];

            foreach ($coincidencias as $coincidencia) {
                $otroUsuario = $coincidencia->usuario_a_id === $user->id 
                    ? $coincidencia->usuarioB 
                    : $coincidencia->usuarioA;

                if (!$otroUsuario) continue;

                $perfil = $otroUsuario->perfil;
                $nombre = $otroUsuario->nombre ?? $otroUsuario->apodo ?? 'Usuario';
                $edad = $otroUsuario->fecha_nacimiento ? Carbon::parse($otroUsuario->fecha_nacimiento)->age : null;
                
                $fotoPrincipal = null;
                if ($perfil) {
                    $foto = $perfil->fotos->first();
                    if ($foto) {
                        $fotoPrincipal = $foto->url ?? $foto->ruta_foto ?? null;
                    }
                }

                if ($fotoPrincipal && !str_starts_with($fotoPrincipal, 'http') && !str_starts_with($fotoPrincipal, '/')) {
                    $fotoPrincipal = '/storage/' . $fotoPrincipal;
                }

                // Si no hay foto, usar avatar por defecto
                if (!$fotoPrincipal) {
                    $fotoPrincipal = '/images/shared/avatar-default.jpg';
                }

                $nombreMostrar = $nombre;
                if ($perfil && $perfil->tipo === 'pareja') {
                    $nombreMostrar = $nombre . ' & ' . ($edad ? $edad . ' & ' . ($edad + rand(-2, 2)) : '');
                } elseif ($edad) {
                    $nombreMostrar = $nombre . ', ' . $edad;
                }

                $resultados[] = [
                    'nombre' => $nombreMostrar,
                    'edad' => $perfil && $perfil->tipo === 'pareja' ? null : ($edad ? $edad . ' años' : null),
                    'imagen' => $fotoPrincipal,
                    'distancia' => 'A ' . rand(1, 5) . ' km de ti',
                ];
            }

            // ✅ ELIMINADO: Ya no se usa fallback para matches
            // if (empty($resultados)) {
            //     $resultados = $this->getMatchesFallback();
            // }

            // ✅ Devuelve array vacío si no hay matches
            return $resultados;

        } catch (\Exception $e) {
            Log::error('Error al obtener matches recientes', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            // ✅ Devuelve array vacío en caso de error
            return [];
        }
    }

    /**
     * Obtiene actividad en la zona
     */
    protected function getActividadZona($user, $perfil)
    {
        try {
            $perfilesDisponibles = Perfil::where('usuario_id', '!=', $user->id)
                ->whereHas('usuario', function($query) {
                    $query->where('estado', 'verificado');
                })
                ->count();

            $coincidenciasHoy = Coincidencia::where(function($query) use ($user) {
                    $query->where('usuario_a_id', $user->id)
                          ->orWhere('usuario_b_id', $user->id);
                })
                ->where('estado', 'coincidencia')
                ->whereDate('created_at', Carbon::today())
                ->count();

            $ciudad = $perfil->ubicacion_ciudad ?? $user->ciudad ?? 'Tu zona';

            return [
                [
                    'icon' => 'pi-users',
                    'titulo' => 'Perfiles disponibles ahora',
                    'valor' => number_format($perfilesDisponibles, 0, ',', '.'),
                    'extra' => '+ ' . rand(5, 20) . ' nuevos'
                ],
                [
                    'icon' => 'pi-heart',
                    'titulo' => 'Coincidencias hoy',
                    'valor' => number_format($coincidenciasHoy, 0, ',', '.'),
                    'extra' => '+ ' . rand(1, 10) . ' nuevas'
                ],
                [
                    'icon' => 'pi-map-marker',
                    'titulo' => 'Zona aproximada',
                    'valor' => $ciudad,
                    'extra' => 'Radio: 25 km'
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error al obtener actividad en zona', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return [
                [
                    'icon' => 'pi-users',
                    'titulo' => 'Perfiles disponibles ahora',
                    'valor' => '0',
                    'extra' => '+ 0 nuevos'
                ],
                [
                    'icon' => 'pi-heart',
                    'titulo' => 'Coincidencias hoy',
                    'valor' => '0',
                    'extra' => '+ 0 nuevas'
                ],
                [
                    'icon' => 'pi-map-marker',
                    'titulo' => 'Zona aproximada',
                    'valor' => $perfil->ubicacion_ciudad ?? $user->ciudad ?? 'Tu zona',
                    'extra' => 'Radio: 25 km'
                ],
            ];
        }
    }

    /**
     * Calcula distancia entre usuarios (mock)
     */
    protected function calcularDistancia($user, $perfil)
    {
        $ciudadUser = $user->ciudad;
        $ciudadPerfil = $perfil->ubicacion_ciudad ?? '';
        
        if ($ciudadUser && $ciudadPerfil && $ciudadUser === $ciudadPerfil) {
            return 'A ' . rand(1, 8) . ' km de ti';
        }
        
        return 'A ' . rand(5, 50) . ' km de ti';
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

        return array_slice($resultados, 0, 3);
    }

    /**
     * Formatea intereses extra
     */
    protected function formatearInteresesExtra($intereses)
    {
        if (empty($intereses)) return [];
        
        $iconos = [
            'conversaciones profundas' => 'pi-comments',
            'charlas' => 'pi-comments',
            'dialogo' => 'pi-comments',
            'fiestas privadas' => 'pi-bolt',
            'fiestas' => 'pi-bolt',
            'eventos privados' => 'pi-bolt',
            'eventos vip' => 'pi-star-fill',
            'vip' => 'pi-star-fill',
            'exclusivo' => 'pi-star-fill',
            'experiencias' => 'pi-sparkles',
            'nuevas experiencias' => 'pi-sparkles',
            'aventura' => 'pi-compass',
            'aventuras' => 'pi-compass',
            'romance' => 'pi-heart-fill',
            'amor' => 'pi-heart-fill',
            'conexión' => 'pi-link',
            'conexiones' => 'pi-link',
        ];

        $resultados = [];
        $contador = 0;
        foreach ($intereses as $interes) {
            if ($contador >= 2) break;
            
            $label = is_array($interes) ? ($interes['label'] ?? $interes['nombre'] ?? $interes['valor'] ?? 'Interés') : (string) $interes;
            $icono = $iconos[strtolower($label)] ?? 'pi-tag';
            
            if (!in_array($label, array_column($resultados, 'label'))) {
                $resultados[] = [
                    'icon' => $icono,
                    'label' => $label
                ];
                $contador++;
            }
        }

        return $resultados;
    }

    /**
     * Acción para pasar al siguiente perfil
     */
    public function pasar(Request $request)
    {
        Log::info('Usuario pasó un perfil', [
            'user_id' => Auth::id(),
            'perfil_id' => $request->perfil_id ?? null
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Acción para conectar con un perfil
     */
    public function conectar(Request $request)
    {
        Log::info('Usuario conectó con un perfil', [
            'user_id' => Auth::id(),
            'perfil_id' => $request->perfil_id ?? null
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Acción para destacar un perfil
     */
    public function destacar(Request $request)
    {
        Log::info('Usuario destacó un perfil', [
            'user_id' => Auth::id(),
            'perfil_id' => $request->perfil_id ?? null
        ]);

        return response()->json(['success' => true]);
    }
}