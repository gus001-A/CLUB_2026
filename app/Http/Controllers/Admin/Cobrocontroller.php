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
        $inicioMes = now()->startOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        // --- KPIs ---
        $ingresosTotales = (float) Transaccion::aprobadas()->sum('monto');

        $cobrosDelMes = (float) Transaccion::aprobadas()
            ->whereIn('tipo', ['suscripcion', 'compra_contenido', 'propina'])
            ->where('created_at', '>=', $inicioMes)
            ->sum('monto');

        $cobrosMesAnterior = (float) Transaccion::aprobadas()
            ->whereIn('tipo', ['suscripcion', 'compra_contenido', 'propina'])
            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])
            ->sum('monto');

        $reembolsosDelMes = (float) Transaccion::where('estado', 'reembolsada')
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

        $transacciones = $query->latest()->paginate(10)->withQueryString();
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

        // --- Gráfica de ingresos (últimos 30 días) ---
        $ingresosPorDia = Transaccion::aprobadas()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as fecha, SUM(monto) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get()
            ->map(fn ($r) => ['fecha' => $r->fecha, 'total' => (float) $r->total]);

        $comisionesDelMes = (float) Transaccion::aprobadas()
            ->where('created_at', '>=', $inicioMes)
            ->sum('comision');

        // --- Tipos de transacción (para el donut, solo aprobadas) ---
        $tiposTotales = Transaccion::aprobadas()
            ->selectRaw('tipo, SUM(monto) as total, COUNT(*) as cantidad')
            ->groupBy('tipo')
            ->get()
            ->map(fn ($r) => [
                'tipo' => $r->tipo,
                'total' => (float) $r->total,
                'cantidad' => $r->cantidad,
            ]);

        // --- Métodos de pago (solo aprobadas) ---
        $totalAprobado = max($ingresosTotales, 0.01); // evita división entre 0
        $metodosPago = Transaccion::aprobadas()
            ->selectRaw('COALESCE(metodo_pago, "otro") as metodo_pago, SUM(monto) as total')
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'metodo' => $r->metodo_pago,
                'total' => (float) $r->total,
                'porcentaje' => round(($r->total / $totalAprobado) * 100),
            ]);

        return Inertia::render('Admin/Cobros/Index', [
            'stats' => [
                'ingresosTotales' => $ingresosTotales,
                'cobrosDelMes' => $cobrosDelMes,
                'cobrosVariacion' => $cobrosMesAnterior > 0
                    ? round((($cobrosDelMes - $cobrosMesAnterior) / $cobrosMesAnterior) * 100, 1)
                    : null,
                'reembolsosDelMes' => $reembolsosDelMes,
                'comisionesDelMes' => $comisionesDelMes,
                'pagosPendientesMonto' => $pagosPendientesMonto,
                'pagosPendientesCount' => $pagosPendientesCount,
            ],
            'transacciones' => $transacciones,
            'filtros' => $request->only(['q', 'tipo', 'desde', 'hasta']),
            'ingresosPorDia' => $ingresosPorDia,
            'tiposTotales' => $tiposTotales,
            'metodosPago' => $metodosPago,
            'pagosPendientes' => Transaccion::with('usuario:id,nombre,apodo')
                ->where('estado', 'pendiente')
                ->latest()
                ->take(5)
                ->get(),
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