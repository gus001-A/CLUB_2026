<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contenido;
use App\Models\Evento;
use App\Models\Pedido;
use App\Models\Suscripcion;
use App\Models\Transaccion;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $hoy = now()->startOfDay();
        $inicioMesActual = now()->startOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        // --- Variación de ingresos vs mes anterior ---
        $ingresosMesActual = (float) Transaccion::where('estado', 'aprobada')
            ->where('created_at', '>=', $inicioMesActual)
            ->sum('monto');
        $ingresosMesAnterior = (float) Transaccion::where('estado', 'aprobada')
            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
            ->sum('monto');
        $ingresosVariacion = $ingresosMesAnterior > 0
            ? round((($ingresosMesActual - $ingresosMesAnterior) / $ingresosMesAnterior) * 100)
            : null;

        // --- Variación de ventas en shop vs mes anterior ---
        $ventasMesActual = (float) Pedido::pagados()
            ->where('created_at', '>=', $inicioMesActual)
            ->sum('total');
        $ventasMesAnterior = (float) Pedido::pagados()
            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
            ->sum('total');
        $ventasVariacion = $ventasMesAnterior > 0
            ? round((($ventasMesActual - $ventasMesAnterior) / $ventasMesAnterior) * 100)
            : null;

        return Inertia::render('Admin/Dashboard', [

            'stats' => [
                'usuariosTotales' => User::count(),
                'usuariosNuevosHoy' => User::where('created_at', '>=', $hoy)->count(),

                'ingresosTotales' => (float) Transaccion::where('estado', 'aprobada')->sum('monto'),
                'ingresosVariacion' => $ingresosVariacion,

                'suscripcionesActivas' => Suscripcion::activas()->count(),
                'suscripcionesNuevasHoy' => Suscripcion::activas()
                    ->where('created_at', '>=', $hoy)
                    ->count(),

                'ventasShop' => (float) Pedido::pagados()->sum('total'),
                'ventasVariacion' => $ventasVariacion,
            ],

            // Tabla "Gestión de Usuarios" (con filtros de búsqueda/rol/estado)
            'gestionUsuarios' => (function () use ($request) {
                $query = User::query();

                if ($search = $request->string('q')->trim()->value()) {
                    $query->where(function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                            ->orWhere('apodo', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                }

                if ($rol = $request->string('rol')->value()) {
                    $query->where('rol', $rol);
                }

                if ($estado = $request->string('estado')->value()) {
                    $query->where('estado', $estado);
                }

                return $query->latest()->paginate(4)->withQueryString()->through(fn ($u) => [
                    'id' => $u->id,
                    'nombre' => $u->nombre,
                    'apodo' => $u->apodo,
                    'email' => $u->email,
                    'rol' => $u->rol,
                    'estado' => $u->estado,
                    'created_at' => $u->created_at,
                ]);
            })(),

            'filtros' => $request->only(['q', 'rol', 'estado']),

            // Tu Vue usa: c.usuario.apodo, c.concepto, c.monto, c.tiempo
            // El where <= now() evita que datos de prueba con fecha futura
            // se cuelen arriba de los de "hoy" (latest() los pondría primero).
            'cobrosRecientes' => Transaccion::with('usuario:id,nombre,apodo')
                ->where('created_at', '<=', now())
                ->latest()
                ->take(4)
                ->get()
                ->map(fn($t) => [
                    'id' => $t->id,
                    'usuario' => [
                        'apodo' => $t->usuario?->apodo ?? $t->usuario?->nombre ?? 'usuario',
                    ],
                    'concepto' => $t->tipo_nombre,
                    'monto' => (float) $t->monto,
                    'tiempo' => $this->tiempoRelativo($t->created_at),
                ]),

            // Tu Vue usa: e.titulo, e.fecha (string ya formateado), e.imagen, e.estado (de publicación)
            'eventosProximos' => Evento::where('fecha', '>=', now())
                ->orderBy('fecha')
                ->take(3)
                ->get(['id', 'nombre', 'fecha', 'estado', 'imagen'])
                ->map(fn($e) => [
                    'id' => $e->id,
                    'titulo' => $e->nombre,
                    'fecha' => $e->fecha_formateada,
                    'estado' => $e->estado, // borrador/programado/publicado/cancelado
                    'imagen' => $e->imagen,
                ]),

            // Actualmente no se usa en el template (las 5 acciones están hardcodeadas
            // en el Vue), pero la dejo lista por si luego la recorres con v-for.
            'accionesRapidas' => [
                ['label' => 'Bloquear Usuario', 'desc' => 'Restringe el acceso de un usuario', 'icon' => 'pi-lock', 'route' => 'admin.usuarios.index'],
                ['label' => 'Ver Usuarios', 'desc' => 'Consulta todos los usuarios registrados', 'icon' => 'pi-users', 'route' => 'admin.usuarios.index'],
                ['label' => 'Ver Cobros', 'desc' => 'Revisa pagos y transacciones', 'icon' => 'pi-dollar', 'route' => 'admin.cobros.index'],
                ['label' => 'Crear Evento', 'desc' => 'Organiza un nuevo evento', 'icon' => 'pi-calendar-plus', 'route' => 'admin.eventos.create'],
                ['label' => 'Enviar Invitación', 'desc' => 'Invita usuarios a la plataforma', 'icon' => 'pi-envelope', 'route' => 'admin.invitaciones.create'],
            ],

            'actividadReciente' => $this->actividadReciente(),
        ]);
    }

    /**
     * "Hoy" / "Ayer" / "Hace X días" / fecha — usado en Cobros Recientes.
     *
     * En Carbon 3, diffInDays() ahora devuelve un float con signo por
     * default (antes siempre era entero absoluto) — por eso forzamos
     * abs() + (int). También cubrimos fechas futuras (datos de prueba
     * con created_at posterior a "ahora"), donde "Hace X días" no
     * tendría sentido.
     */
    private function tiempoRelativo($fecha): string
    {
        $fecha = \Illuminate\Support\Carbon::parse($fecha);

        if ($fecha->isFuture()) return $fecha->format('d/m/Y');

        $segundos = (int) abs($fecha->diffInSeconds(now()));

        if ($segundos < 60) return 'Hace unos segundos';

        if ($segundos < 3600) {
            $min = intdiv($segundos, 60);
            return 'Hace ' . $min . ' minuto' . ($min === 1 ? '' : 's');
        }

        if ($fecha->isToday()) {
            $horas = intdiv($segundos, 3600);
            return 'Hace ' . $horas . ' hora' . ($horas === 1 ? '' : 's');
        }

        if ($fecha->isYesterday()) return 'Ayer · ' . $fecha->format('h:i a');

        $dias = (int) abs($fecha->diffInDays(now()));
        if ($dias < 7) return 'Hace ' . $dias . ' días';

        return $fecha->format('d/m/Y');
    }

    /**
     * Actividad reciente del sistema.
     * Todas las consultas filtran created_at <= now() (mismo motivo que
     * cobrosRecientes): sin ese filtro, datos de prueba con fecha futura
     * se cuelan arriba de las actividades de hoy.
     */
    private function actividadReciente(): array
    {
        $usuarios = User::where('created_at', '<=', now())
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($u) => [
                'id' => 'u-' . $u->id,
                'tipo' => 'usuario_nuevo',
                'icon' => 'pi-user-plus',
                'texto' => "Nuevo usuario registrado: @{$u->apodo}",
                'fecha' => $u->created_at,
            ]);

        $pagos = Transaccion::with('usuario:id,apodo,nombre')
            ->where('estado', 'aprobada')
            ->where('created_at', '<=', now())
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($t) => [
                'id' => 't-' . $t->id,
                'tipo' => 'pago',
                'icon' => 'pi-dollar',
                'texto' => 'Pago de $' . number_format($t->monto, 2) .
                    ' por @' . ($t->usuario?->apodo ?? $t->usuario?->nombre ?? 'usuario'),
                'fecha' => $t->created_at,
            ]);

        $eventos = Evento::where('created_at', '<=', now())
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($e) => [
                'id' => 'e-' . $e->id,
                'tipo' => 'evento',
                'icon' => 'pi-calendar',
                'texto' => "Nuevo evento creado: {$e->nombre}",
                'fecha' => $e->created_at,
            ]);

        // Ícono + etiqueta por tipo de contenido (foto/video/galería/audio/...)
        $iconoPorTipo = [
            'foto' => 'pi-image', 'video' => 'pi-video', 'galeria' => 'pi-images',
            'audio' => 'pi-volume-up', 'articulo' => 'pi-file-edit', 'documento' => 'pi-file',
            'exclusivo' => 'pi-star',
        ];
        $labelPorTipo = [
            'foto' => 'Foto', 'video' => 'Video', 'galeria' => 'Galería',
            'audio' => 'Audio', 'articulo' => 'Artículo', 'documento' => 'Documento',
            'exclusivo' => 'Exclusivo',
        ];
        $contenidos = Contenido::where('created_at', '<=', now())
            ->latest()
            ->take(6)
            ->get()
            ->map(fn($c) => [
                'id' => 'c-' . $c->id,
                'tipo' => 'contenido',
                'icon' => $iconoPorTipo[$c->tipo] ?? 'pi-file',
                'texto' => 'Nuevo contenido (' . ($labelPorTipo[$c->tipo] ?? $c->tipo) . '): ' . ($c->titulo ?: 'Sin título'),
                'fecha' => $c->created_at,
            ]);

        return $usuarios
            ->concat($pagos)
            ->concat($eventos)
            ->concat($contenidos)
            ->sortByDesc('fecha')
            ->take(5)
            ->values()
            ->all();
    }
}