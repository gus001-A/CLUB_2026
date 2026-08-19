<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReporteExport;
use App\Http\Controllers\Controller;
use App\Models\Creador;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Reporte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteController extends Controller
{
    /**
     * Catálogo de reportes disponibles. Para agregar uno nuevo: 1) una
     * entrada aquí (para la tarjeta del hub), 2) un case en datosReporte().
     * Nada más se toca — el detalle, el PDF y el Excel ya funcionan solos.
     */
    private const CATALOGO = [
        'ventas-periodo' => [
            'titulo' => 'Ventas por Periodo',
            'descripcion' => 'Consulta las ventas por día, semana, mes o año.',
            'icono' => 'pi-chart-line',
            'usaPeriodo' => true,
        ],
        'ventas-usuario' => [
            'titulo' => 'Ventas por Usuario',
            'descripcion' => 'Quién ha comprado más en la tienda.',
            'icono' => 'pi-user',
            'usaPeriodo' => true,
        ],
        'productos-mas-vendidos' => [
            'titulo' => 'Productos Más Vendidos',
            'descripcion' => 'Ranking de productos por unidades vendidas.',
            'icono' => 'pi-chart-bar',
            'usaPeriodo' => true,
        ],
        'valor-inventario' => [
            'titulo' => 'Valor del Inventario',
            'descripcion' => 'Total invertido en stock actual, por producto y categoría.',
            'icono' => 'pi-wallet',
            'usaPeriodo' => false,
        ],
        'bajo-stock' => [
            'titulo' => 'Bajo Stock',
            'descripcion' => 'Productos con 10 unidades o menos.',
            'icono' => 'pi-exclamation-triangle',
            'usaPeriodo' => false,
        ],
        'admins-reportes-atendidos' => [
            'titulo' => 'Admins Más Activos en Moderación',
            'descripcion' => 'Cuántos reportes ha resuelto cada admin (desde que se activó este conteo).',
            'icono' => 'pi-shield',
            'usaPeriodo' => false,
        ],
        'creadores-populares' => [
            'titulo' => 'Creadores Más Populares',
            'descripcion' => 'Ranking de creadores por suscriptores activos.',
            'icono' => 'pi-star',
            'usaPeriodo' => false,
        ],
    ];

    public function index(): Response
    {
        return Inertia::render('Admin/Reportes/Index', [
            'reportes' => collect(self::CATALOGO)->map(fn ($r, $tipo) => [...$r, 'tipo' => $tipo])->values(),
        ]);
    }

    public function detalle(Request $request, string $tipo): Response
    {
        abort_unless(isset(self::CATALOGO[$tipo]), 404);

        return Inertia::render('Admin/Reportes/Detalle', $this->datosReporte($tipo, $request) + [
            'catalogo' => self::CATALOGO[$tipo] + ['tipo' => $tipo],
        ]);
    }

    public function exportarPdf(Request $request, string $tipo)
    {
        abort_unless(isset(self::CATALOGO[$tipo]), 404);

        $datos = $this->datosReporte($tipo, $request);
        $pdf = Pdf::loadView('reportes.pdf-generico', $datos)->setPaper('letter', 'portrait');

        return $pdf->download($this->nombreArchivo($tipo, 'pdf'));
    }

    public function exportarExcel(Request $request, string $tipo): StreamedResponse
    {
        abort_unless(isset(self::CATALOGO[$tipo]), 404);

        $datos = $this->datosReporte($tipo, $request);
        $spreadsheet = (new ReporteExport($datos))->build();

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true); // sin esto, la gráfica no se guarda en el archivo

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $this->nombreArchivo($tipo, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * El corazón de todo: por cada tipo de reporte regresa la misma forma
     * de datos (título, columnas, filas, resumen, y opcionalmente gráfica),
     * sin importar si el destino es la pantalla, el PDF o el Excel.
     */
    private function datosReporte(string $tipo, Request $request): array
    {
        $usaPeriodo = self::CATALOGO[$tipo]['usaPeriodo'];
        $periodo = $usaPeriodo ? $request->string('periodo', 'mes')->value() : null;
        [$inicio, $fin, $granularidad, $periodoLabel] = $usaPeriodo ? $this->rangoPeriodo($periodo) : [null, null, null, null];

        $base = [
            'tipo' => $tipo,
            'titulo' => self::CATALOGO[$tipo]['titulo'],
            'periodo' => $periodo,
            'periodoLabel' => $periodoLabel,
            'generadoEn' => now()->format('d-m-Y H:i'),
            'chart' => null,
        ];

        return match ($tipo) {
            'ventas-periodo' => $this->reporteVentasPeriodo($base, $inicio, $fin, $granularidad),
            'ventas-usuario' => $this->reporteVentasUsuario($base, $inicio, $fin),
            'productos-mas-vendidos' => $this->reporteProductosMasVendidos($base, $inicio, $fin),
            'valor-inventario' => $this->reporteValorInventario($base),
            'bajo-stock' => $this->reporteBajoStock($base),
            'admins-reportes-atendidos' => $this->reporteAdminsAtendidos($base),
            'creadores-populares' => $this->reporteCreadoresPopulares($base),
        };
    }

    private function reporteVentasPeriodo(array $base, $inicio, $fin, string $granularidad): array
    {
        $pedidosBase = Pedido::where('estado', '!=', 'carrito')->whereBetween('created_at', [$inicio, $fin]);

        $totalVentas = (float) (clone $pedidosBase)->sum('total');
        $totalPedidos = (clone $pedidosBase)->count();

        $porFecha = (clone $pedidosBase)
            ->selectRaw($granularidad === 'mes' ? "DATE_FORMAT(created_at, '%Y-%m-01') as fecha, COUNT(*) as pedidos, SUM(total) as total" : 'DATE(created_at) as fecha, COUNT(*) as pedidos, SUM(total) as total')
            ->groupBy('fecha')
            ->get()
            ->keyBy('fecha');

        $filas = [];
        $chartLabels = [];
        $chartData = [];
        $diasSinVentas = 0;
        $cursor = $granularidad === 'mes' ? $inicio->copy()->startOfMonth() : $inicio->copy();
        while ($cursor->lte($fin)) {
            $clave = $granularidad === 'mes' ? $cursor->format('Y-m-01') : $cursor->format('Y-m-d');
            $fila = $porFecha->get($clave);
            $etiqueta = $granularidad === 'mes' ? $cursor->format('M Y') : $cursor->format('d M');
            $totalDia = (float) ($fila->total ?? 0);

            // La gráfica conserva todos los días (para que la tendencia se
            // vea completa), pero la tabla de abajo solo muestra los días
            // que sí tuvieron ventas — si no, queda una lista larga de puros
            // $0.00 que no aporta nada y hace el PDF innecesariamente largo.
            if ($totalDia > 0) {
                $filas[] = [$etiqueta, (int) ($fila->pedidos ?? 0), '$' . number_format($totalDia, 2)];
            } else {
                $diasSinVentas++;
            }

            $chartLabels[] = $etiqueta;
            $chartData[] = $totalDia;
            $granularidad === 'mes' ? $cursor->addMonth() : $cursor->addDay();
        }

        return [
            'columnas' => ['Fecha', 'Pedidos', 'Total'],
            'filas' => $filas,
            'resumen' => [
                ['label' => 'Total de pedidos', 'valor' => $totalPedidos],
                ['label' => 'Total vendido', 'valor' => '$' . number_format($totalVentas, 2)],
                ['label' => $granularidad === 'mes' ? 'Meses sin ventas' : 'Días sin ventas', 'valor' => $diasSinVentas],
            ],
            'chart' => ['labels' => $chartLabels, 'data' => $chartData],
        ] + $base;
    }

    private function reporteVentasUsuario(array $base, $inicio, $fin): array
    {
        $filas = Pedido::where('estado', '!=', 'carrito')
            ->whereBetween('created_at', [$inicio, $fin])
            ->selectRaw('usuario_id, COUNT(*) as pedidos, SUM(total) as total')
            ->groupBy('usuario_id')
            ->orderByDesc('total')
            ->with('usuario:id,nombre,apodo')
            ->take(50)
            ->get()
            ->map(fn ($r) => [
                $r->usuario ? "{$r->usuario->nombre} (@{$r->usuario->apodo})" : 'Usuario eliminado',
                (int) $r->pedidos,
                '$' . number_format((float) $r->total, 2),
            ])
            ->all();

        return [
            'columnas' => ['Usuario', 'Pedidos', 'Total gastado'],
            'filas' => $filas,
            'resumen' => [
                ['label' => 'Usuarios distintos', 'valor' => count($filas)],
            ],
        ] + $base;
    }

    private function reporteProductosMasVendidos(array $base, $inicio, $fin): array
    {
        $filas = ItemPedido::whereHas('pedido', fn ($q) => $q->where('estado', '!=', 'carrito')->whereBetween('created_at', [$inicio, $fin]))
            ->selectRaw('producto_id, SUM(cantidad) as unidades, SUM(total) as ingresos')
            ->groupBy('producto_id')
            ->orderByDesc('unidades')
            ->with('producto:id,nombre,categoria')
            ->take(50)
            ->get()
            ->map(fn ($r) => [
                $r->producto?->nombre ?? 'Producto eliminado',
                $r->producto?->categoria ?? '—',
                (int) $r->unidades,
                '$' . number_format((float) $r->ingresos, 2),
            ])
            ->all();

        return [
            'columnas' => ['Producto', 'Categoría', 'Unidades vendidas', 'Ingresos'],
            'filas' => $filas,
            'resumen' => [
                ['label' => 'Productos distintos vendidos', 'valor' => count($filas)],
            ],
        ] + $base;
    }

    private function reporteValorInventario(array $base): array
    {
        $productos = Producto::orderByDesc('stock')->get(['nombre', 'categoria', 'precio', 'stock']);

        $filas = $productos->map(fn ($p) => [
            $p->nombre,
            $p->categoria,
            '$' . number_format((float) $p->precio, 2),
            $p->stock,
            '$' . number_format((float) $p->precio * $p->stock, 2),
        ])->all();

        $valorTotal = $productos->sum(fn ($p) => (float) $p->precio * $p->stock);

        $porCategoria = $productos->groupBy('categoria')->map(fn ($grupo, $cat) => [
            'label' => $cat ?: 'Sin categoría',
            'valor' => '$' . number_format($grupo->sum(fn ($p) => (float) $p->precio * $p->stock), 2),
        ])->values()->all();

        return [
            'columnas' => ['Producto', 'Categoría', 'Precio', 'Stock', 'Valor'],
            'filas' => $filas,
            'resumen' => array_merge(
                [['label' => 'Valor total del inventario', 'valor' => '$' . number_format($valorTotal, 2)]],
                $porCategoria
            ),
        ] + $base;
    }

    private function reporteBajoStock(array $base): array
    {
        $productos = Producto::where('stock', '<=', 10)
            ->orderBy('stock')
            ->get(['nombre', 'categoria', 'stock', 'esta_activo']);

        $filas = $productos->map(fn ($p) => [
            $p->nombre,
            $p->categoria,
            $p->stock <= 0 ? 'Agotado' : $p->stock,
            $p->esta_activo ? 'Activo' : 'Inactivo',
        ])->all();

        return [
            'columnas' => ['Producto', 'Categoría', 'Stock', 'Estado'],
            'filas' => $filas,
            'resumen' => [
                ['label' => 'Productos con bajo stock', 'valor' => $productos->count()],
                ['label' => 'Productos agotados', 'valor' => $productos->where('stock', '<=', 0)->count()],
            ],
        ] + $base;
    }

    private function reporteAdminsAtendidos(array $base): array
    {
        $filas = Reporte::whereNotNull('atendido_por_admin_id')
            ->selectRaw('atendido_por_admin_id, COUNT(*) as cantidad')
            ->groupBy('atendido_por_admin_id')
            ->orderByDesc('cantidad')
            ->with('atendidoPor:id,nombre')
            ->get()
            ->map(fn ($r) => [
                $r->atendidoPor?->nombre ?? 'Admin eliminado',
                (int) $r->cantidad,
            ])
            ->all();

        return [
            'columnas' => ['Administrador', 'Reportes atendidos'],
            'filas' => $filas,
            'resumen' => [
                ['label' => 'Admins con al menos un reporte atendido', 'valor' => count($filas)],
                ['label' => 'Total de reportes atendidos', 'valor' => array_sum(array_column($filas, 1))],
            ],
        ] + $base;
    }

    private function reporteCreadoresPopulares(array $base): array
    {
        $creadores = Creador::with('usuario:id,nombre,apodo')
            ->withCount(['suscripciones as suscriptores_activos' => fn ($q) => $q->where('estado', 'activa')])
            ->having('suscriptores_activos', '>', 0)
            ->orderByDesc('suscriptores_activos')
            ->take(50)
            ->get();

        $filas = $creadores->map(fn ($c) => [
            $c->usuario ? "{$c->usuario->nombre} (@{$c->usuario->apodo})" : 'Usuario eliminado',
            $c->suscriptores_activos,
            $c->esta_verificado ? 'Sí' : 'No',
            $c->es_premium ? 'Sí' : 'No',
        ])->all();

        return [
            'columnas' => ['Creador', 'Suscriptores activos', 'Verificado', 'Premium'],
            'filas' => $filas,
            'resumen' => [
                ['label' => 'Creadores con al menos 1 suscriptor', 'valor' => $creadores->where('suscriptores_activos', '>', 0)->count()],
                ['label' => 'Total de suscriptores activos', 'valor' => $creadores->sum('suscriptores_activos')],
            ],
        ] + $base;
    }

    /** Mismo patrón que ya usan Eventos/Contenido para el selector de periodo. */
    private function rangoPeriodo(string $periodo): array
    {
        return match ($periodo) {
            'dia' => [now()->startOfDay(), now()->endOfDay(), 'dia', 'Hoy'],
            'semana' => [now()->startOfWeek(), now()->endOfDay(), 'dia', 'Esta semana'],
            'anio' => [now()->subMonths(11)->startOfMonth(), now()->endOfDay(), 'mes', 'Este año'],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay(), 'dia', 'Este mes'],
        };
    }

    private function nombreArchivo(string $tipo, string $ext): string
    {
        return str_replace('-', '_', $tipo) . '_' . now()->format('Y-m-d_His') . '.' . $ext;
    }
}