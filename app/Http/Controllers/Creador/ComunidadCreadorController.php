<?php

namespace App\Http\Controllers\Creador;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Creador;
use App\Models\Contenido;
use App\Models\Suscripcion;
use App\Models\Transaccion;
use App\Models\Evento;
use App\Models\Reserva;
use App\Models\Interaccion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ComunidadCreadorController extends Controller
{
    /**
     * Muestra la comunidad del creador - ACCESIBLE PARA TODOS
     */
    public function index()
    {
        Log::info('=== COMUNIDAD CREADOR ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre, 'rol' => $user->rol]);

        $esCreador = ($user->rol === 'creador' && $user->creador);
        Log::info('¿Es creador?', ['es_creador' => $esCreador]);

        // ✅ PRIMERO OBTENER SUSCRIPCIONES DEL USUARIO
        $suscripcionesUsuario = $this->getSuscripcionesUsuario($user);

        // ✅ LUEGO OBTENER CONTENIDO DEL CREADOR (pasando las suscripciones)
        $contenidosCreadores = $this->getContenidosCreadores($user, $suscripcionesUsuario);

        // Si es creador, obtener sus datos personales
        $creadorData = null;
        $configuracion = null;
        $contenidosRecientes = [];
        $estadisticas = [];
        $proximosEventos = [];

        // Obtener eventos próximos (para todos)
        $proximosEventos = $this->getProximosEventos();

        if ($esCreador) {
            $creador = $user->creador;
            $configuracion = $creador->configuracionMonetizacion;
            
            $totalContenidos = $creador->contenidos()->where('estado', 'publicado')->count();
            
            $contenidosRecientes = $creador->contenidos()
                ->where('estado', 'publicado')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($contenido) use ($user, $creador) {
                    return [
                        'id' => $contenido->id,
                        'titulo' => $contenido->titulo,
                        'tipo' => $contenido->tipo,
                        'descripcion' => $contenido->descripcion,
                        'precio' => $contenido->precio,
                        'es_premium' => $contenido->es_premium,
                        'visibilidad' => $contenido->visibilidad,
                        'archivos' => $contenido->archivos,
                        'created_at' => $contenido->created_at,
                        'total_likes' => $contenido->total_likes,
                        'total_comentarios' => $contenido->total_comentarios,
                        'total_vistas' => $contenido->total_vistas,
                        'autor' => $user->nombre,
                        'avatar' => $this->getAvatarUrl($user),
                        'verificado' => $user->estado === 'verificado',
                        'creador_id' => $creador->id,
                        'yo_le_di_like' => $contenido->tieneLikeDe($user->id),
                    ];
                });

            $estadisticas = [
                'total_publicaciones' => $totalContenidos,
                'total_suscriptores' => Suscripcion::where('creador_id', $creador->id)
                    ->where('estado', 'activa')
                    ->count(),
                'total_ganancias' => Transaccion::where('creador_id', $creador->id)
                    ->where('estado', 'aprobada')
                    ->sum('monto') ?? 0,
                'visitas' => $creador->estadisticas['visitas'] ?? 0,
                'interacciones' => $creador->estadisticas['interacciones'] ?? 0,
            ];

            $creadorData = [
                'biografia' => $creador->biografia,
                'categorias' => $creador->categorias,
                'es_premium' => $creador->es_premium,
                'esta_verificado' => $creador->estado_verificacion === 'aprobado',
                'estado_verificacion' => $creador->estado_verificacion,
            ];
        }

        $data = [
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'avatar' => $this->getAvatarUrl($user),
                'verificado' => $user->estado === 'verificado',
                'rol' => $user->rol,
            ],
            'esCreador' => $esCreador,
            'publicacionesCreadores' => $contenidosCreadores,
            'suscripcionesUsuario' => $suscripcionesUsuario,
            'creador' => $creadorData,
            'estadisticas' => $estadisticas,
            'contenidos_recientes' => $contenidosRecientes,
            'proximosEventos' => $proximosEventos,
            'configuracion_monetizacion' => $configuracion ? [
                'modelo_ingresos' => $configuracion->modelo_ingresos,
                'precio_personalizado' => $configuracion->precio_personalizado,
                'tiene_tarjeta' => !is_null($configuracion->tarjeta_ultimos4),
                'tarjeta_display' => $configuracion->tarjeta_ultimos4 ? '**** ' . $configuracion->tarjeta_ultimos4 : null,
                'frecuencia_pago' => $configuracion->frecuencia_pago,
                'comision_plataforma' => $configuracion->comision_plataforma,
                'comision_creador' => 100 - ($configuracion->comision_plataforma ?? 20),
                'mostrar_vista_previa' => (bool)$configuracion->mostrar_vista_previa,
            ] : null,
            'footerColumnas' => $this->getFooterColumnas(),
        ];

        Log::info('Datos de la comunidad preparados', [
            'es_creador' => $esCreador,
            'contenidos_count' => count($contenidosCreadores),
            'suscripciones_count' => count($suscripcionesUsuario)
        ]);

        // ✅ LOG PARA DEPURAR - VERIFICAR QUE LLEGAN LOS CAMPOS DE SUSCRIPCIÓN
        if (count($contenidosCreadores) > 0) {
            Log::info('Primer contenido:', [
                'titulo' => $contenidosCreadores[0]['titulo'] ?? 'sin titulo',
                'es_premium' => $contenidosCreadores[0]['es_premium'] ?? false,
                'usuario_esta_suscrito' => $contenidosCreadores[0]['usuario_esta_suscrito'] ?? false,
                'usuario_es_creador' => $contenidosCreadores[0]['usuario_es_creador'] ?? false,
                'usuario_puede_acceder' => $contenidosCreadores[0]['usuario_puede_acceder'] ?? false,
            ]);
        }

        return Inertia::render('Creador/ComunidadCreador', $data);
    }

    /**
     * Obtiene las suscripciones activas del usuario
     */
    private function getSuscripcionesUsuario($user)
    {
        if (!$user) {
            return [];
        }

        $suscripciones = Suscripcion::where('usuario_id', $user->id)
            ->where('estado', 'activa')
            ->get()
            ->map(function ($suscripcion) {
                return [
                    'id' => $suscripcion->id,
                    'creador_id' => $suscripcion->creador_id,
                    'plan' => $suscripcion->plan,
                    'estado' => $suscripcion->estado,
                ];
            })
            ->toArray();

        Log::info('Suscripciones del usuario:', [
            'user_id' => $user->id,
            'count' => count($suscripciones),
            'detalles' => $suscripciones
        ]);

        return $suscripciones;
    }

    /**
     * Obtiene contenido de todos los creadores verificados (tabla contenidos)
     * ✅ RECIBE LAS SUSCRIPCIONES COMO PARÁMETRO
     */
    private function getContenidosCreadores($user, $suscripcionesUsuario = [])
    {
        // Obtener IDs de creadores verificados
        $creadorIds = User::where('rol', 'creador')
            ->whereHas('creador', function($query) {
                $query->where('estado_verificacion', 'aprobado');
            })
            ->pluck('id')
            ->toArray();

        Log::info('Creadores verificados encontrados:', ['count' => count($creadorIds)]);

        if (empty($creadorIds)) {
            return $this->getContenidosEjemplo();
        }

        // Obtener IDs de los creadores a los que el usuario está suscrito
        $creadorIdsSuscritos = array_column($suscripcionesUsuario, 'creador_id');
        Log::info('Creadores a los que está suscrito:', ['ids' => $creadorIdsSuscritos]);

        // Obtener contenido de la tabla contenidos
        $contenidos = Contenido::with(['creador.usuario'])
            ->whereIn('creador_id', function($query) use ($creadorIds) {
                $query->select('id')
                    ->from('creadores')
                    ->whereIn('usuario_id', $creadorIds)
                    ->where('estado_verificacion', 'aprobado');
            })
            ->where('estado', 'publicado')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $result = [];
        $usuarioActualId = $user?->id;

        foreach ($contenidos as $contenido) {
            $creador = $contenido->creador;
            $usuario = $creador ? $creador->usuario : null;
            
            // ✅ VERIFICAR SUSCRIPCIÓN USANDO EL ARRAY PASADO
            $estaSuscrito = in_array($contenido->creador_id, $creadorIdsSuscritos);
            $esElCreador = $usuarioActualId && $usuario && $usuario->id === $usuarioActualId;
            
            // ✅ LOG PARA DEPURAR CADA CONTENIDO
            Log::info('Procesando contenido:', [
                'id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'creador_id' => $contenido->creador_id,
                'es_premium' => $contenido->es_premium,
                'esta_suscrito' => $estaSuscrito,
                'es_el_creador' => $esElCreador,
                'puede_acceder' => $estaSuscrito || $esElCreador || !$contenido->es_premium,
            ]);

            $result[] = [
                'id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'descripcion' => $contenido->descripcion,
                'tipo' => $contenido->tipo,
                'autor' => $usuario ? $usuario->nombre : 'Creador',
                'avatar' => $usuario ? $this->getAvatarUrl($usuario) : '/images/shared/avatar-default.jpg',
                'verificado' => $usuario ? $usuario->estado === 'verificado' : false,
                'precio' => $contenido->precio,
                'es_premium' => (bool) $contenido->es_premium,
                'visibilidad' => $contenido->visibilidad,
                'archivos' => $contenido->archivos,
                'total_likes' => $contenido->total_likes,
                'total_comentarios' => $contenido->total_comentarios,
                'total_vistas' => $contenido->total_vistas,
                'created_at' => $contenido->created_at,
                'tiempo' => $contenido->created_at->diffForHumans(),
                'es_creador' => true,
                'creador_id' => $contenido->creador_id,
                'yo_le_di_like' => $usuarioActualId ? $contenido->tieneLikeDe($usuarioActualId) : false,
                // ✅ CAMPOS PARA CONTROL DE ACCESO
                'usuario_esta_suscrito' => $estaSuscrito,
                'usuario_es_creador' => $esElCreador,
                'usuario_puede_acceder' => $estaSuscrito || $esElCreador || !$contenido->es_premium,
            ];
        }

        return $result;
    }

    /**
     * Devuelve contenido de ejemplo cuando no hay creadores
     */
    private function getContenidosEjemplo()
    {
        return [
            [
                'id' => 1,
                'titulo' => 'Bienvenida a la comunidad de creadores',
                'descripcion' => 'Bienvenidos a la comunidad de creadores. Aquí encontrarás contenido exclusivo y experiencias únicas. ¡Síguenos para no perderte nada!',
                'tipo' => 'texto',
                'autor' => 'CreadorEjemplo',
                'avatar' => '/images/shared/avatar-default.jpg',
                'verificado' => true,
                'precio' => 0,
                'es_premium' => false,
                'visibilidad' => 'publico',
                'archivos' => [],
                'total_likes' => 15,
                'total_comentarios' => 3,
                'total_vistas' => 120,
                'created_at' => now()->subHours(2),
                'tiempo' => 'Hace 2 horas',
                'es_creador' => true,
                'creador_id' => 1,
                'yo_le_di_like' => false,
                'usuario_esta_suscrito' => false,
                'usuario_es_creador' => false,
                'usuario_puede_acceder' => false,
            ],
            [
                'id' => 2,
                'titulo' => 'Contenido exclusivo para suscriptores',
                'descripcion' => 'Este es un espacio exclusivo para creadores donde pueden compartir su contenido y conectar con su audiencia.',
                'tipo' => 'exclusivo',
                'autor' => 'ContenidoExclusivo',
                'avatar' => '/images/shared/avatar-default.jpg',
                'verificado' => true,
                'precio' => 9.99,
                'es_premium' => true,
                'visibilidad' => 'suscriptores',
                'archivos' => [],
                'total_likes' => 8,
                'total_comentarios' => 1,
                'total_vistas' => 45,
                'created_at' => now()->subHours(5),
                'tiempo' => 'Hace 5 horas',
                'es_creador' => true,
                'creador_id' => 2,
                'yo_le_di_like' => false,
                'usuario_esta_suscrito' => false,
                'usuario_es_creador' => false,
                'usuario_puede_acceder' => false,
            ],
        ];
    }

    /**
     * Obtiene próximos eventos con formato en español
     */
    private function getProximosEventos()
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
        ];
    }

    /**
     * Obtiene la URL del avatar de un usuario
     */
    private function getAvatarUrl($user)
    {
        if (!$user) {
            return '/images/shared/avatar-default.jpg';
        }

        if ($user->foto_principal) {
            if (filter_var($user->foto_principal, FILTER_VALIDATE_URL)) {
                return $user->foto_principal;
            }
            return Storage::url($user->foto_principal);
        }

        if ($user->perfil) {
            $fotoPrincipal = $user->perfil->fotos()->where('es_principal', true)->first();
            if ($fotoPrincipal) {
                return $fotoPrincipal->url;
            }
            
            $primeraFoto = $user->perfil->fotos()->first();
            if ($primeraFoto) {
                return $primeraFoto->url;
            }
        }

        return '/images/shared/avatar-default.jpg';
    }

    /**
     * Obtiene las columnas del footer
     */
    private function getFooterColumnas()
    {
        return [
            'navegacion' => ['Inicio', 'Descubrir', 'Eventos'],
            'comunidad' => ['Mensajes', 'Creadores'],
            'soporte' => ['Sobre nosotros', 'Términos y condiciones', 'Política de privacidad'],
            'legal' => ['Centro de ayuda', 'Contacto', 'Reportar un problema'],
        ];
    }
}