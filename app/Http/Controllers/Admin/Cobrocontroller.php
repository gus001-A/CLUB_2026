<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CobroController extends Controller
{
    public function index(Request $request): Response
    {
        $periodoResumen = $request->string('periodo_resumen')->value() ?: 'mes';
        $periodoIngresos = $request->string('periodo_ingresos')->value() ?: 'mes';
        $periodoTipos = $request->string('periodo_tipos')->value() ?: 'mes';

        $inicioMes = now()->startOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        // --- KPIs (siempre mes calendario real, para comparar "vs mes anterior") ---
        $ingresosTotales = (float) Transaccion::aprobadas()->sum('monto');

        $cobrosDelMesKpi = (float) Transaccion::aprobadas()
            ->whereIn('tipo', ['suscripcion', 'compra_contenido', 'propina'])
            ->where('created_at', '>=', $inicioMes)
            ->sum('monto');

        $cobrosMesAnterior = (float) Transaccion::aprobadas()
            ->whereIn('tipo', ['suscripcion', 'compra_contenido', 'propina'])
            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
            ->sum('monto');

        $reembolsosDelMesKpi = (float) Transaccion::where('estado', 'reembolsada')
            ->where('created_at', '>=', $inicioMes)
            ->sum('monto');

        $pagosPendientesQuery = Transaccion::where('estado', 'pendiente');
        $pagosPendientesMonto = (float) (clone $pagosPendientesQuery)->sum('monto');
        $pagosPendientesCount = (clone $pagosPendientesQuery)->count();

        // --- Filtros de la tabla ---
        $query = Transaccion::with('usuario:id,nombre,apodo');

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('tipo', $tipo);
        }

        if ($desde = $request->date('desde')) {
            $query->where('created_at', '>=', $desde->startOfDay());
        }

        if ($hasta = $request->date('hasta')) {
            $query->where('created_at', '<=', $hasta->endOfDay());
        }

        if ($search = $request->string('q')->trim()->value()) {
            $query->whereHas('usuario', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apodo', 'like', "%{$search}%");
            });
        }

        $transacciones = $query->latest()->paginate(4)->withQueryString();
        $transacciones->through(fn ($t) => [
            'id' => $t->id,
            'usuario' => $t->usuario,
            'tipo' => $t->tipo,
            'tipo_nombre' => $t->tipo_nombre,
            'monto' => $t->monto,
            'estado' => $t->estado,
            'estado_nombre' => $t->estado_nombre,
            'metodo_pago_nombre' => $t->metodo_pago_nombre,
            'es_reembolso' => in_array($t->estado, ['reembolsada']) || $t->tipo === 'retiro',
            'created_at' => $t->created_at,
        ]);

        // --- Resumen de Cobros (obedece el selector "periodo_resumen") ---
        [$inicioResumen, $finResumen] = $this->rangoPeriodo($periodoResumen);
        $cobrosDelMes = (float) Transaccion::aprobadas()
            ->whereIn('tipo', ['suscripcion', 'compra_contenido', 'propina'])
            ->whereBetween('created_at', [$inicioResumen, $finResumen])
            ->sum('monto');
        $comisionesDelMes = (float) Transaccion::aprobadas()
            ->whereBetween('created_at', [$inicioResumen, $finResumen])
            ->sum('comision');
        $reembolsosDelMes = (float) Transaccion::where('estado', 'reembolsada')
            ->whereBetween('created_at', [$inicioResumen, $finResumen])
            ->sum('monto');

        // --- Gráfica de ingresos (obedece el selector "periodo_ingresos") ---
        // Se rellena con $0 cada día/mes sin transacciones dentro del rango,
        // así "Esta semana" y "Este mes" se ven distintos aunque casi todo
        // esté en cero (en vez de solo mostrar los días con datos reales).
        [$inicioIngresos, $finIngresos, $granularidad] = $this->rangoPeriodo($periodoIngresos);
        $formatoFecha = $granularidad === 'mes' ? "DATE_FORMAT(created_at, '%Y-%m-01')" : 'DATE(created_at)';
        $datosReales = Transaccion::aprobadas()
            ->whereBetween('created_at', [$inicioIngresos, $finIngresos])
            ->selectRaw("{$formatoFecha} as fecha, SUM(monto) as total")
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $ingresosPorDia = collect();
        if ($granularidad === 'mes') {
            $cursor = $inicioIngresos->copy()->startOfMonth();
            while ($cursor->lte($finIngresos)) {
                $clave = $cursor->format('Y-m-01');
                $ingresosPorDia->push(['fecha' => $clave, 'total' => (float) ($datosReales[$clave] ?? 0)]);
                $cursor->addMonth();
            }
        } else {
            $cursor = $inicioIngresos->copy();
            while ($cursor->lte($finIngresos)) {
                $clave = $cursor->format('Y-m-d');
                $ingresosPorDia->push(['fecha' => $clave, 'total' => (float) ($datosReales[$clave] ?? 0)]);
                $cursor->addDay();
            }
        }

        // --- Cobros / Reembolsos / Otros (obedece el selector "periodo_tipos") ---
        [$inicioTipos, $finTipos] = $this->rangoPeriodo($periodoTipos);
        $totalCobrosCat = (float) Transaccion::where('estado', 'aprobada')
            ->whereBetween('created_at', [$inicioTipos, $finTipos])->sum('monto');
        $totalReembolsosCat = (float) Transaccion::where('estado', 'reembolsada')
            ->whereBetween('created_at', [$inicioTipos, $finTipos])->sum('monto');
        $totalOtrosCat = (float) Transaccion::whereNotIn('estado', ['aprobada', 'reembolsada'])
            ->whereBetween('created_at', [$inicioTipos, $finTipos])->sum('monto');

        $categoriasResumen = [
            ['id' => 'cobros', 'label' => 'Cobros', 'total' => $totalCobrosCat],
            ['id' => 'reembolsos', 'label' => 'Reembolsos', 'total' => $totalReembolsosCat],
            ['id' => 'otros', 'label' => 'Otros', 'total' => $totalOtrosCat],
        ];

        // --- Métodos de pago (solo aprobadas, sin selector de periodo por ahora) ---
        $metodosLabel = [
            'tarjeta_credito' => 'Tarjeta de Crédito',
            'tarjeta_debito' => 'Tarjeta de Débito',
            'paypal' => 'PayPal',
            'transferencia' => 'Transferencia',
            'otro' => 'Otro',
        ];
        $totalAprobado = max($ingresosTotales, 0.01); // evita división entre 0
        $metodosPago = Transaccion::aprobadas()
            ->selectRaw('COALESCE(NULLIF(metodo_pago, ""), "otro") as metodo_pago, SUM(monto) as total')
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'metodo' => $r->metodo_pago,
                'metodo_nombre' => $metodosLabel[$r->metodo_pago] ?? 'Otro',
                'total' => (float) $r->total,
                'porcentaje' => round(($r->total / $totalAprobado) * 100),
            ]);

        return Inertia::render('Admin/Cobros/Index', [
            'stats' => [
                'ingresosTotales' => $ingresosTotales,
                'cobrosDelMes' => $cobrosDelMesKpi,
                'cobrosVariacion' => $cobrosMesAnterior > 0
                    ? round((($cobrosDelMesKpi - $cobrosMesAnterior) / $cobrosMesAnterior) * 100, 1)
                    : null,
                'reembolsosDelMes' => $reembolsosDelMesKpi,
                'pagosPendientesMonto' => $pagosPendientesMonto,
                'pagosPendientesCount' => $pagosPendientesCount,
            ],
            'resumen' => [
                'cobrosDelMes' => $cobrosDelMes,
                'comisionesDelMes' => $comisionesDelMes,
                'reembolsosDelMes' => $reembolsosDelMes,
            ],
            'transacciones' => $transacciones,
            'filtros' => $request->only(['q', 'tipo', 'desde', 'hasta']),
            'ingresosPorDia' => $ingresosPorDia,
            'categoriasResumen' => $categoriasResumen,
            'metodosPago' => $metodosPago,
            'pagosPendientes' => Transaccion::with('usuario:id,nombre,apodo')
                ->where('estado', 'pendiente')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * Devuelve [inicio, fin, granularidad] según el periodo elegido en cualquiera
     * de los 3 selectores (Resumen de Cobros / Ingresos / Cobros y Reembolsos).
     */
    private function rangoPeriodo(string $periodo): array
    {
        return match ($periodo) {
            'semana' => [now()->subDays(6)->startOfDay(), now()->endOfDay(), 'dia'],
            'anio' => [now()->subMonths(11)->startOfMonth(), now()->endOfDay(), 'mes'],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay(), 'dia'], // 'mes' (default)
        };
    }

    /**
     * Vista de historial completo de transacciones, con más filtros
     * y desglose por estado/tipo — separado del dashboard de cobros.
     */
    public function transacciones(Request $request): Response
    {
        $query = Transaccion::with('usuario:id,nombre,apodo');

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('tipo', $tipo);
        }
        if ($estado = $request->string('estado')->value()) {
            $query->where('estado', $estado);
        }
        if ($metodoPago = $request->string('metodo_pago')->value()) {
            $query->where('metodo_pago', $metodoPago);
        }
        if ($desde = $request->date('desde')) {
            $query->where('created_at', '>=', $desde->startOfDay());
        }
        if ($hasta = $request->date('hasta')) {
            $query->where('created_at', '<=', $hasta->endOfDay());
        }
        if ($search = $request->string('q')->trim()->value()) {
            $query->whereHas('usuario', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apodo', 'like', "%{$search}%");
            });
        }

        $transacciones = $query->latest()->paginate(10)->withQueryString();
        $transacciones->through(fn ($t) => [
            'id' => $t->id,
            'usuario' => $t->usuario,
            'tipo' => $t->tipo,
            'tipo_nombre' => $t->tipo_nombre,
            'monto' => $t->monto,
            'comision' => $t->comision,
            'monto_neto' => $t->monto_neto,
            'estado' => $t->estado,
            'estado_nombre' => $t->estado_nombre,
            'metodo_pago_nombre' => $t->metodo_pago_nombre,
            'es_reembolso' => $t->estado === 'reembolsada',
            'created_at' => $t->created_at,
        ]);

        // --- Desglose por estado (para las tarjetas de arriba) ---
        $porEstadoLabel = ['aprobada' => 'Aprobadas', 'pendiente' => 'Pendientes', 'rechazada' => 'Rechazadas', 'reembolsada' => 'Reembolsadas', 'retirada' => 'Retiradas'];
        $porEstado = Transaccion::selectRaw('estado, COUNT(*) as cantidad, SUM(monto) as total')
            ->groupBy('estado')
            ->get()
            ->map(fn ($r) => [
                'estado' => $r->estado,
                'label' => $porEstadoLabel[$r->estado] ?? $r->estado,
                'cantidad' => (int) $r->cantidad,
                'total' => (float) $r->total,
            ]);

        // --- Desglose por tipo ---
        $porTipoLabel = ['suscripcion' => 'Suscripción', 'compra_contenido' => 'Compra de contenido', 'propina' => 'Propina', 'retiro' => 'Retiro'];
        $porTipo = Transaccion::selectRaw('tipo, COUNT(*) as cantidad, SUM(monto) as total')
            ->groupBy('tipo')
            ->get()
            ->map(fn ($r) => [
                'tipo' => $r->tipo,
                'label' => $porTipoLabel[$r->tipo] ?? $r->tipo,
                'cantidad' => (int) $r->cantidad,
                'total' => (float) $r->total,
            ]);

        return Inertia::render('Admin/Cobros/Transacciones', [
            'transacciones' => $transacciones,
            'filtros' => $request->only(['q', 'tipo', 'estado', 'metodo_pago', 'desde', 'hasta']),
            'porEstado' => $porEstado,
            'porTipo' => $porTipo,
            'totalGeneral' => (float) Transaccion::sum('monto'),
            'totalRegistros' => Transaccion::count(),
        ]);
    }

    public function show(Transaccion $cobro): Response
    {
        // Ajusta 'creador.usuario' si tu modelo Creador no tiene ese nombre
        // de relación hacia el usuario dueño del perfil de creador.
        $cobro->load(['usuario:id,nombre,apodo,email', 'creador.usuario:id,nombre,apodo']);

        return Inertia::render('Admin/Cobros/Show', [
            'transaccion' => [
                'id' => $cobro->id,
                'usuario' => $cobro->usuario,
                'creador' => $cobro->creador,
                'tipo' => $cobro->tipo,
                'tipo_nombre' => $cobro->tipo_nombre,
                'monto' => (float) $cobro->monto,
                'comision' => (float) $cobro->comision,
                'monto_neto' => (float) $cobro->monto_neto,
                'moneda' => $cobro->moneda,
                'estado' => $cobro->estado,
                'estado_nombre' => $cobro->estado_nombre,
                'metodo_pago' => $cobro->metodo_pago,
                'metodo_pago_nombre' => $cobro->metodo_pago_nombre,
                'pago_id' => $cobro->pago_id,
                'metadatos' => $cobro->metadatos,
                'created_at' => $cobro->created_at,
                'updated_at' => $cobro->updated_at,
            ],
        ]);
    }

    public function exportar(Request $request)
    {
        $query = Transaccion::with('usuario:id,nombre,apodo');

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('tipo', $tipo);
        }
        if ($desde = $request->date('desde')) {
            $query->where('created_at', '>=', $desde->startOfDay());
        }
        if ($hasta = $request->date('hasta')) {
            $query->where('created_at', '<=', $hasta->endOfDay());
        }
        if ($search = $request->string('q')->trim()->value()) {
            $query->whereHas('usuario', fn ($q) => $q->where('nombre', 'like', "%{$search}%")->orWhere('apodo', 'like', "%{$search}%"));
        }

        $transacciones = $query->latest()->get();

        $filename = 'transacciones_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($transacciones) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Usuario', 'Correo', 'Tipo', 'Monto', 'Estado', 'Método de pago', 'Fecha']);

            foreach ($transacciones as $t) {
                fputcsv($handle, [
                    'TRX-' . str_pad($t->id, 4, '0', STR_PAD_LEFT),
                    $t->usuario?->nombre,
                    $t->usuario?->apodo,
                    $t->tipo_nombre,
                    $t->monto,
                    $t->estado_nombre,
                    $t->metodo_pago_nombre,
                    $t->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function aprobar(Transaccion $cobro)
    {
        $cobro->update(['estado' => 'aprobada']);

        return back()->with('success', "Transacción #{$cobro->id} aprobada.");
    }

    public function reembolsar(Transaccion $cobro)
    {
        $cobro->update(['estado' => 'reembolsada']);

        return back()->with('success', "Transacción #{$cobro->id} reembolsada.");
    }
}