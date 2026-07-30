<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Pedido;
use App\Models\Suscripcion;
use App\Models\Transaccion;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $hoy = now()->startOfDay();

        // Usuarios recientes (se reutiliza en dos secciones)
        $usuariosRecientes = User::latest()
            ->take(5)
            ->get([
                'id',
                'nombre',
                'apodo',
                'email',
                'rol',
                'estado',
                'created_at',
            ]);

        return Inertia::render('Admin/Dashboard', [

            'stats' => [
                'usuariosTotales' => User::count(),
                'usuariosNuevosHoy' => User::where('created_at', '>=', $hoy)->count(),

                'ingresosTotales' => (float) Transaccion::where('estado', 'aprobada')->sum('monto'),
                'ingresosHoy' => (float) Transaccion::where('estado', 'aprobada')
                    ->whereDate('created_at', today())
                    ->sum('monto'),

                'suscripcionesActivas' => Suscripcion::activas()->count(),
                'suscripcionesNuevasHoy' => Suscripcion::activas()
                    ->where('created_at', '>=', $hoy)
                    ->count(),

                'ventasShop' => (float) Pedido::pagados()->sum('total'),
                'ventasHoy' => (float) Pedido::pagados()
                    ->whereDate('created_at', today())
                    ->sum('total'),

                'eventosActivos' => Evento::where('estado', 'activo')->count(),
                'eventosProximos' => Evento::where('fecha', '>=', now())->count(),
            ],

            'usuariosRecientes' => $usuariosRecientes,

            'gestionUsuarios' => $usuariosRecientes,

            'cobrosRecientes' => Transaccion::with('usuario:id,nombre,apodo')
                ->latest()
                ->take(4)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'usuario' => $t->usuario?->apodo ?? $t->usuario?->nombre ?? 'Usuario',
                    'tipo' => $t->tipo_nombre,
                    'monto' => (float) $t->monto,
                    'fecha' => $t->created_at,
                ]),

            'eventosProximos' => Evento::where('fecha', '>=', now())
                ->orderBy('fecha')
                ->take(3)
                ->get([
                    'id',
                    'nombre',
                    'fecha',
                    'estado',
                ]),

            'accionesRapidas' => [
                [
                    'label' => 'Bloquear Usuario',
                    'desc' => 'Restringe el acceso de un usuario',
                    'icon' => 'pi-lock',
                    'route' => 'admin.usuarios.index',
                ],
                [
                    'label' => 'Ver Usuarios',
                    'desc' => 'Consulta todos los usuarios registrados',
                    'icon' => 'pi-users',
                    'route' => 'admin.usuarios.index',
                ],
                [
                    'label' => 'Ver Cobros',
                    'desc' => 'Revisa pagos y transacciones',
                    'icon' => 'pi-dollar',
                    'route' => 'admin.cobros.index',
                ],
                [
                    'label' => 'Crear Evento',
                    'desc' => 'Organiza un nuevo evento',
                    'icon' => 'pi-calendar-plus',
                    'route' => 'admin.eventos.index',
                ],
                [
                    'label' => 'Enviar Invitación',
                    'desc' => 'Invita usuarios a la plataforma',
                    'icon' => 'pi-envelope',
                    'route' => 'admin.invitaciones.index',
                ],
            ],

            'actividadReciente' => $this->actividadReciente(),
        ]);
    }

    /**
     * Actividad reciente del sistema.
     */
    private function actividadReciente(): array
    {
        $usuarios = User::latest()
            ->take(3)
            ->get()
            ->map(fn($u) => [
                'tipo' => 'usuario_nuevo',
                'icon' => 'pi-user-plus',
                'texto' => "Nuevo usuario registrado: @{$u->apodo}",
                'fecha' => $u->created_at,
            ]);

        $pagos = Transaccion::with('usuario:id,apodo,nombre')
            ->where('estado', 'aprobada')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($t) => [
                'tipo' => 'pago',
                'icon' => 'pi-dollar',
                'texto' => 'Pago de $' . number_format($t->monto, 2) .
                    ' por @' . ($t->usuario?->apodo ?? $t->usuario?->nombre ?? 'usuario'),
                'fecha' => $t->created_at,
            ]);

        $eventos = Evento::latest()
            ->take(2)
            ->get()
            ->map(fn($e) => [
                'tipo' => 'evento',
                'icon' => 'pi-calendar',
                'texto' => "Nuevo evento creado: {$e->nombre}",
                'fecha' => $e->created_at,
            ]);

        return $usuarios
            ->concat($pagos)
            ->concat($eventos)
            ->sortByDesc('fecha')
            ->take(6)
            ->values()
            ->all();
    }
}