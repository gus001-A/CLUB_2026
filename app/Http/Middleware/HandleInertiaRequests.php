<?php

namespace App\Http\Middleware;

use App\Models\CodigoInvitacion;
use App\Models\MensajeSoporte;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        
        // Obtener usuario autenticado con su perfil
        $user = $request->user();
        $userData = null;

        // OJO: cuando solo hay sesión de admin activa, $request->user() (guard
        // por defecto) puede regresar el Administrador en vez de null — todo
        // este bloque asume un User de citas (perfil, fotos, etc.), así que
        // hay que descartarlo explícitamente si en realidad es un admin.
        if ($user && ! $user instanceof \App\Models\Administrador) {
            // Cargar el perfil con sus relaciones
            $user->load(['perfil.fotoPrincipal']);
            
            // Determinar el avatar
            $avatar = '/images/shared/avatar-default.jpg';
            $found = false; // Bandera para saber si ya encontramos el avatar
            
            // 🔥 1. Si tiene foto_principal directamente en el usuario
            if (!$found && $user->foto_principal) {
                // Verificar si es una URL completa o una ruta
                if (str_starts_with($user->foto_principal, 'http') || str_starts_with($user->foto_principal, '/')) {
                    $avatar = $user->foto_principal;
                    $found = true;
                } else {
                    $avatar = asset('storage/' . $user->foto_principal);
                    $found = true;
                }
            } 
            
            // 🔥 2. Si el perfil tiene fotoPrincipal (relación con tabla Fotos)
            if (!$found && $user->perfil && $user->perfil->fotoPrincipal) {
                $foto = $user->perfil->fotoPrincipal;
                
                // Verificar si es un objeto o un string
                if (is_object($foto)) {
                    if (isset($foto->url) && !empty($foto->url)) {
                        $avatar = $foto->url;
                        $found = true;
                    } elseif (isset($foto->ruta_foto) && !empty($foto->ruta_foto)) {
                        $avatar = asset('storage/' . $foto->ruta_foto);
                        $found = true;
                    }
                } elseif (is_string($foto)) {
                    // Si es un string, es la URL o ruta directamente
                    if (str_starts_with($foto, 'http') || str_starts_with($foto, '/')) {
                        $avatar = $foto;
                    } else {
                        $avatar = asset('storage/' . $foto);
                    }
                    $found = true;
                }
            }
            
            // 🔥 3. Si el perfil tiene fotos en el campo JSON
            if (!$found && $user->perfil && $user->perfil->fotos) {
                $fotos = is_string($user->perfil->fotos) 
                    ? json_decode($user->perfil->fotos, true) 
                    : $user->perfil->fotos;
                
                if (is_array($fotos) && count($fotos) > 0) {
                    // Buscar foto principal
                    foreach ($fotos as $foto) {
                        if (isset($foto['principal']) && $foto['principal'] === true) {
                            if (isset($foto['url']) && !empty($foto['url'])) {
                                $avatar = $foto['url'];
                                $found = true;
                                break;
                            } elseif (isset($foto['ruta_foto']) && !empty($foto['ruta_foto'])) {
                                $avatar = asset('storage/' . $foto['ruta_foto']);
                                $found = true;
                                break;
                            }
                        }
                    }
                    
                    // Si no hay principal, usar la primera
                    if (!$found && isset($fotos[0])) {
                        if (isset($fotos[0]['url']) && !empty($fotos[0]['url'])) {
                            $avatar = $fotos[0]['url'];
                            $found = true;
                        } elseif (isset($fotos[0]['ruta_foto']) && !empty($fotos[0]['ruta_foto'])) {
                            $avatar = asset('storage/' . $fotos[0]['ruta_foto']);
                            $found = true;
                        }
                    }
                }
            }
            
            // 🔥 4. Verificar si el perfil tiene fotoPrincipalId (pero no está cargada la relación)
            if (!$found && $user->perfil && $user->perfil->fotoPrincipalId) {
                // Si la relación no está cargada como objeto, podríamos intentar obtenerla
                // Pero mejor dejamos que se maneje en el frontend
            }
            
            // 🔥 LOG para debug
            Log::info('👤 Usuario autenticado en HandleInertiaRequests:', [
                'user_id' => $user->id,
                'nombre' => $user->nombre,
                'avatar_final' => $avatar,
                'tiene_perfil' => !is_null($user->perfil),
                'tiene_foto_principal' => !is_null($user->foto_principal),
                'perfil_foto_principal' => $user->perfil ? $user->perfil->fotoPrincipal : null,
                'perfil_foto_principal_type' => $user->perfil ? gettype($user->perfil->fotoPrincipal) : 'null',
            ]);

            // 🔥 CONSTRUIR DATOS DEL PERFIL DE FORMA SEGURA
            $perfilData = null;
            if ($user->perfil) {
                $perfilData = [
                    'id' => $user->perfil->id,
                    'tipo' => $user->perfil->tipo ?? 'personal',
                    'fotos' => $user->perfil->fotos ?? [],
                    'biografia' => $user->perfil->biografia ?? '',
                    'descripcion' => $user->perfil->descripcion ?? '',
                    'intereses' => $user->perfil->intereses ?? [],
                    'pasatiempos' => $user->perfil->pasatiempos ?? [],
                    'privacidad_fotos' => $user->perfil->privacidad_fotos ?? 'todos',
                    'ubicacion_ciudad' => $user->perfil->ubicacion_ciudad ?? '',
                    'esta_verificado' => $user->perfil->esta_verificado ?? false,
                    'puntuacion_compatibilidad' => $user->perfil->puntuacion_compatibilidad ?? 0,
                    'metadatos' => $user->perfil->metadatos ?? [],
                    'fotoPrincipalId' => $user->perfil->fotoPrincipalId ?? null,
                ];
                
                // 🔥 Agregar fotoPrincipal SOLO si es un objeto
                if ($user->perfil->fotoPrincipal && is_object($user->perfil->fotoPrincipal)) {
                    $perfilData['fotoPrincipal'] = [
                        'id' => $user->perfil->fotoPrincipal->id ?? null,
                        'url' => $user->perfil->fotoPrincipal->url ?? null,
                        'ruta_foto' => $user->perfil->fotoPrincipal->ruta_foto ?? null,
                    ];
                } elseif ($user->perfil->fotoPrincipal && is_string($user->perfil->fotoPrincipal)) {
                    // Si es un string, lo pasamos como está
                    $perfilData['fotoPrincipal'] = $user->perfil->fotoPrincipal;
                } else {
                    $perfilData['fotoPrincipal'] = null;
                }
            }

            $userData = [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apodo' => $user->apodo,
                'email' => $user->email,
                'rol' => $user->rol,
                'estado' => $user->estado,
                'foto_principal' => $user->foto_principal,
                'avatar' => $avatar,
                'verificado' => $user->estado === 'verificado' || $user->email_verificado_en !== null,
                'email_verificado_en' => $user->email_verificado_en,
                'created_at' => $user->created_at,
                'perfil' => $perfilData,
            ];
        }

        // Admin autenticado (guard "admin") — separado del usuario normal de
        // arriba. AdminLayout.vue lee esto en `page.props.auth.admin`, así
        // que la clave tiene que vivir ahí adentro (antes se compartía en
        // `page.props.admin.user`, una ruta distinta que el layout nunca leía).
        $adminUser = $request->user('admin');
        $adminData = null;

        if ($adminUser) {
            $adminData = [
                'id' => $adminUser->id,
                'nombre' => $adminUser->nombre,
                'nickname' => $adminUser->nickname,
                'email' => $adminUser->email,
                'telefono' => $adminUser->telefono,
                'rol' => $adminUser->rol,
                'foto_perfil_url' => $this->resolverUrlAdmin($adminUser->foto_perfil_url),
                'doble_factor_habilitado' => (bool) $adminUser->autenticacion_doble_habilitada,
            ];
        }
        
        // LOG para debug
        Log::info('📦 HandleInertiaRequests - Share:', [
            'flash' => $flash,
            'toast' => $toast,
            'has_errors' => !is_null($errors),
            'session_id' => session()->getId(),
            'user_type' => $request->user() ? 'web' : ($request->user('admin') ? 'admin' : 'guest'),
            'user_id' => $userData ? $userData['id'] : null,
            'user_avatar' => $userData ? $userData['avatar'] : null,
        ]);
        
        return [
            ...parent::share($request),
            
            // Compartir flash messages (para usuarios web)
            'flash' => $flash,
            
            // Compartir toast directamente (para compatibilidad)
            'toast' => $toast,
            
            // Compartir errores de validación
            'errors' => $errors ? $errors->getBag('default')->getMessages() : (object) [],
            
            // Compartir usuario autenticado (web) y admin (guard admin) juntos
            // bajo "auth", que es lo que leen las vistas.
            'auth' => [
                'user' => $userData,
                'admin' => $adminData,
            ],
            
            // Se deja por compatibilidad con cualquier otro lugar que ya
            // dependiera de esta ruta — el modelo completo ya no expone el
            // secreto/códigos de 2FA porque están en $hidden en el modelo.
            'admin' => [
                'user' => $adminUser,
            ],
            'badges' => fn () => $this->badges($request),
            'notificaciones' => fn () => $this->notificaciones($request),
        ];
    }

    /** Misma lógica que ya usan Eventos/Contenido: URL externa igual, ruta interna resuelta al disco público. */
    private function resolverUrlAdmin(?string $ruta): ?string
    {
        if (! $ruta) {
            return null;
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }

        return Storage::disk('public')->url($ruta);
    }

    /**
     * Contadores reales para los badges del sidebar y la campana de notificaciones.
     * Solo se calculan si hay un admin autenticado, para no pegarle a la BD en /admin/login.
     */
    private function badges(Request $request): array
    {
        // Si no hay admin autenticado, retornar ceros
        if (!$request->user('admin')) {
            return [
                'invitacionesPendientes' => 0,
                'pagosPendientes' => 0,
                'mensajesSoporteSinLeer' => 0,
                'notificaciones' => 0,
            ];
        }

        // Contar pagos pendientes
        $pagosPendientes = Transaccion::where('estado', 'pendiente')->count();

        // Mensajes de soporte (usuario → admin) que siguen sin leerse
        $mensajesSoporteSinLeer = MensajeSoporte::deUsuario()->noLeidos()->count();

        return [
            'invitacionesPendientes' => 0,
            'pagosPendientes' => $pagosPendientes,
            'mensajesSoporteSinLeer' => $mensajesSoporteSinLeer,
            'notificaciones' => $pagosPendientes + $mensajesSoporteSinLeer,
        ];
    }

    /**
     * Lista real que alimenta el panel de la campana (no solo el número).
     * Combina pagos pendientes y mensajes de soporte sin leer, ordenados
     * por fecha, tope de 5.
     */
    private function notificaciones(Request $request): array
    {
        if (! $request->user('admin')) {
            return [];
        }

        $pagos = Transaccion::with('usuario:id,nombre,apodo')
            ->where('estado', 'pendiente')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($t) => [
                'id' => 'pago-' . $t->id,
                'titulo' => 'Pago pendiente de aprobación',
                'mensaje' => '@' . ($t->usuario?->apodo ?? $t->usuario?->nombre ?? 'usuario')
                    . ' — $' . number_format($t->monto, 2),
                'fecha' => $t->created_at->diffForHumans(),
                'route' => 'admin.cobros.index',
                'params' => [],
                'ordenar_por' => $t->created_at,
            ]);

        $mensajes = MensajeSoporte::with('usuario:id,nombre,apodo')
            ->deUsuario()
            ->noLeidos()
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($m) => [
                'id' => 'soporte-' . $m->id,
                'titulo' => 'Nuevo mensaje de soporte',
                'mensaje' => '@' . ($m->usuario?->apodo ?? $m->usuario?->nombre ?? 'usuario')
                    . ': ' . str($m->texto)->limit(50)->value(),
                'fecha' => $m->created_at->diffForHumans(),
                'route' => 'admin.mensajes.index',
                'params' => ['soporte' => $m->soporte_id],
                'ordenar_por' => $m->created_at,
            ]);

        return $pagos->concat($mensajes)
            ->sortByDesc('ordenar_por')
            ->take(5)
            ->map(fn ($n) => collect($n)->except('ordenar_por')->all())
            ->values()
            ->all();
    }
}