<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    private const CATEGORIAS = [
        'Lencería',
        'Juguetes sexuales',
        'Aceites y masajes',
        'Juegos para parejas',
        'Accesorios',
        'Bienestar íntimo',
    ];

    public function index(Request $request): Response
    {
        $total = Producto::count();
        $activos = Producto::where('esta_activo', true)->count();
        $sinStock = Producto::where('stock', '<=', 0)->count();
        $valorInventario = (float) (Producto::selectRaw('SUM(precio * stock) as total')->value('total') ?? 0);

        $query = Producto::query();

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($categoria = $request->string('categoria')->value()) {
            $query->where('categoria', $categoria);
        }

        if ($estado = $request->string('estado')->value()) {
            if ($estado === 'activo') {
                $query->where('esta_activo', true);
            } elseif ($estado === 'inactivo') {
                $query->where('esta_activo', false);
            } elseif ($estado === 'sin_stock') {
                $query->where('stock', '<=', 0);
            }
        }

        $productos = $query->latest()->paginate(5)->withQueryString();
        $productos->through(fn (Producto $p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'nombre' => $p->nombre,
            'categoria' => $p->categoria,
            'precio' => (float) $p->precio,
            'stock' => $p->stock,
            'esta_activo' => $p->esta_activo,
            'imagen' => $this->resolverUrl($p->imagenes[0] ?? null),
        ]);

        $porCategoria = collect(self::CATEGORIAS)->map(fn ($cat) => [
            'categoria' => $cat,
            'cantidad' => Producto::where('categoria', $cat)->count(),
        ]);

        return Inertia::render('Admin/Productos/Index', [
            'stats' => [
                'total' => $total,
                'activos' => $activos,
                'sinStock' => $sinStock,
                'valorInventario' => $valorInventario,
            ],
            'productos' => $productos,
            'filtros' => $request->only(['q', 'categoria', 'estado']),
            'categorias' => self::CATEGORIAS,
            'porCategoria' => $porCategoria,
        ]);
    }

    /**
     * Vista "Ver todos los productos": listado completo con más filtros
     * y desglose por categoría/estado — separado del dashboard de Productos.
     */
    public function todos(Request $request): Response
    {
        $query = Producto::query();

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        if ($categoria = $request->string('categoria')->value()) {
            $query->where('categoria', $categoria);
        }
        if ($estado = $request->string('estado')->value()) {
            if ($estado === 'activo') {
                $query->where('esta_activo', true);
            } elseif ($estado === 'inactivo') {
                $query->where('esta_activo', false);
            } elseif ($estado === 'sin_stock') {
                $query->where('stock', '<=', 0);
            }
        }

        $productos = $query->latest()->paginate(10)->withQueryString();
        $productos->through(fn (Producto $p) => [
            'id' => $p->id,
            'sku' => $p->sku,
            'nombre' => $p->nombre,
            'categoria' => $p->categoria,
            'marca' => $p->marca,
            'precio' => (float) $p->precio,
            'stock' => $p->stock,
            'esta_activo' => $p->esta_activo,
            'imagen' => $this->resolverUrl($p->imagenes[0] ?? null),
        ]);

        // --- Desglose por categoría ---
        $porCategoria = collect(self::CATEGORIAS)->map(fn ($cat) => [
            'categoria' => $cat,
            'label' => $cat,
            'cantidad' => Producto::where('categoria', $cat)->count(),
        ]);

        // --- Desglose por estado ---
        $porEstado = collect([
            ['estado' => 'activo', 'label' => 'Activos', 'cantidad' => Producto::where('esta_activo', true)->count()],
            ['estado' => 'inactivo', 'label' => 'Inactivos', 'cantidad' => Producto::where('esta_activo', false)->count()],
            ['estado' => 'sin_stock', 'label' => 'Sin stock', 'cantidad' => Producto::where('stock', '<=', 0)->count()],
        ]);

        return Inertia::render('Admin/Productos/Productos', [
            'productos' => $productos,
            'filtros' => $request->only(['q', 'categoria', 'estado']),
            'categorias' => self::CATEGORIAS,
            'porCategoria' => $porCategoria,
            'porEstado' => $porEstado,
            'totalGeneral' => Producto::count(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Productos/Create', [
            'categorias' => self::CATEGORIAS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'in:' . implode(',', self::CATEGORIAS)],
            'marca' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'esta_activo' => ['boolean'],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['string', 'max:50'],
            'variantes' => ['nullable', 'array'],
            'imagenes' => ['required', 'array', 'min:1'],
            'imagenes.*' => ['file', 'image', 'max:10240'],
        ]);

        $data['imagenes'] = $this->guardarImagenes($request->file('imagenes', []));

        $producto = Producto::create($data);

        return redirect()->route('admin.productos.index')->with('success', "Producto \"{$producto->nombre}\" creado correctamente.");
    }

    public function show(Producto $producto): Response
    {
        $data = $producto->toArray();
        $data['imagenes'] = $this->resolverImagenes($producto->imagenes);

        return Inertia::render('Admin/Productos/Show', [
            'producto' => $data,
        ]);
    }

    public function edit(Producto $producto): Response
    {
        $data = $producto->toArray();
        $data['imagenes'] = $this->resolverImagenes($producto->imagenes);

        return Inertia::render('Admin/Productos/Edit', [
            'producto' => $data,
            'categorias' => self::CATEGORIAS,
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:productos,sku,' . $producto->id],
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'categoria' => ['required', 'string', 'in:' . implode(',', self::CATEGORIAS)],
            'marca' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'esta_activo' => ['boolean'],
            'etiquetas' => ['nullable', 'array'],
            'etiquetas.*' => ['string', 'max:50'],
            'variantes' => ['nullable', 'array'],
            'imagenes_existentes' => ['nullable', 'array'],
            'imagenes_existentes.*' => ['string'],
            'imagenes_nuevas' => ['nullable', 'array'],
            'imagenes_nuevas.*' => ['file', 'image', 'max:10240'],
        ]);

        // Al menos una imagen entre las que se conservan + las nuevas
        $totalImagenes = count($data['imagenes_existentes'] ?? []) + count($request->file('imagenes_nuevas', []));
        if ($totalImagenes < 1) {
            return back()->withErrors(['imagenes' => 'Agrega al menos una imagen.'])->withInput();
        }

        $existentes = array_map(fn ($url) => $this->rutaOriginal($url), $data['imagenes_existentes'] ?? []);

        foreach ($producto->imagenes ?? [] as $rutaAnterior) {
            if (! in_array($rutaAnterior, $existentes, true)) {
                $this->borrarSiEsPropia($rutaAnterior);
            }
        }

        $nuevasRutas = $this->guardarImagenes($request->file('imagenes_nuevas', []));

        $data['imagenes'] = array_values(array_merge($existentes, $nuevasRutas));
        unset($data['imagenes_existentes'], $data['imagenes_nuevas']);

        $producto->update($data);

        return redirect()->route('admin.productos.index')->with('success', "Producto \"{$producto->nombre}\" actualizado correctamente.");
    }

    public function destroy(Producto $producto)
    {
        $nombre = $producto->nombre;

        foreach ($producto->imagenes ?? [] as $ruta) {
            $this->borrarSiEsPropia($ruta);
        }

        $producto->delete();

        return redirect()->route('admin.productos.index')->with('success', "Producto \"{$nombre}\" eliminado correctamente.");
    }

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

    private function resolverImagenes(?array $rutas): array
    {
        return array_values(array_filter(array_map(fn ($r) => $this->resolverUrl($r), $rutas ?? [])));
    }

    private function rutaOriginal(string $url): string
    {
        $marcador = '/storage/';
        $pos = strpos($url, $marcador);

        if ($pos !== false) {
            return substr($url, $pos + strlen($marcador));
        }

        return $url;
    }

    private function guardarImagenes(array $archivos): array
    {
        $rutas = [];
        foreach ($archivos as $archivo) {
            $rutas[] = $archivo->store('productos', 'public');
        }

        return $rutas;
    }

    private function borrarSiEsPropia(?string $ruta): void
    {
        if ($ruta && ! str_starts_with($ruta, 'http://') && ! str_starts_with($ruta, 'https://')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}