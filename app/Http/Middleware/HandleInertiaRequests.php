<?php

namespace App\Http\Middleware;

use App\Models\CodigoInvitacion;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Log;

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
        // Obtener datos de la sesión
        $flash = session('flash', []);
        $toast = session('toast');
        $errors = session('errors');
        
        // LOG PARA DEBUG
        Log::info('📦 HandleInertiaRequests - Share:', [
            'flash' => $flash,
            'toast' => $toast,
            'has_errors' => !is_null($errors),
            'session_id' => session()->getId()
        ]);
        
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

}