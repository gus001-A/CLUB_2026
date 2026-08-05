<?php

namespace App\Http\Middleware;

use App\Models\CodigoInvitacion;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'admin' => $request->user('admin'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'badges' => fn () => $this->badges($request),
            'notificaciones' => fn () => $this->notificaciones($request),
        ];
    }

    /**
     * Contadores reales para los badges del sidebar y la campana de notificaciones.
     * Solo se calculan si hay un admin autenticado, para no pegarle a la BD en /admin/login.
     */
    private function badges(Request $request): array
    {
        if (! $request->user('admin')) {
            return [
                'invitacionesPendientes' => 0,
                'pagosPendientes'        => 0,
                'notificaciones'         => 0,
            ];
        }

        $pagosPendientes = Transaccion::where('estado', 'pendiente')->count();

        // NOTIFICACIONES Y BADGES PARA EL ADMIN:
        // 1. "invitacionesPendientes" en 0 para que no te alerte por invitaciones que tú mismo enviaste.
        //    (Aquí podrás contar solo las solicitudes o respuestas cuando las configures en la BD).
        // 2. "notificaciones" de la campana solo incluye pagos/eventos pendientes recibidos.

        return [
            'invitacionesPendientes' => 0, 
            'pagosPendientes'        => $pagosPendientes,
            'notificaciones'         => $pagosPendientes,
        ];
    }

    /**
     * Lista real que alimenta el panel de la campana (no solo el número).
     * Por ahora son los pagos pendientes, porque es lo único que "badges"
     * está contando de verdad. Cuando exista una tabla de notificaciones,
     * esto se reemplaza por Notificacion::where('leida', false)->...
     */
    private function notificaciones(Request $request): array
    {
        if (! $request->user('admin')) {
            return [];
        }

        return Transaccion::with('usuario:id,nombre,apodo')
            ->where('estado', 'pendiente')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'titulo' => 'Pago pendiente de aprobación',
                'mensaje' => '@' . ($t->usuario?->apodo ?? $t->usuario?->nombre ?? 'usuario')
                    . ' — $' . number_format($t->monto, 2),
                'fecha' => $t->created_at->diffForHumans(),
                'route' => 'admin.cobros.index',
            ])
            ->all();
    }
}