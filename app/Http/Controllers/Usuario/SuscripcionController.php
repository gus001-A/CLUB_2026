<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Creador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuscripcionController extends Controller
{
    /**
     * GET /suscripciones
     * Muestra todas las suscripciones del usuario autenticado
     */
    public function index()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        // Obtener suscripciones activas e inactivas con todas las relaciones necesarias
        $suscripcionesActivas = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'activa')
            ->with(['creador' => function($query) {
                $query->with(['usuario' => function($q) {
                    // ✅ CORREGIDO: solo seleccionar columnas que existen
                    $q->select('id', 'nombre', 'apodo', 'email', 'foto_principal');
                }]);
            }])
            ->orderBy('fecha_renovacion', 'asc')
            ->get()
            ->map(function ($suscripcion) {
                return $this->formatearSuscripcion($suscripcion);
            });

        $suscripcionesInactivas = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', '!=', 'activa')
            ->with(['creador' => function($query) {
                $query->with(['usuario' => function($q) {
                    // ✅ CORREGIDO: solo seleccionar columnas que existen
                    $q->select('id', 'nombre', 'apodo', 'email', 'foto_principal');
                }]);
            }])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($suscripcion) {
                return $this->formatearSuscripcion($suscripcion);
            });

        // Estadísticas del usuario
        $estadisticas = [
            'total_suscripciones' => Suscripcion::where('usuario_id', $usuario->id)->count(),
            'activas' => $suscripcionesActivas->count(),
            'inactivas' => $suscripcionesInactivas->count(),
            'gasto_mensual' => $suscripcionesActivas->sum('precio'),
            'por_vencer' => Suscripcion::where('usuario_id', $usuario->id)
                ->where('estado', 'activa')
                ->where('fecha_renovacion', '<=', now()->addDays(7))
                ->count(),
        ];

        // Recomendaciones con fotos de perfil
        $idsSuscriptos = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'activa')
            ->pluck('creador_id')
            ->toArray();

        $recomendaciones = Creador::whereNotIn('id', $idsSuscriptos)
            ->with(['usuario' => function($q) {
                // ✅ CORREGIDO: solo seleccionar columnas que existen
                $q->select('id', 'nombre', 'apodo', 'email', 'foto_principal');
            }])
            ->where('estado_verificacion', 'aprobado')
            ->limit(6)
            ->get()
            ->map(function ($creador) {
                return [
                    'id' => $creador->id,
                    'usuario' => [
                        'id' => $creador->usuario->id ?? null,
                        'nombre' => $creador->usuario->nombre ?? 'Creador',
                        'apodo' => $creador->usuario->apodo ?? null,
                        'foto_principal' => $creador->usuario->foto_principal ?? null,
                    ],
                    'esta_verificado' => $creador->esta_verificado,
                    'categoria' => $creador->categorias[0] ?? 'Creador de contenido',
                    'total_suscriptores' => $creador->total_suscriptores,
                ];
            });

        return inertia('Usuario/Suscripciones/Index', [
            'suscripcionesActivas' => $suscripcionesActivas,
            'suscripcionesInactivas' => $suscripcionesInactivas,
            'estadisticas' => $estadisticas,
            'recomendaciones' => $recomendaciones,
            'usuarioActual' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'foto_principal' => $usuario->foto_principal,
            ],
        ]);
    }

    /**
     * Formatea una suscripción para la vista
     */
    private function formatearSuscripcion($suscripcion)
    {
        $creador = $suscripcion->creador;
        $usuario = $creador->usuario ?? null;

        return [
            'id' => $suscripcion->id,
            'creador_id' => $suscripcion->creador_id,
            'plan' => $suscripcion->plan,
            'plan_nombre' => $suscripcion->plan_nombre,
            'precio' => $suscripcion->precio,
            'estado' => $suscripcion->estado,
            'fecha_inicio' => $suscripcion->fecha_inicio,
            'fecha_renovacion' => $suscripcion->fecha_renovacion,
            'dias_restantes' => $suscripcion->dias_restantes,
            'vence_pronto' => $suscripcion->vence_pronto,
            'creador' => [
                'id' => $creador->id ?? null,
                'esta_verificado' => $creador->esta_verificado ?? false,
                'usuario' => [
                    'id' => $usuario->id ?? null,
                    'nombre' => $usuario->nombre ?? 'Creador',
                    'apodo' => $usuario->apodo ?? null,
                    'foto_principal' => $usuario->foto_principal ?? null,
                ],
                'categorias' => $creador->categorias ?? [],
                'total_suscriptores' => $creador->total_suscriptores ?? 0,
            ],
            'created_at' => $suscripcion->created_at,
            'updated_at' => $suscripcion->updated_at,
        ];
    }

    /**
     * Obtiene la URL del avatar de un usuario
     * ✅ CORREGIDO: usa solo foto_principal
     */
    private function getAvatarUrl($usuario)
    {
        if (!$usuario) {
            return '/images/shared/avatar-default.jpg';
        }

        // Usar foto_principal si existe
        if (!empty($usuario->foto_principal)) {
            // Si es una URL completa
            if (filter_var($usuario->foto_principal, FILTER_VALIDATE_URL)) {
                return $usuario->foto_principal;
            }
            // Si es una ruta de storage
            if (str_starts_with($usuario->foto_principal, '/')) {
                return $usuario->foto_principal;
            }
            return '/storage/' . $usuario->foto_principal;
        }

        return '/images/shared/avatar-default.jpg';
    }

    /**
     * GET /suscripciones/{id}
     * Muestra los detalles de una suscripción específica
     */
    public function show($id)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        $suscripcion = Suscripcion::where('id', $id)
            ->where('usuario_id', $usuario->id)
            ->with(['creador.usuario'])
            ->first();

        if (!$suscripcion) {
            return response()->json(['mensaje' => 'Suscripción no encontrada'], 404);
        }

        return response()->json([
            'ok' => true,
            'suscripcion' => $this->formatearSuscripcion($suscripcion),
        ]);
    }

    /**
     * POST /suscripciones/{id}/cancelar
     * Cancela una suscripción activa
     */
    public function cancelar($id)
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json(['mensaje' => 'No autenticado'], 401);
            }

            $suscripcion = Suscripcion::where('id', $id)
                ->where('usuario_id', $usuario->id)
                ->where('estado', 'activa')
                ->first();

            if (!$suscripcion) {
                return response()->json(['mensaje' => 'Suscripción no encontrada o ya cancelada'], 404);
            }

            $suscripcion->estado = 'cancelada';
            $suscripcion->save();

            Log::info('Suscripción cancelada', [
                'usuario_id' => $usuario->id,
                'suscripcion_id' => $suscripcion->id,
                'creador_id' => $suscripcion->creador_id
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Suscripción cancelada exitosamente. Disfrutarás del contenido hasta el final del período.',
                'suscripcion' => $this->formatearSuscripcion($suscripcion),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cancelar suscripción: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ocurrió un error al cancelar la suscripción.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /suscripciones/{id}/reactivar
     * Reactiva una suscripción cancelada
     */
    public function reactivar($id)
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json(['mensaje' => 'No autenticado'], 401);
            }

            $suscripcion = Suscripcion::where('id', $id)
                ->where('usuario_id', $usuario->id)
                ->where('estado', 'cancelada')
                ->first();

            if (!$suscripcion) {
                return response()->json(['mensaje' => 'Suscripción no encontrada o no está cancelada'], 404);
            }

            if ($suscripcion->fecha_renovacion < now()) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Esta suscripción ya expiró. Debes crear una nueva suscripción.',
                ], 400);
            }

            $suscripcion->estado = 'activa';
            $suscripcion->save();

            Log::info('Suscripción reactivada', [
                'usuario_id' => $usuario->id,
                'suscripcion_id' => $suscripcion->id,
                'creador_id' => $suscripcion->creador_id
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Suscripción reactivada exitosamente.',
                'suscripcion' => $this->formatearSuscripcion($suscripcion),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al reactivar suscripción: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ocurrió un error al reactivar la suscripción.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /suscripciones/{id}/renovar
     * Renueva una suscripción expirada
     */
    public function renovar($id)
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json(['mensaje' => 'No autenticado'], 401);
            }

            $suscripcion = Suscripcion::where('id', $id)
                ->where('usuario_id', $usuario->id)
                ->where('estado', 'expirada')
                ->first();

            if (!$suscripcion) {
                return response()->json(['mensaje' => 'Suscripción no encontrada o no está expirada'], 404);
            }

            $nuevaFechaRenovacion = now()->addDays(30);

            $suscripcion->estado = 'activa';
            $suscripcion->fecha_inicio = now();
            $suscripcion->fecha_renovacion = $nuevaFechaRenovacion;
            $suscripcion->save();

            Log::info('Suscripción renovada', [
                'usuario_id' => $usuario->id,
                'suscripcion_id' => $suscripcion->id,
                'creador_id' => $suscripcion->creador_id,
                'nueva_fecha_renovacion' => $nuevaFechaRenovacion
            ]);

            return response()->json([
                'ok' => true,
                'mensaje' => 'Suscripción renovada exitosamente por 30 días.',
                'suscripcion' => $this->formatearSuscripcion($suscripcion),
            ]);

        } catch (\Exception $e) {
            Log::error('Error al renovar suscripción: ' . $e->getMessage());
            return response()->json([
                'ok' => false,
                'mensaje' => 'Ocurrió un error al renovar la suscripción.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /suscripciones/creador/{creadorId}
     * Verifica si el usuario está suscrito a un creador específico
     */
    public function verificarSuscripcion($creadorId)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        $suscripcion = Suscripcion::where('usuario_id', $usuario->id)
            ->where('creador_id', $creadorId)
            ->where('estado', 'activa')
            ->first();

        return response()->json([
            'ok' => true,
            'suscrito' => $suscripcion !== null,
            'suscripcion' => $suscripcion ? $this->formatearSuscripcion($suscripcion) : null,
        ]);
    }

    /**
     * GET /suscripciones/estadisticas
     * Obtiene estadísticas de suscripciones del usuario
     */
    public function estadisticas()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        $activas = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'activa')
            ->count();

        $porVencer = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'activa')
            ->where('fecha_renovacion', '<=', now()->addDays(7))
            ->count();

        $expiradas = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'expirada')
            ->count();

        $canceladas = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'cancelada')
            ->count();

        $gastoMensual = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'activa')
            ->sum('precio');

        $planes = Suscripcion::where('usuario_id', $usuario->id)
            ->where('estado', 'activa')
            ->selectRaw('plan, count(*) as total')
            ->groupBy('plan')
            ->get()
            ->pluck('total', 'plan')
            ->toArray();

        return response()->json([
            'ok' => true,
            'estadisticas' => [
                'activas' => $activas,
                'por_vencer' => $porVencer,
                'expiradas' => $expiradas,
                'canceladas' => $canceladas,
                'gasto_mensual' => $gastoMensual,
                'total' => Suscripcion::where('usuario_id', $usuario->id)->count(),
                'planes' => $planes,
            ]
        ]);
    }

    /**
     * GET /suscripciones/historial
     * Obtiene el historial completo de suscripciones del usuario
     */
    public function historial()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        $historial = Suscripcion::where('usuario_id', $usuario->id)
            ->with(['creador.usuario'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($suscripcion) {
                return $this->formatearSuscripcion($suscripcion);
            });

        return response()->json([
            'ok' => true,
            'historial' => $historial,
        ]);
    }

    /**
     * GET /suscripciones/creador/{creadorId}/detalles
     * Obtiene detalles de la suscripción a un creador específico
     */
    public function detallesPorCreador($creadorId)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        $suscripcion = Suscripcion::where('usuario_id', $usuario->id)
            ->where('creador_id', $creadorId)
            ->with(['creador.usuario'])
            ->first();

        if (!$suscripcion) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No estás suscrito a este creador.',
                'suscrito' => false
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'suscrito' => $suscripcion->estado === 'activa',
            'suscripcion' => $this->formatearSuscripcion($suscripcion),
        ]);
    }
}