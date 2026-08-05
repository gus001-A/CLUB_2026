<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\Interaccion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContenidoController extends Controller
{
    public function index(Request $request): Response
    {
        $total = Contenido::count();
        $publicados = Contenido::where('estado', 'publicado')->count();
        $borradores = Contenido::where('estado', 'borrador')->count();
        $archivados = Contenido::where('estado', 'archivado')->count();

        $query = Contenido::query();

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                    ->orWhere('categoria', 'like', "%{$search}%");
            });
        }

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('tipo', $tipo);
        }

        if ($estado = $request->string('estado')->value()) {
            $query->where('estado', $estado);
        }

        $contenidos = $query->latest()->paginate(5)->withQueryString();
        $contenidos->through(fn ($c) => [
            'id' => $c->id,
            'titulo' => $c->titulo ?: 'Sin título',
            'tipo' => $c->tipo,
            'categoria' => $c->categoria,
            'estado' => $c->estado,
            'created_at' => $c->created_at,
            'vistas' => $c->interacciones()->where('tipo', 'vista')->count(),
            'imagen' => $c->archivos[0] ?? null,
        ]);

        $tiposContenido = Contenido::selectRaw('tipo, COUNT(*) as cantidad')->groupBy('tipo')->pluck('cantidad', 'tipo');

        // --- Estadísticas de Contenido (obedece el selector de periodo) ---
        $periodoStats = $request->string('periodo_stats', 'mes')->value();
        [$inicioStats, $finStats, $granularidad] = $this->rangoPeriodo($periodoStats);

        $datosReales = Interaccion::where('tipo', 'vista')
            ->whereBetween('created_at', [$inicioStats, $finStats])
            ->selectRaw($granularidad === 'mes' ? "DATE_FORMAT(created_at, '%Y-%m-01') as fecha, COUNT(*) as total" : 'DATE(created_at) as fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $vistasPorDia = collect();
        $cursor = $granularidad === 'mes' ? $inicioStats->copy()->startOfMonth() : $inicioStats->copy();
        while ($cursor->lte($finStats)) {
            $clave = $granularidad === 'mes' ? $cursor->format('Y-m-01') : $cursor->format('Y-m-d');
            $vistasPorDia->push(['fecha' => $clave, 'total' => (int) ($datosReales[$clave] ?? 0)]);
            $granularidad === 'mes' ? $cursor->addMonth() : $cursor->addDay();
        }

        return Inertia::render('Admin/Contenido/Index', [
            'stats' => [
                'total' => $total,
                'nuevosEsteMes' => Contenido::where('created_at', '>=', now()->startOfMonth())->count(),
                'publicados' => $publicados,
                'borradores' => $borradores,
                'archivados' => $archivados,
            ],
            'contenidos' => $contenidos,
            'filtros' => $request->only(['q', 'tipo', 'estado', 'periodo_stats']),
            'tiposContenido' => [
                'video' => $tiposContenido['video'] ?? 0,
                'articulo' => $tiposContenido['articulo'] ?? 0,
                'galeria' => $tiposContenido['galeria'] ?? 0,
                'audio' => $tiposContenido['audio'] ?? 0,
                'documento' => $tiposContenido['documento'] ?? 0,
            ],
            'contenidoReciente' => Contenido::latest()->take(4)->get()->map(fn ($c) => [
                'id' => $c->id,
                'titulo' => $c->titulo ?: 'Sin título',
                'tipo' => $c->tipo,
                'estado' => $c->estado,
                'created_at' => $c->created_at,
                'imagen' => $c->archivos[0] ?? null,
            ]),
            'estadisticas' => [
                'vistasPorDia' => $vistasPorDia,
                'vistasTotales' => Interaccion::where('tipo', 'vista')->whereBetween('created_at', [$inicioStats, $finStats])->count(),
                'usuariosUnicos' => Interaccion::where('tipo', 'vista')->whereBetween('created_at', [$inicioStats, $finStats])->distinct('usuario_id')->count('usuario_id'),
                'interaccionesTotales' => Interaccion::whereIn('tipo', ['like', 'comentario', 'compartir'])->whereBetween('created_at', [$inicioStats, $finStats])->count(),
            ],
        ]);
    }

    /**
     * Devuelve [inicio, fin, granularidad] según el periodo elegido en el
     * selector de "Estadísticas de Contenido" (mismo patrón que en Cobros).
     */
    private function rangoPeriodo(string $periodo): array
    {
        return match ($periodo) {
            'semana' => [now()->subDays(6)->startOfDay(), now()->endOfDay(), 'dia'],
            'anio' => [now()->subMonths(11)->startOfMonth(), now()->endOfDay(), 'mes'],
            default => [now()->subDays(29)->startOfDay(), now()->endOfDay(), 'dia'], // 'mes'
        };
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Contenido/Create', [
            'tipoPreseleccionado' => $request->query('tipo'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'in:foto,video,galeria,audio,articulo,documento,exclusivo'],
            'visibilidad' => ['required', 'in:publico,suscriptores,individual'],
            'estado' => ['required', 'in:borrador,publicado,programado,archivado'],
            'precio' => ['required', 'numeric', 'min:0'],
            'es_premium' => ['boolean'],
            'archivos' => [
                'required', 'array',
                function ($attribute, $value, $fail) use ($request) {
                    $minimo = $request->input('tipo') === 'galeria' ? 3 : 1;
                    if (count($value) < $minimo) {
                        $fail($minimo === 3 ? 'Una galería debe tener al menos 3 fotos.' : 'Agrega al menos un archivo.');
                    }
                },
            ],
            'archivos.*' => ['string', 'max:2048'],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['string', 'max:50'],
            'programado_en' => ['nullable', 'required_if:estado,programado', 'date'],
        ]);

        $contenido = Contenido::create($data + [
            'creador_id' => auth('admin')->id(),
        ]);

        return redirect()->route('admin.contenido.index')->with('success', "Contenido \"{$contenido->titulo}\" creado correctamente.");
    }

    public function show(Contenido $contenido): Response
    {
        $contenido->vistas = $contenido->interacciones()->where('tipo', 'vista')->count();
        $contenido->likes = $contenido->interacciones()->where('tipo', 'like')->count();
        $contenido->comentarios = $contenido->interacciones()->where('tipo', 'comentario')->count();

        return Inertia::render('Admin/Contenido/Show', [
            'contenido' => $contenido,
        ]);
    }

    public function edit(Contenido $contenido): Response
    {
        return Inertia::render('Admin/Contenido/Edit', [
            'contenido' => $contenido,
        ]);
    }

    public function update(Request $request, Contenido $contenido)
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['required', 'in:foto,video,galeria,audio,articulo,documento,exclusivo'],
            'visibilidad' => ['required', 'in:publico,suscriptores,individual'],
            'estado' => ['required', 'in:borrador,publicado,programado,archivado'],
            'precio' => ['required', 'numeric', 'min:0'],
            'es_premium' => ['boolean'],
            'archivos' => [
                'required', 'array',
                function ($attribute, $value, $fail) use ($request) {
                    $minimo = $request->input('tipo') === 'galeria' ? 3 : 1;
                    if (count($value) < $minimo) {
                        $fail($minimo === 3 ? 'Una galería debe tener al menos 3 fotos.' : 'Agrega al menos un archivo.');
                    }
                },
            ],
            'archivos.*' => ['string', 'max:2048'],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['string', 'max:50'],
            'programado_en' => ['nullable', 'required_if:estado,programado', 'date'],
        ]);

        $contenido->update($data);

        return redirect()->route('admin.contenido.index')->with('success', "Contenido \"{$contenido->titulo}\" actualizado correctamente.");
    }

    public function destroy(Contenido $contenido)
    {
        $titulo = $contenido->titulo ?: 'Sin título';
        $contenido->delete();

        return back()->with('success', "Contenido \"{$titulo}\" eliminado correctamente.");
    }
}