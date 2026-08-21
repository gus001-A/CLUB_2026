<?php

namespace App\Http\Middleware;

use App\Models\CodigoInvitacion;
use App\Models\MensajeSoporte;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reporte;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

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

        // ============================================================
        // OBTENER USUARIO NORMAL
        // ============================================================
        $user = $request->user();
        $userData = null;

        // OJO: cuando solo hay sesión de admin activa, $request->user() (guard
        // por defecto) puede regresar el Administrador en vez de null — todo
        // este bloque asume un User de citas (perfil, fotos, etc.), así que
        // hay que descartarlo explícitamente si en realidad es un admin.
        if ($user && !$user instanceof \App\Models\Administrador) {
            // Cargar el perfil con sus relaciones
            $user->load(['perfil.fotoPrincipal']);

            // Determinar el avatar
            $avatar = '/images/shared/avatar-default.jpg';
            $found = false;

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

                // OBTENER AVATAR
                $foto_original = null;

                // 1. Verificar foto_principal en el usuario
                if ($user->foto_principal && !empty($user->foto_principal)) {
                    $foto_original = $user->foto_principal;

                    // CONSTRUIR LA URL CORRECTA
                    if (str_starts_with($foto_original, 'http://') || str_starts_with($foto_original, 'https://')) {
                        $avatar = $foto_original;
                    } elseif (str_starts_with($foto_original, '/')) {
                        $avatar = $foto_original;
                    } else {
                        // La ruta es "perfil/fotos/..." -> /storage/perfil/fotos/...
                        $avatar = '/storage/' . $foto_original;
                    }
                }
                // 2. Si no tiene foto_principal, buscar en perfil
                else if ($user->perfil && $user->perfil->fotos) {
                    $fotos = is_string($user->perfil->fotos)
                        ? json_decode($user->perfil->fotos, true)
                        : $user->perfil->fotos;

                    if (is_array($fotos) && count($fotos) > 0) {
                        foreach ($fotos as $fotoItem) {
                            if (isset($fotoItem['principal']) && $fotoItem['principal'] === true) {
                                if (isset($fotoItem['url']) && !empty($fotoItem['url'])) {
                                    $avatar = $fotoItem['url'];
                                    break;
                                } elseif (isset($fotoItem['ruta_foto']) && !empty($fotoItem['ruta_foto'])) {
                                    $avatar = '/storage/' . $fotoItem['ruta_foto'];
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
            }

            // DATOS DEL USUARIO
            $userData = [
                'id' => $user->id,
                'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
                'apodo' => $user->apodo ?? $user->nombre ?? 'Usuario',
                'email' => $user->email ?? '',
                'rol' => $user->rol ?? 'usuario',
                'estado' => $user->estado ?? 'pendiente',
                'foto_principal' => $user->foto_principal,
                'avatar' => $avatar,
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
        }

        // ============================================================
        // OBTENER ADMINISTRADOR
        // ============================================================
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

        // ============================================================
        // COMPARTIR DATOS CON INERTIA
        // ============================================================
        return array_merge(parent::share($request), [
            'flash' => $flash,
            'toast' => $toast,
            'errors' => $errors ? $errors->getBag('default')->getMessages() : (object) [],

            // DATOS DEL USUARIO EN LA RAIZ
            'usuario' => $userData,

            // DATOS DEL ADMINISTRADOR
            'admin' => $adminData,

            // AUTH - para compatibilidad
            'auth' => [
                'user' => $userData,
                'admin' => $adminData,
            ],

            'badges' => $this->badges($request),
            'notificaciones' => $this->notificaciones($request),
        ]);
    }

    /**
     * Resuelve la URL de la imagen del admin
     */
    private function resolverUrlAdmin(?string $ruta): ?string
    {
        if (!$ruta) {
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
        if (!$request->user('admin')) {
            return [
                'invitacionesPendientes' => 0,
                'pagosPendientes' => 0,
                'reportesPendientes' => 0,
                'mensajesSoporteSinLeer' => 0,
                'pedidosNuevos' => 0,
                'productosStockBajo' => 0,
                'notificaciones' => 0,
            ];
        }

        $pagosPendientes = Transaccion::where('estado', 'pendiente')->count();

        // Mensajes de soporte (usuario → admin) que siguen sin leerse
        $mensajesSoporteSinLeer = MensajeSoporte::deUsuario()->noLeidos()->count();

        // Reportes de usuarios sin revisar
        $reportesPendientes = Reporte::where('estado', 'pendiente')->count();

        // Invitaciones que llegaron a un resultado final (aceptada o
        // expirada) en los últimos 7 días — sin columna extra: no
        // contamos las "pendientes" (esas las mandó el propio admin, no
        // necesita que se le avise de su propio envío), solo lo que hizo
        // el destinatario o lo que venció.
        $ahora = now();
        $desde = $ahora->copy()->subDays(7);
        $invitacionesNotificar = CodigoInvitacion::where(function ($q) use ($ahora, $desde) {
            $q->where('usado_en', '>=', $desde)
                ->orWhere(function ($q2) use ($ahora, $desde) {
                    $q2->whereNull('usado_en')
                        ->whereNotNull('expira_en')
                        ->whereBetween('expira_en', [$desde, $ahora]);
                });
        })->count();

        // Pedidos recién pagados que todavía no se marcan como enviados —
        // se autolimpia solo: en cuanto lo marcas "enviado" desde el Show,
        // deja de contar aquí.
        $pedidosNuevos = Pedido::where('estado', 'pagado')->count();

        // Productos activos con 5 unidades o menos (incluye agotados) —
        // se autolimpia en cuanto repongas stock.
        $umbralStockBajo = 5;
        $productosStockBajo = Producto::where('esta_activo', true)
            ->where('stock', '<=', $umbralStockBajo)
            ->count();

        return [
            'invitacionesPendientes' => $invitacionesNotificar,
            'pagosPendientes' => $pagosPendientes,
            'reportesPendientes' => $reportesPendientes,
            'mensajesSoporteSinLeer' => $mensajesSoporteSinLeer,
            'pedidosNuevos' => $pedidosNuevos,
            'productosStockBajo' => $productosStockBajo,
            'notificaciones' => $pagosPendientes + $mensajesSoporteSinLeer + $reportesPendientes
                + $invitacionesNotificar + $pedidosNuevos + $productosStockBajo,
        ];
    }

    /**
     * Lista real que alimenta el panel de la campana (no solo el número).
     * Combina pagos pendientes y mensajes de soporte sin leer, ordenados
     * por fecha, tope de 5.
     */
    private function notificaciones(Request $request): array
    {
        if (!$request->user('admin')) {
            return [];
        }

        $pagos = Transaccion::with('usuario:id,nombre,apodo')
            ->where('estado', 'pendiente')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($t) => [
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
            ->map(fn($m) => [
                'id' => 'soporte-' . $m->id,
                'titulo' => 'Nuevo mensaje de soporte',
                'mensaje' => '@' . ($m->usuario?->apodo ?? $m->usuario?->nombre ?? 'usuario')
                    . ': ' . str($m->texto)->limit(50)->value(),
                'fecha' => $m->created_at->diffForHumans(),
                'route' => 'admin.mensajes.index',
                'params' => ['soporte' => $m->soporte_id],
                'ordenar_por' => $m->created_at,
            ]);

        $reportes = Reporte::with('reportado:id,nombre,apodo')
            ->where('estado', 'pendiente')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'id' => 'reporte-' . $r->id,
                'titulo' => 'Nuevo reporte: ' . ($r->tipo_nombre ?? $r->tipo),
                'mensaje' => 'Sobre @' . ($r->reportado?->apodo ?? $r->reportado?->nombre ?? 'usuario eliminado'),
                'fecha' => $r->created_at->diffForHumans(),
                'route' => 'admin.soporte.index',
                'params' => [],
                'ordenar_por' => $r->created_at,
            ]);

        $ahora = now();
        $desde = $ahora->copy()->subDays(7);
        $invitaciones = CodigoInvitacion::where(function ($q) use ($ahora, $desde) {
            $q->where('usado_en', '>=', $desde)
                ->orWhere(function ($q2) use ($ahora, $desde) {
                    $q2->whereNull('usado_en')
                        ->whereNotNull('expira_en')
                        ->whereBetween('expira_en', [$desde, $ahora]);
                });
        })
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($c) {
                $aceptada = !is_null($c->usado_en);
                $nombre = $c->nombre_destinatario ?: $c->email;

                return [
                    'id' => 'invitacion-' . $c->id,
                    'titulo' => $aceptada ? 'Invitación aceptada' : 'Invitación expirada',
                    'mensaje' => $aceptada ? "{$nombre} aceptó la invitación" : "{$nombre} — la invitación expiró sin usarse",
                    'fecha' => ($aceptada ? $c->usado_en : $c->expira_en)->diffForHumans(),
                    'route' => 'admin.invitaciones.index',
                    'params' => ['q' => $c->codigo],
                    'ordenar_por' => $aceptada ? $c->usado_en : $c->expira_en,
                ];
            });

        $pedidosNuevos = Pedido::with('usuario:id,nombre,apodo')
            ->where('estado', 'pagado')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id' => 'pedido-' . $p->id,
                'titulo' => 'Pedido nuevo',
                'mensaje' => "#{$p->numero_pedido} de @" . ($p->usuario?->apodo ?? 'usuario') . ' — $' . number_format($p->total, 2),
                'fecha' => $p->created_at->diffForHumans(),
                'route' => 'admin.shop.show',
                'params' => ['pedido' => $p->id],
                'ordenar_por' => $p->created_at,
            ]);

        $productosStockBajo = Producto::where('esta_activo', true)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id' => 'stock-' . $p->id,
                'titulo' => $p->stock <= 0 ? 'Producto agotado' : 'Stock bajo',
                'mensaje' => $p->stock <= 0 ? "\"{$p->nombre}\" ya no tiene stock" : "\"{$p->nombre}\" — quedan {$p->stock} unidades",
                'fecha' => $p->updated_at->diffForHumans(),
                'route' => 'admin.productos.show',
                'params' => ['producto' => $p->id],
                'ordenar_por' => $p->updated_at,
            ]);

        return $pagos->concat($mensajes)
            ->concat($reportes)
            ->concat($invitaciones)
            ->concat($pedidosNuevos)
            ->concat($productosStockBajo)
            ->sortByDesc('ordenar_por')
            ->take(5)
            ->map(fn($n) => collect($n)->except('ordenar_por')->all())
            ->values()
            ->all();
    }
}