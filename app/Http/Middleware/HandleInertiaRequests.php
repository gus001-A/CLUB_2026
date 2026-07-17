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
        ];
    }

    /**
     * Contadores reales para los badges del sidebar y la campana de notificaciones.
     * Solo se calculan si hay un admin autenticado, para no pegarle a la BD en /admin/login.
     */
    private function badges(Request $request): array
    {
        if (! $request->user('admin')) {
            return ['invitacionesPendientes' => 0, 'notificaciones' => 0];
        }

        $ahora = now();

        $invitacionesPendientes = CodigoInvitacion::whereNull('usado_en')
            ->where('esta_activo', true)
            ->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', $ahora))
            ->whereColumn('contador_usos', '<', 'usos_maximos')
            ->count();

        $pagosPendientes = Transaccion::where('estado', 'pendiente')->count();

        return [
            'invitacionesPendientes' => $invitacionesPendientes,
            'notificaciones' => $invitacionesPendientes + $pagosPendientes,
        ];
    }
}