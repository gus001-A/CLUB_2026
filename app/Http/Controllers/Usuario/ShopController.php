<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ShopController extends Controller
{
    /**
     * Muestra la página principal del Shop
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();

        // Obtener usuario con datos necesarios
        $usuarioData = $this->getUsuarioData($usuario);

        // Obtener notificaciones, favoritos y mensajes (ajusta según tu lógica)
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        // Obtener productos con filtros
        $productos = $this->getProductos($request);
        
        // Obtener recomendados para el usuario
        $recomendados = $this->getRecomendados($usuario);

        // Obtener colecciones destacadas
        $colecciones = $this->getColecciones();

        // Obtener categorías
        $categorias = $this->getCategorias();

        // Obtener marcas
        $marcas = $this->getMarcas();

        // Obtener carrito del usuario
        $carrito = $this->getCarrito($usuario);

        return Inertia::render('Usuario/Shop', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'productos' => $productos['items'],
            'totalProductos' => $productos['total'],
            'recomendados' => $recomendados,
            'colecciones' => $colecciones,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'carritoInicial' => $carrito,
        ]);
    }

    /**
     * Obtiene los datos del usuario formateados
     */
    private function getUsuarioData($usuario)
    {
        if (!$usuario) {
            return [
                'id' => null,
                'nombre' => 'Invitado',
                'avatar' => '/images/shared/avatar-default.jpg',
                'verificado' => false,
                'rol' => 'invitado'
            ];
        }

        // Obtener avatar
        $avatar = '/images/shared/avatar-default.jpg';
        if ($usuario->foto_principal) {
            if (str_starts_with($usuario->foto_principal, 'http://') || 
                str_starts_with($usuario->foto_principal, 'https://') || 
                str_starts_with($usuario->foto_principal, '/')) {
                $avatar = $usuario->foto_principal;
            } else {
                $avatar = '/storage/' . $usuario->foto_principal;
            }
        } elseif ($usuario->avatar && $usuario->avatar !== '/images/shared/avatar-default.jpg') {
            $avatar = $usuario->avatar;
        }

        return [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre ?? $usuario->apodo ?? 'Usuario',
            'avatar' => $avatar,
            'verificado' => $usuario->estado === 'verificado' || $usuario->email_verificado_en !== null,
            'rol' => $usuario->rol ?? 'usuario'
        ];
    }

    /**
     * Obtiene productos con filtros aplicados
     */
    private function getProductos(Request $request)
    {
        $query = Producto::activos();

        // Filtro por búsqueda
        if ($request->has('busqueda') && !empty($request->busqueda)) {
            $query->where(function($q) use ($request) {
                $q->where('nombre', 'LIKE', '%' . $request->busqueda . '%')
                  ->orWhere('descripcion', 'LIKE', '%' . $request->busqueda . '%');
            });
        }

        // Filtro por categoría
        if ($request->has('categoria') && !empty($request->categoria)) {
            $query->where('categoria', $request->categoria);
        }

        // Filtro por precio
        if ($request->has('precio_min') && $request->has('precio_max')) {
            $query->whereBetween('precio', [(float)$request->precio_min, (float)$request->precio_max]);
        }

        // Filtro por marcas
        if ($request->has('marcas') && !empty($request->marcas)) {
            $marcas = explode(',', $request->marcas);
            $query->whereIn('marca', $marcas);
        }

        // Filtro solo destacados
        if ($request->has('destacados') && $request->destacados) {
            $query->whereJsonContains('etiquetas', 'destacado');
        }

        // Ordenamiento
        $orden = $request->get('orden', 'relevantes');
        switch ($orden) {
            case 'precio_asc':
                $query->orderBy('precio', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            case 'calificacion':
                $query->orderBy('calificacion', 'desc');
                break;
            case 'relevantes':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Paginación
        $perPage = $request->get('per_page', 8);
        $productos = $query->paginate($perPage);

        // Formatear productos para la vista
        $items = $productos->map(function($producto) {
            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => (float)$producto->precio,
                'imagen' => $producto->imagen_principal ?? '/images/shared/placeholder.jpg',
                'rating' => (float)($producto->calificacion ?? 0),
                'resenas' => $producto->itemsPedido()->count() ?? 0,
                'badge' => $this->getBadge($producto),
                'categoria' => $producto->categoria,
                'marca' => $producto->marca,
                'stock' => $producto->stock,
                'estaEnStock' => $producto->esta_en_stock,
            ];
        });

        return [
            'items' => $items,
            'total' => $productos->total(),
        ];
    }

    /**
     * Obtiene el badge para un producto
     */
    private function getBadge($producto)
    {
        if (!$producto->etiquetas) return null;
        
        $etiquetas = is_array($producto->etiquetas) ? $producto->etiquetas : json_decode($producto->etiquetas, true);
        
        if (in_array('top_ventas', $etiquetas)) {
            return 'TOP VENTAS';
        }
        if (in_array('nuevo', $etiquetas)) {
            return 'NUEVO';
        }
        if (in_array('exclusivo', $etiquetas)) {
            return 'EXCLUSIVO';
        }
        if (in_array('oferta', $etiquetas)) {
            return 'OFERTA';
        }
        
        return null;
    }

    /**
     * Obtiene productos recomendados para el usuario
     */
    private function getRecomendados($usuario)
    {
        // Si no hay usuario, mostrar productos populares
        $query = Producto::activos()
            ->orderBy('calificacion', 'desc')
            ->limit(5);

        return $query->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => (float)$producto->precio,
                    'imagen' => $producto->imagen_principal ?? '/images/shared/placeholder.jpg',
                ];
            })
            ->toArray();
    }

    /**
     * Obtiene colecciones destacadas
     */
    private function getColecciones()
    {
        // Si tienes un modelo Coleccion, úsalo
        // Por ahora, devolver colecciones estáticas de ejemplo
        return [
            [
                'titulo' => 'Encanto y seducción',
                'desc' => 'Lencería que despierta la imaginación.',
                'imagen' => '/images/shop-listado/col-encanto.jpg'
            ],
            [
                'titulo' => 'Conexión y complicidad',
                'desc' => 'Productos para explorar juntos y crear momentos inolvidables.',
                'imagen' => '/images/shop-listado/col-conexion.jpg'
            ],
            [
                'titulo' => 'Bienestar íntimo',
                'desc' => 'Cuida tu cuerpo y mente con productos de bienestar premium.',
                'imagen' => '/images/shop-listado/col-bienestar.jpg'
            ]
        ];
    }

    /**
     * Obtiene categorías
     */
    private function getCategorias()
    {
        // Obtener categorías únicas de productos existentes
        $categorias = Producto::activos()
            ->select('categoria')
            ->distinct()
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->pluck('categoria');

        if ($categorias->isEmpty()) {
            return [
                ['label' => 'Lencería', 'icon' => 'pi-heart'],
                ['label' => 'Juguetes sexuales', 'icon' => 'pi-circle'],
                ['label' => 'Aceites y masajes', 'icon' => 'pi-droplet'],
                ['label' => 'Juegos para parejas', 'icon' => 'pi-th-large'],
                ['label' => 'Accesorios', 'icon' => 'pi-circle'],
                ['label' => 'Bienestar íntimo', 'icon' => 'pi-heart'],
            ];
        }

        return $categorias->map(function($cat) {
            return [
                'label' => $cat,
                'icon' => $this->getIconForCategoria($cat)
            ];
        })->values()->toArray();
    }

    /**
     * Obtiene el icono para una categoría
     */
    private function getIconForCategoria($categoria)
    {
        $mapa = [
            'lenceria' => 'pi-heart',
            'lencería' => 'pi-heart',
            'juguetes sexuales' => 'pi-circle',
            'juguetes' => 'pi-circle',
            'aceites y masajes' => 'pi-droplet',
            'aceites' => 'pi-droplet',
            'masajes' => 'pi-droplet',
            'juegos para parejas' => 'pi-th-large',
            'juegos' => 'pi-th-large',
            'accesorios' => 'pi-circle',
            'bienestar intimo' => 'pi-heart',
            'bienestar' => 'pi-heart',
        ];

        $lower = strtolower(trim($categoria));
        return $mapa[$lower] ?? 'pi-tag';
    }

    /**
     * Obtiene marcas
     */
    private function getMarcas()
    {
        $marcas = Producto::activos()
            ->select('marca')
            ->distinct()
            ->whereNotNull('marca')
            ->where('marca', '!=', '')
            ->pluck('marca');

        if ($marcas->isEmpty()) {
            return [
                ['label' => 'Todos', 'checked' => true],
                ['label' => 'Bijoux Indiscrets', 'checked' => false],
                ['label' => 'LELO', 'checked' => false],
                ['label' => 'We-Vibe', 'checked' => false],
                ['label' => 'Fifty Shades of Grey', 'checked' => false],
                ['label' => 'Amorelie', 'checked' => false],
            ];
        }

        $result = [['label' => 'Todos', 'checked' => true]];
        foreach ($marcas as $marca) {
            $result[] = ['label' => $marca, 'checked' => false];
        }

        return $result;
    }

    /**
     * Obtiene el carrito del usuario
     */
    private function getCarrito($usuario)
    {
        if (!$usuario) {
            return [];
        }

        // Si tienes un modelo Carrito, úsalo
        // Por ahora, devolver carrito vacío
        return [];
    }

    /**
     * Obtiene notificaciones del usuario
     */
    private function getNotificaciones($usuario)
    {
        if (!$usuario) return 0;
        // TODO: Implementar lógica real de notificaciones
        return 0;
    }

    /**
     * Obtiene favoritos del usuario
     */
    private function getFavoritos($usuario)
    {
        if (!$usuario) return 0;
        // TODO: Implementar lógica real de favoritos
        return 0;
    }

    /**
     * Obtiene mensajes del usuario
     */
    private function getMensajes($usuario)
    {
        if (!$usuario) return 0;
        // TODO: Implementar lógica real de mensajes
        return 0;
    }

    /**
     * Filtra productos (endpoint para AJAX)
     */
    public function filtrar(Request $request)
    {
        $productos = $this->getProductos($request);
        
        return response()->json([
            'productos' => $productos['items'],
            'totalProductos' => $productos['total']
        ]);
    }

    /**
     * Obtiene detalles de un producto
     */
    public function show($id)
    {
        $producto = Producto::activos()->findOrFail($id);

        return Inertia::render('Usuario/ProductDetail', [
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => (float)$producto->precio,
                'imagenes' => $producto->imagenes ?? [$producto->imagen_principal],
                'rating' => (float)($producto->calificacion ?? 0),
                'resenas' => $producto->itemsPedido()->count() ?? 0,
                'categoria' => $producto->categoria,
                'marca' => $producto->marca,
                'stock' => $producto->stock,
                'estaEnStock' => $producto->esta_en_stock,
                'variantes' => $producto->variantes_formateadas,
                'etiquetas' => $producto->etiquetas,
                'calificacionTexto' => $producto->calificacion_texto,
            ]
        ]);
    }
}