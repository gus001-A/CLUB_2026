<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemPedido;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(Request $request): Response
    {
        // Los pedidos "carrito" no son ventas reales todavía
        $base = Pedido::where('estado', '!=', 'carrito');

        $pedidosTotales = (clone $base)->count();
        $ventasTotales = (float) (clone $base)->sum('total');
        $pedidosCompletados = (clone $base)->where('estado', 'entregado')->count();

        $inicioMes = now()->startOfMonth();
        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();
        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();

        $ventasEsteMes = (float) (clone $base)->where('created_at', '>=', $inicioMes)->sum('total');
        $ventasMesAnterior = (float) (clone $base)->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])->sum('total');
        $variacion = $ventasMesAnterior > 0 ? round((($ventasEsteMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1) : null;

        // --- Periodo del "Resumen de Ventas" (día / semana / mes) ---
        $periodo = $request->string('periodo')->value() ?: 'mes';
        $inicioPeriodo = match ($periodo) {
            'dia' => now()->startOfDay(),
            'semana' => now()->startOfWeek(),
            default => now()->startOfMonth(),
        };

        $query = Pedido::with(['usuario:id,nombre,apodo', 'items.producto:id,nombre,imagenes'])->where('estado', '!=', 'carrito');

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_pedido', 'like', "%{$search}%")
                    ->orWhereHas('usuario', fn ($u) => $u->where('nombre', 'like', "%{$search}%")->orWhere('apodo', 'like', "%{$search}%"));
            });
        }

        if ($estado = $request->string('estado')->value()) {
            $query->where('estado', $estado);
        }

        if ($metodo = $request->string('metodo')->value()) {
            $query->where('metodo_pago', $metodo);
        }

        $pedidos = $query->latest()->paginate(4)->withQueryString();
        $pedidos->through(fn ($p) => [
            'id' => $p->id,
            'numero_pedido' => $p->numero_pedido,
            'usuario' => $p->usuario,
            'total_items' => $p->items->sum('cantidad'),
            'miniaturas' => $p->items->take(3)->map(fn ($i) => $this->resolverUrl($i->producto?->imagen_principal))->filter()->values(),
            'total' => $p->total,
            'metodo_pago' => $p->metodo_pago,
            'estado' => $p->estado,
            'created_at' => $p->created_at,
        ]);

        // --- Productos más vendidos ---
        $masVendidos = ItemPedido::whereHas('pedido', fn ($q) => $q->where('estado', '!=', 'carrito'))
            ->selectRaw('producto_id, SUM(cantidad) as unidades, SUM(total) as ingresos')
            ->groupBy('producto_id')
            ->orderByDesc('unidades')
            ->with('producto:id,nombre,imagenes')
            ->take(5)
            ->get()
            ->map(fn ($r) => [
                'nombre' => $r->producto?->nombre ?? 'Producto eliminado',
                'imagen' => $this->resolverUrl($r->producto?->imagen_principal),
                'unidades' => (int) $r->unidades,
                'ingresos' => (float) $r->ingresos,
            ]);

        // --- Ventas por categoría ---
        $ventasPorCategoria = ItemPedido::whereHas('pedido', fn ($q) => $q->where('estado', '!=', 'carrito'))
            ->join('productos', 'productos.id', '=', 'items_pedido.producto_id')
            ->selectRaw('COALESCE(productos.categoria, "Sin categoría") as categoria, SUM(items_pedido.total) as total')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['categoria' => $r->categoria, 'total' => (float) $r->total]);

        // --- Métodos de pago ---
        $totalParaPorcentaje = max($ventasTotales, 0.01);
        $metodosPago = (clone $base)
            ->selectRaw('COALESCE(metodo_pago, "otro") as metodo_pago, SUM(total) as total')
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'metodo' => $r->metodo_pago,
                'total' => (float) $r->total,
                'porcentaje' => round(($r->total / $totalParaPorcentaje) * 100),
            ]);

        // --- Actividad reciente ---
        $actividad = Pedido::where('estado', '!=', 'carrito')
            ->with('usuario:id,nombre,apodo')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($p) => [
                'texto' => "Pedido {$p->numero_pedido} de @{$p->usuario?->apodo}",
                'icon' => 'pi-shopping-cart',
                'fecha' => $p->created_at,
            ]);

        return Inertia::render('Admin/Shop/Index', [
            'stats' => [
                'pedidosTotales' => $pedidosTotales,
                'ventasTotales' => $ventasTotales,
                'pedidosCompletados' => $pedidosCompletados,
                'porcentajeCompletados' => $pedidosTotales ? round(($pedidosCompletados / $pedidosTotales) * 100) : 0,
                'ventasEsteMes' => $ventasEsteMes,
                'variacion' => $variacion,
            ],
            'pedidos' => $pedidos,
            'filtros' => $request->only(['q', 'estado', 'metodo']),
            'resumen' => [
                'periodo' => $periodo,
                'ventasTotales' => (float) (clone $base)->where('created_at', '>=', $inicioPeriodo)->sum('total'),
                'subtotal' => (float) (clone $base)->where('created_at', '>=', $inicioPeriodo)->sum('subtotal'),
                'envios' => (float) (clone $base)->where('created_at', '>=', $inicioPeriodo)->sum('envio'),
            ],
            'masVendidos' => $masVendidos,
            'ventasPorCategoria' => $ventasPorCategoria,
            'metodosPago' => $metodosPago,
            'actividadReciente' => $actividad,
        ]);
    }

    public function show(Pedido $pedido): Response
    {
        $pedido->load(['usuario', 'items.producto']);

        return Inertia::render('Admin/Shop/Show', [
            'pedido' => [
                'id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'estado' => $pedido->estado,
                'metodo_pago' => $pedido->metodo_pago,
                'pago_id' => $pedido->pago_id,
                'numero_seguimiento' => $pedido->numero_seguimiento,
                'direccion_envio' => $pedido->direccion_envio,
                'subtotal' => $pedido->subtotal,
                'envio' => $pedido->envio,
                'total' => $pedido->total,
                'created_at' => $pedido->created_at,
                'updated_at' => $pedido->updated_at,
                'usuario' => $pedido->usuario,
                'items' => $pedido->items->map(fn ($i) => [
                    'id' => $i->id,
                    'producto' => $i->producto ? [
                        'nombre' => $i->producto->nombre,
                        'sku' => $i->producto->sku,
                        'imagen' => $this->resolverUrl($i->producto->imagen_principal),
                    ] : ['nombre' => 'Producto eliminado', 'sku' => '—', 'imagen' => null],
                    'cantidad' => $i->cantidad,
                    'precio' => $i->precio,
                    'total' => $i->total,
                ]),
            ],
        ]);
    }

    public function actualizarEstado(Request $request, Pedido $pedido)
    {
        $request->validate(['estado' => ['required', 'in:pagado,enviado,entregado,cancelado']]);

        $pedido->update(['estado' => $request->estado]);

        return back()->with('success', "Pedido #{$pedido->numero_pedido} actualizado a \"{$pedido->estado_texto}\".");
    }

    public function exportar(Request $request)
    {
        $query = Pedido::with('usuario:id,nombre,apodo')->where('estado', '!=', 'carrito');

        if ($estado = $request->string('estado')->value()) {
            $query->where('estado', $estado);
        }

        $pedidos = $query->latest()->get();
        $filename = 'pedidos_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($pedidos) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Pedido', 'Usuario', 'Total', 'Estado', 'Fecha']);
            foreach ($pedidos as $p) {
                fputcsv($handle, [$p->numero_pedido, $p->usuario?->apodo, $p->total, $p->estado, $p->created_at->format('Y-m-d H:i')]);
            }
            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }

    /** Mismo criterio que Evento/Contenido/Producto: URL externa igual, ruta interna resuelta al disco público. */
    private function resolverUrl(?string $ruta): ?string
    {
        if (! $ruta) {
            return null;
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }

        return Storage::disk('public')->url($ruta);
    }
}