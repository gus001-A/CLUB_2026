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

        $contenidos = $query->latest()->paginate(7)->withQueryString();
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

        return Inertia::render('Admin/Contenido/Index', [
            'stats' => [
                'total' => $total,
                'nuevosEsteMes' => Contenido::where('created_at', '>=', now()->startOfMonth())->count(),
                'publicados' => $publicados,
                'borradores' => $borradores,
                'archivados' => $archivados,
            ],
            'contenidos' => $contenidos,
            'filtros' => $request->only(['q', 'tipo', 'estado']),
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
                'vistasPorDia' => Interaccion::where('tipo', 'vista')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')
                    ->groupBy('fecha')->orderBy('fecha')->get()
                    ->map(fn ($r) => ['fecha' => $r->fecha, 'total' => (int) $r->total]),
                'vistasTotales' => Interaccion::where('tipo', 'vista')->count(),
                'usuariosUnicos' => Interaccion::where('tipo', 'vista')->distinct('usuario_id')->count('usuario_id'),
                'interaccionesTotales' => Interaccion::whereIn('tipo', ['like', 'comentario', 'compartir'])->count(),
            ],
        ]);
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
            'url_archivo' => ['nullable', 'string', 'max:2048'],
        ]);

        $archivos = $data['url_archivo'] ? [$data['url_archivo']] : [];
        unset($data['url_archivo']);

        $contenido = Contenido::create($data + [
            'creador_id' => null,
            'archivos' => $archivos,
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
}