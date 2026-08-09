<?php

namespace App\Http\Middleware;

use App\Models\Transaccion;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Log;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $flash = session('flash', []);
        $toast = session('toast');
        $errors = session('errors');
        
        // 🔥 OBTENER ADMINISTRADOR
        $admin = $request->user('admin');
        $adminData = null;
        
        if ($admin) {
            $adminData = [
                'id' => $admin->id,
                'nombre' => $admin->nombre,
                'nickname' => $admin->nickname ?? null,
                'email' => $admin->email,
                'rol' => $admin->rol ?? 'admin',
            ];
        }
        
        // 🔥 OBTENER USUARIO NORMAL
        $user = $request->user();
        $userData = null;
        
        if ($user && !$admin) {
            try {
                // Cargar perfil
                $user->load('perfil');
                
                // 🔥 OBTENER AVATAR
                $avatar = '/images/shared/avatar-default.jpg';
                $foto_original = null;
                
                Log::info('🔍 DEBUG - Datos del usuario:', [
                    'user_id' => $user->id,
                    'nombre' => $user->nombre,
                    'foto_principal' => $user->foto_principal,
                    'tiene_perfil' => !is_null($user->perfil),
                ]);
                
                // 1. Verificar foto_principal en el usuario
                if ($user->foto_principal && !empty($user->foto_principal)) {
                    $foto_original = $user->foto_principal;
                    
                    Log::info('📸 foto_principal encontrada:', ['foto' => $foto_original]);
                    
                    // 🔥 CONSTRUIR LA URL CORRECTA
                    if (str_starts_with($foto_original, 'http://') || str_starts_with($foto_original, 'https://')) {
                        $avatar = $foto_original;
                    } elseif (str_starts_with($foto_original, '/')) {
                        $avatar = $foto_original;
                    } else {
                        // La ruta es "perfil/fotos/..." -> /storage/perfil/fotos/...
                        $avatar = '/storage/' . $foto_original;
                    }
                    
                    Log::info('✅ Avatar generado desde foto_principal:', ['avatar' => $avatar]);
                }
                // 2. Si no tiene foto_principal, buscar en perfil
                else if ($user->perfil && $user->perfil->fotos) {
                    $fotos = is_string($user->perfil->fotos) 
                        ? json_decode($user->perfil->fotos, true) 
                        : $user->perfil->fotos;
                    
                    if (is_array($fotos) && count($fotos) > 0) {
                        foreach ($fotos as $foto) {
                            if (isset($foto['principal']) && $foto['principal'] === true) {
                                if (isset($foto['url']) && !empty($foto['url'])) {
                                    $avatar = $foto['url'];
                                    break;
                                } elseif (isset($foto['ruta_foto']) && !empty($foto['ruta_foto'])) {
                                    $avatar = '/storage/' . $foto['ruta_foto'];
                                    break;
                                }
                            }
                        }
                        
                        if ($avatar === '/images/shared/avatar-default.jpg' && isset($fotos[0])) {
                            if (isset($fotos[0]['url']) && !empty($fotos[0]['url'])) {
                                $avatar = $fotos[0]['url'];
                            } elseif (isset($fotos[0]['ruta_foto']) && !empty($fotos[0]['ruta_foto'])) {
                                $avatar = '/storage/' . $fotos[0]['ruta_foto'];
                            }
                        }
                    }
                }
                
                Log::info('📸 AVATAR FINAL:', [
                    'user_id' => $user->id,
                    'nombre' => $user->nombre,
                    'foto_original' => $foto_original,
                    'avatar_final' => $avatar,
                ]);

                // 🔥 DATOS DEL USUARIO
                $userData = [
                    'id' => $user->id,
                    'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                    'apodo' => $user->apodo ?? $user->nombre ?? 'Usuario',
                    'email' => $user->email ?? '',
                    'rol' => $user->rol ?? 'usuario',
                    'estado' => $user->estado ?? 'pendiente',
                    'foto_principal' => $user->foto_principal,
                    'avatar' => $avatar, // 🔥 EL AVATAR CON LA RUTA COMPLETA
                    'verificado' => ($user->estado === 'verificado' || $user->email_verificado_en !== null),
                    'email_verificado_en' => $user->email_verificado_en,
                    'created_at' => $user->created_at,
                    'perfil' => $user->perfil ? [
                        'id' => $user->perfil->id,
                        'tipo' => $user->perfil->tipo ?? 'personal',
                        'ubicacion_ciudad' => $user->perfil->ubicacion_ciudad ?? '',
                        'esta_verificado' => $user->perfil->esta_verificado ?? false,
                    ] : null,
                    'tiene_perfil' => !is_null($user->perfil),
                ];
                
            } catch (\Exception $e) {
                Log::error('Error en HandleInertiaRequests:', [
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                ]);
                
                $userData = [
                    'id' => $user->id,
                    'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                    'apodo' => $user->apodo ?? $user->nombre ?? 'Usuario',
                    'email' => $user->email ?? '',
                    'rol' => $user->rol ?? 'usuario',
                    'estado' => $user->estado ?? 'pendiente',
                    'foto_principal' => null,
                    'avatar' => '/images/shared/avatar-default.jpg',
                    'verificado' => false,
                    'email_verificado_en' => null,
                    'created_at' => $user->created_at,
                    'perfil' => null,
                    'tiene_perfil' => false,
                ];
            }
        }
        
        Log::info('📦 Share final:', [
            'user_type' => $user && !$admin ? 'web' : ($admin ? 'admin' : 'guest'),
            'user_id' => $userData ? $userData['id'] : null,
            'avatar' => $userData ? $userData['avatar'] : 'null',
        ]);
        
        return array_merge(parent::share($request), [
            'flash' => $flash,
            'toast' => $toast,
            'errors' => $errors ? $errors->getBag('default')->getMessages() : (object) [],
            
            // 🔥 DATOS DEL USUARIO EN LA RAIZ
            'usuario' => $userData,
            
            // 🔥 DATOS DEL ADMINISTRADOR
            'admin' => $adminData,
            
            'badges' => $this->getBadges($request),
            'notificaciones' => $this->notificaciones($request),
        ]);
    }

    private function getBadges(Request $request): array
    {
        if (!$request->user('admin')) {
            return ['invitacionesPendientes' => 0, 'pagosPendientes' => 0, 'notificaciones' => 0];
        }

        $pagosPendientes = Transaccion::where('estado', 'pendiente')->count();

        return [
            'invitacionesPendientes' => 0,
            'pagosPendientes' => $pagosPendientes,
            'notificaciones' => $pagosPendientes,
        ];
    }

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