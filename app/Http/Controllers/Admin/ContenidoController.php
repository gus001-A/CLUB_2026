<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contenido;
use App\Models\Interaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ContenidoController extends Controller
{
    public function index(Request $request): Response
    {
        // Salvavidas: si un contenido "programado" ya pasó su fecha/hora y
        // nadie lo ha visto, lo publicamos aquí. Esto NO sustituye un
        // scheduler real — solo cubre cuando un admin entra al panel. Si el
        // sitio público depende de que esto pase puntual sin que nadie abra
        // el admin, hace falta un comando con schedule:everyMinute() en
        // routes/console.php (o el Kernel, según tu versión de Laravel).
        $this->promoverProgramados();

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

        $contenidos = $query->withCount(['interacciones as vistas' => fn ($q) => $q->where('tipo', 'vista')])
            ->latest()->paginate(5)->withQueryString();
        $contenidos->through(fn ($c) => [
            'id' => $c->id,
            'titulo' => $c->titulo ?: 'Sin título',
            'tipo' => $c->tipo,
            'categoria' => $c->categoria,
            'estado' => $c->estado,
            'created_at' => $c->created_at,
            'vistas' => $c->vistas,
            'imagen' => $this->resolverUrl($c->archivos[0] ?? null),
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
                'foto' => $tiposContenido['foto'] ?? 0,
                'video' => $tiposContenido['video'] ?? 0,
                'galeria' => $tiposContenido['galeria'] ?? 0,
                'audio' => $tiposContenido['audio'] ?? 0,
                'articulo' => $tiposContenido['articulo'] ?? 0,
                'documento' => $tiposContenido['documento'] ?? 0,
                'exclusivo' => $tiposContenido['exclusivo'] ?? 0,
            ],
            'contenidoReciente' => Contenido::latest()->take(4)->get()->map(fn ($c) => [
                'id' => $c->id,
                'titulo' => $c->titulo ?: 'Sin título',
                'tipo' => $c->tipo,
                'estado' => $c->estado,
                'created_at' => $c->created_at,
                'imagen' => $this->resolverUrl($c->archivos[0] ?? null),
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

    /** Publica el contenido programado cuya fecha/hora ya se cumplió. */
    private function promoverProgramados(): void
    {
        Contenido::where('estado', 'programado')
            ->where('programado_en', '<=', now())
            ->update(['estado' => 'publicado']);
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
            'archivos.*' => [
                'file', 'max:1048576',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm,avi,mp3,wav,ogg,m4a,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z',
            ],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['string', 'max:50'],
            'programado_en' => ['nullable', 'required_if:estado,programado', 'date'],
        ]);

        $data['archivos'] = $this->guardarArchivos($request->file('archivos', []));

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

        $data = $contenido->toArray();
        $data['archivos'] = $this->resolverArchivos($contenido->archivos);

        return Inertia::render('Admin/Contenido/Show', [
            'contenido' => $data,
        ]);
    }

    public function edit(Contenido $contenido): Response
    {
        $data = $contenido->toArray();
        $data['archivos'] = $this->resolverArchivos($contenido->archivos);

        return Inertia::render('Admin/Contenido/Edit', [
            'contenido' => $data,
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
            'archivos_existentes' => [
                'array',
                function ($attribute, $value, $fail) use ($request) {
                    $totalNuevos = count($request->file('archivos_nuevos', []));
                    $minimo = $request->input('tipo') === 'galeria' ? 3 : 1;
                    if (count($value) + $totalNuevos < $minimo) {
                        $fail($minimo === 3 ? 'Una galería debe tener al menos 3 fotos.' : 'Agrega al menos un archivo.');
                    }
                },
            ],
            'archivos_existentes.*' => ['string'],
            'archivos_nuevos' => ['nullable', 'array'],
            'archivos_nuevos.*' => [
                'file', 'max:1048576',
                'mimes:jpg,jpeg,png,gif,webp,mp4,mov,webm,avi,mp3,wav,ogg,m4a,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar,7z',
            ],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['string', 'max:50'],
            'programado_en' => ['nullable', 'required_if:estado,programado', 'date'],
        ]);

        // Rutas internas que el admin decidió conservar (convertidas de vuelta
        // desde la URL pública que le mandamos al formulario)
        $existentes = array_map(fn ($url) => $this->rutaOriginal($url), $data['archivos_existentes'] ?? []);

        // Borra del disco los archivos que ya no están entre los conservados
        foreach ($contenido->archivos ?? [] as $rutaAnterior) {
            if (! in_array($rutaAnterior, $existentes, true)) {
                $this->borrarSiEsPropio($rutaAnterior);
            }
        }

        $nuevasRutas = $this->guardarArchivos($request->file('archivos_nuevos', []));

        $data['archivos'] = array_values(array_merge($existentes, $nuevasRutas));
        unset($data['archivos_existentes'], $data['archivos_nuevos']);

        $contenido->update($data);

        return redirect()->route('admin.contenido.index')->with('success', "Contenido \"{$contenido->titulo}\" actualizado correctamente.");
    }

    public function destroy(Contenido $contenido)
    {
        $titulo = $contenido->titulo ?: 'Sin título';

        foreach ($contenido->archivos ?? [] as $ruta) {
            $this->borrarSiEsPropio($ruta);
        }

        $contenido->delete();

        return redirect()->route('admin.contenido.index')->with('success', "Contenido \"{$titulo}\" eliminado correctamente.");
    }

    /**
     * Convierte una ruta guardada en storage (ej. "contenido/archivo.jpg") en
     * su URL pública. Si ya es una URL externa (http/https), la deja igual
     * — mismo criterio que ya usan en Evento.imagen.
     */
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

    /** Resuelve un array completo de rutas a sus URLs públicas. */
    private function resolverArchivos(?array $rutas): array
    {
        return array_values(array_filter(array_map(fn ($r) => $this->resolverUrl($r), $rutas ?? [])));
    }

    /**
     * Camino inverso: dada la URL pública que manda el frontend (la que
     * generó resolverUrl), regresa la ruta interna guardable en la BD.
     * Si es una URL externa que no pasa por /storage/, se deja tal cual.
     */
    private function rutaOriginal(string $url): string
    {
        $marcador = '/storage/';
        $pos = strpos($url, $marcador);

        if ($pos !== false) {
            return substr($url, $pos + strlen($marcador));
        }

        return $url;
    }

    /** Sube los archivos nuevos recibidos y devuelve las rutas guardadas. */
    private function guardarArchivos(array $archivos): array
    {
        $rutas = [];
        foreach ($archivos as $archivo) {
            $rutas[] = $archivo->store('contenido', 'public');
        }

        return $rutas;
    }

    /** Borra del disco solo si es una ruta interna (no una URL externa). */
    private function borrarSiEsPropio(?string $ruta): void
    {
        if ($ruta && ! str_starts_with($ruta, 'http://') && ! str_starts_with($ruta, 'https://')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}