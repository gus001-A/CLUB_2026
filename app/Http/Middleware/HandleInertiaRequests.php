<?php

namespace App\Http\Middleware;

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
            
            // Compartir flash messages
            'flash' => $flash,
            
            // Compartir toast directamente (para compatibilidad)
            'toast' => $toast,
            
            // Compartir errores de validación
            'errors' => $errors ? $errors->getBag('default')->getMessages() : (object) [],
            
            // Compartir usuario autenticado
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }
}