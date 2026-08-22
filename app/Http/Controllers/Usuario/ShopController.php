<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Pedido;
use App\Models\ItemPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // Obtener notificaciones, favoritos y mensajes
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

        // Obtener carrito del usuario (desde sesión o base de datos)
        $carrito = $this->getCarrito($usuario);

        // Obtener métricas
        $metricas = $this->getMetricas();

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
            'metricas' => $metricas,
        ]);
    }

    /**
     * Muestra la página del carrito
     */
    public function carrito(Request $request)
    {
        $usuario = Auth::user();
        $usuarioData = $this->getUsuarioData($usuario);
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        // Obtener carrito de la sesión
        $carrito = $request->session()->get('carrito', []);

        return Inertia::render('Usuario/Carrito', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'carrito' => $carrito,
            'config' => [
                'envio_gratis_desde' => 500,
                'costo_envio' => 150,
            ],
        ]);
    }

    /**
     * Sincroniza el carrito del frontend con la sesión
     */
    public function sincronizarCarrito(Request $request)
    {
        $carrito = $request->input('carrito', []);

        $request->session()->put('carrito', $carrito);

        return response()->json([
            'success' => true,
            'message' => empty($carrito) ? 'Carrito vaciado' : 'Carrito sincronizado',
        ]);
    }

    /**
     * Muestra la página de checkout
     */
    public function checkout(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para realizar un pedido.');
        }

        // Obtener carrito de la sesión
        $carrito = $request->session()->get('carrito', []);
        
        // Si el carrito está vacío, redirigir a la tienda
        if (empty($carrito)) {
            return redirect()->route('tienda')->with('error', 'Tu carrito está vacío.');
        }

        // Obtener direcciones del usuario (si existe el método)
        $direcciones = [];
        if (method_exists($usuario, 'direcciones')) {
            $direcciones = $usuario->direcciones()->get() ?? [];
        }

        $usuarioData = $this->getUsuarioData($usuario);
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        return Inertia::render('Usuario/Checkout', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'carrito' => $carrito,
            'direcciones' => $direcciones,
            'config' => [
                'envio_gratis_desde' => 500,
                'costo_envio' => 150,
                'metodos_pago' => ['tarjeta_credito', 'tarjeta_debito', 'paypal', 'oxxo'],
            ],
        ]);
    }

    /**
     * Confirma el pedido y crea la orden - CORREGIDO PARA INERTIA
     */
    public function confirmarPedido(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para realizar un pedido.');
        }

        // Validar datos con mensajes en español
        $validated = $request->validate([
            'productos' => 'required|array|min:1',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio' => 'required|numeric|min:0',
            'direccion' => 'required|array',
            'direccion.destinatario' => 'required|string|max:255',
            'direccion.telefono' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
            'direccion.calle' => 'required|string|max:255',
            'direccion.colonia' => 'required|string|max:255',
            'direccion.ciudad' => 'required|string|max:255',
            'direccion.estado' => 'required|string|max:255',
            'direccion.codigo_postal' => 'required|string|max:10|regex:/^[0-9]+$/',
            'direccion.referencias' => 'nullable|string|max:500',
            'metodo_pago' => 'required|string|in:tarjeta_credito,tarjeta_debito,paypal,oxxo',
            'subtotal' => 'required|numeric|min:0',
            'costo_envio' => 'required|numeric|min:0',
            'descuento' => 'numeric|min:0',
            'total' => 'required|numeric|min:0',
        ], [
            // Mensajes de error en español
            'productos.required' => 'Debes agregar al menos un producto al pedido.',
            'productos.*.id.required' => 'El ID del producto es obligatorio.',
            'productos.*.id.exists' => 'El producto seleccionado no existe en nuestra base de datos.',
            'productos.*.cantidad.required' => 'La cantidad del producto es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad mínima por producto es 1.',
            'productos.*.precio.required' => 'El precio del producto es obligatorio.',
            'productos.*.precio.numeric' => 'El precio debe ser un valor numérico.',
            'productos.*.precio.min' => 'El precio no puede ser negativo.',
            
            'direccion.required' => 'La dirección de envío es obligatoria.',
            'direccion.destinatario.required' => 'El nombre del destinatario es obligatorio.',
            'direccion.telefono.required' => 'El teléfono de contacto es obligatorio.',
            'direccion.telefono.regex' => 'El teléfono solo debe contener números, guiones y espacios.',
            'direccion.calle.required' => 'La calle y número son obligatorios.',
            'direccion.colonia.required' => 'La colonia es obligatoria.',
            'direccion.ciudad.required' => 'La ciudad es obligatoria.',
            'direccion.estado.required' => 'El estado es obligatorio.',
            'direccion.codigo_postal.required' => 'El código postal es obligatorio.',
            'direccion.codigo_postal.regex' => 'El código postal solo debe contener números.',
            
            'metodo_pago.required' => 'Debes seleccionar un método de pago.',
            'metodo_pago.in' => 'El método de pago seleccionado no es válido.',
            
            'subtotal.required' => 'El subtotal es obligatorio.',
            'subtotal.numeric' => 'El subtotal debe ser un valor numérico.',
            'subtotal.min' => 'El subtotal no puede ser negativo.',
            
            'costo_envio.required' => 'El costo de envío es obligatorio.',
            'costo_envio.numeric' => 'El costo de envío debe ser un valor numérico.',
            'costo_envio.min' => 'El costo de envío no puede ser negativo.',
            
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un valor numérico.',
            'total.min' => 'El total no puede ser negativo.',
        ]);

        // 🔥 Agregar país por defecto
        $validated['direccion']['pais'] = 'México';

        // Datos de tarjeta (solo si el método es tarjeta_credito o tarjeta_debito)
        if ($request->metodo_pago === 'tarjeta_credito' || $request->metodo_pago === 'tarjeta_debito') {
            $request->validate([
                'datos_tarjeta' => 'required|array',
                'datos_tarjeta.numero' => 'required|string|min:16|max:19',
                'datos_tarjeta.nombre' => 'required|string|max:255',
                'datos_tarjeta.expiracion' => 'required|string|max:7',
                'datos_tarjeta.cvv' => 'required|string|min:3|max:4',
            ], [
                'datos_tarjeta.required' => 'Los datos de la tarjeta son obligatorios.',
                'datos_tarjeta.numero.required' => 'El número de tarjeta es obligatorio.',
                'datos_tarjeta.numero.min' => 'El número de tarjeta debe tener al menos 16 dígitos.',
                'datos_tarjeta.numero.max' => 'El número de tarjeta no puede tener más de 19 dígitos.',
                'datos_tarjeta.nombre.required' => 'El nombre en la tarjeta es obligatorio.',
                'datos_tarjeta.expiracion.required' => 'La fecha de expiración es obligatoria.',
                'datos_tarjeta.cvv.required' => 'El código de seguridad CVV es obligatorio.',
                'datos_tarjeta.cvv.min' => 'El CVV debe tener al menos 3 dígitos.',
                'datos_tarjeta.cvv.max' => 'El CVV no puede tener más de 4 dígitos.',
            ]);
        }

        try {
            DB::beginTransaction();

            // Generar número de pedido único
            $numeroPedido = $this->generarNumeroPedido();

            // Mapear método de pago al enum de la base de datos
            $metodoPagoEnum = $this->mapearMetodoPago($request->metodo_pago);

            // Crear el pedido
            $pedido = Pedido::create([
                'usuario_id' => $usuario->id,
                'numero_pedido' => $numeroPedido,
                'subtotal' => $request->subtotal,
                'envio' => $request->costo_envio,
                'total' => $request->total,
                'estado' => 'pagado',
                'metodo_pago' => $metodoPagoEnum,
                'direccion_envio' => $validated['direccion'],
                'metadatos' => [
                    'descuento' => $request->descuento ?? 0,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            // Crear items del pedido
            foreach ($request->productos as $item) {
                // Verificar stock
                $producto = Producto::find($item['id']);
                if (!$producto || $producto->stock < $item['cantidad']) {
                    throw new \Exception("No hay suficiente stock para el producto: {$producto->nombre}");
                }

                // Descontar stock
                $producto->stock -= $item['cantidad'];
                $producto->save();

                // Crear item
                ItemPedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $item['id'],
                    'variante' => $item['variante'] ?? null,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio'],
                    'total' => $item['precio'] * $item['cantidad'],
                    'metadatos' => [
                        'nombre' => $item['nombre'] ?? $producto->nombre,
                        'imagen' => $item['imagen'] ?? $producto->imagen_principal,
                    ],
                ]);
            }

            // Guardar dirección como dirección principal si existe el modelo
            $this->guardarDireccionUsuario($usuario, $validated['direccion']);

            // Limpiar carrito de la sesión
            $request->session()->forget('carrito');

            DB::commit();

            // 🔥 REDIRIGIR A LA PÁGINA DE ÉXITO - ESTO ES LO QUE ESPERA INERTIA
            return redirect()->route('tienda.pedido.exito', $pedido->id)
                ->with('success', '✅ ¡Pedido confirmado exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            // 🔥 REDIRIGIR DE VUELTA AL CHECKOUT CON ERROR
            return redirect()->back()
                ->with('error', '❌ Error al procesar el pedido: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mapea el método de pago del frontend al enum de la base de datos
     */
    private function mapearMetodoPago($metodoFrontend)
    {
        $mapa = [
            'tarjeta' => 'tarjeta_credito',
            'tarjeta_credito' => 'tarjeta_credito',
            'tarjeta_debito' => 'tarjeta_debito',
            'paypal' => 'paypal',
            'oxxo' => 'oxxo',
            'mercado_pago' => 'paypal', // Fallback si llega mercado_pago
            'transferencia' => 'paypal', // Fallback si llega transferencia
        ];

        return $mapa[$metodoFrontend] ?? 'paypal';
    }

    /**
     * Muestra la página de éxito del pedido
     */
    public function pedidoExito($id)
    {
        $usuario = Auth::user();
        
        if (!$usuario) {
            return redirect()->route('login');
        }

        $pedido = Pedido::with(['items.producto'])
            ->where('usuario_id', $usuario->id)
            ->findOrFail($id);

        $usuarioData = $this->getUsuarioData($usuario);
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        return Inertia::render('Usuario/PedidoExito', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'pedido' => [
                'id' => $pedido->id,
                'numero' => $pedido->numero_pedido,
                'subtotal' => $pedido->subtotal,
                'envio' => $pedido->envio,
                'total' => $pedido->total,
                'estado' => $pedido->estado,
                'estado_texto' => $this->getEstadoTexto($pedido->estado),
                'metodo_pago' => $pedido->metodo_pago,
                'direccion_envio' => $pedido->direccion_envio,
                'items' => $pedido->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->producto->nombre ?? $item->metadatos['nombre'] ?? 'Producto',
                        'imagen' => $item->producto->imagen_principal ?? $item->metadatos['imagen'] ?? '/images/shared/placeholder.jpg',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'total' => $item->total,
                        'variante' => $item->variante,
                    ];
                }),
                'created_at' => $pedido->created_at,
            ],
        ]);
    }

    /**
     * Obtiene los pedidos del usuario
     */
    public function misPedidos()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        $pedidos = Pedido::with(['items.producto'])
            ->where('usuario_id', $usuario->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $usuarioData = $this->getUsuarioData($usuario);
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        return Inertia::render('Usuario/MisPedidos', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'pedidos' => $pedidos,
        ]);
    }

    /**
     * Obtiene los detalles de un pedido específico
     */
    public function pedidoDetalle($id)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        $pedido = Pedido::with(['items.producto'])
            ->where('usuario_id', $usuario->id)
            ->findOrFail($id);

        $usuarioData = $this->getUsuarioData($usuario);
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        return Inertia::render('Usuario/PedidoDetalle', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'pedido' => [
                'id' => $pedido->id,
                'numero' => $pedido->numero_pedido,
                'subtotal' => $pedido->subtotal,
                'envio' => $pedido->envio,
                'total' => $pedido->total,
                'estado' => $pedido->estado,
                'estado_texto' => $this->getEstadoTexto($pedido->estado),
                'color_estado' => $this->getColorEstado($pedido->estado),
                'metodo_pago' => $pedido->metodo_pago,
                'direccion_envio' => $pedido->direccion_envio,
                'items' => $pedido->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'nombre' => $item->producto->nombre ?? $item->metadatos['nombre'] ?? 'Producto',
                        'imagen' => $item->producto->imagen_principal ?? $item->metadatos['imagen'] ?? '/images/shared/placeholder.jpg',
                        'cantidad' => $item->cantidad,
                        'precio' => $item->precio,
                        'total' => $item->total,
                        'variante' => $item->variante,
                    ];
                }),
                'created_at' => $pedido->created_at,
                'updated_at' => $pedido->updated_at,
            ],
        ]);
    }

    /**
     * Cancela un pedido
     */
    public function cancelarPedido($id)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['error' => 'Debes iniciar sesión.'], 401);
        }

        $pedido = Pedido::where('usuario_id', $usuario->id)
            ->whereIn('estado', ['carrito', 'pagado'])
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            // Devolver stock
            foreach ($pedido->items as $item) {
                $producto = Producto::find($item->producto_id);
                if ($producto) {
                    $producto->stock += $item->cantidad;
                    $producto->save();
                }
            }

            $pedido->estado = 'cancelado';
            $pedido->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado exitosamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al cancelar el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene detalles de un producto
     */
    public function show($id)
    {
        $usuario = Auth::user();
        $producto = Producto::activos()->findOrFail($id);

        // Obtener imágenes del producto
        $imagenes = [];
        if ($producto->imagen_principal) {
            $imagenes[] = $producto->imagen_principal;
        }
        if ($producto->imagenes) {
            $imagenesExtra = is_array($producto->imagenes) ? $producto->imagenes : json_decode($producto->imagenes, true);
            $imagenes = array_merge($imagenes, $imagenesExtra);
        }
        if (empty($imagenes)) {
            $imagenes = ['/images/shared/placeholder.jpg'];
        }

        // Obtener variantes/colores
        $colores = $this->getColoresProducto($producto);
        $tallas = $this->getTallasProducto($producto);

        // Obtener productos relacionados
        $relacionados = $this->getProductosRelacionados($producto);

        // Obtener métricas
        $metricas = $this->getMetricas();

        // Datos del usuario
        $usuarioData = $this->getUsuarioData($usuario);
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        // Calcular descuento
        $precioOriginal = $this->getPrecioOriginal($producto);
        $descuento = $precioOriginal ? round((($precioOriginal - $producto->precio) / $precioOriginal) * 100) : 0;

        return Inertia::render('Usuario/Producto', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'metricas' => $metricas,
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'descripcionCorta' => $this->getDescripcionCorta($producto),
                'precio' => (float)$producto->precio,
                'precioOriginal' => $precioOriginal,
                'descuento' => $descuento,
                'moneda' => 'MXN',
                'imagenes' => $imagenes,
                'rating' => (float)($producto->calificacion ?? 0),
                'resenas' => $producto->itemsPedido()->count() ?? 0,
                'categoria' => $producto->categoria,
                'marca' => $producto->marca,
                'stock' => $producto->stock,
                'enStock' => $producto->esta_en_stock,
                'etiqueta' => $this->getBadge($producto),
                'colores' => $colores,
                'tallas' => $tallas,
                'caracteristicas' => $this->getCaracteristicas($producto),
                'destacados' => $this->getDestacadosProducto($producto),
            ],
            'relacionados' => $relacionados,
        ]);
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

    // ===================================================================
    // MÉTODOS PRIVADOS
    // ===================================================================

    /**
     * Obtiene los datos del usuario formateados
     */
    private function getUsuarioData($usuario)
    {
        if (!$usuario) {
            return [
                'id' => null,
                'nombre' => 'Invitado',
                'email' => '',
                'telefono' => '',
                'avatar' => '/images/shared/avatar-default.jpg',
                'verificado' => false,
                'rol' => 'invitado'
            ];
        }

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
            'email' => $usuario->email ?? '',
            'telefono' => $usuario->telefono ?? '',
            'avatar' => $avatar,
            'verificado' => $usuario->estado === 'verificado' || $usuario->email_verificado_en !== null,
            'rol' => $usuario->rol ?? 'usuario'
        ];
    }

    /**
     * Obtiene métricas para mostrar en el shop
     */
    private function getMetricas()
    {
        $totalProductos = Producto::activos()->count();

        return [
            [
                'icon' => 'pi-box',
                'titulo' => 'Productos',
                'valor' => number_format($totalProductos),
                'etiqueta' => 'disponibles',
                'color' => '#2563EB'
            ],
            [
                'icon' => 'pi-truck',
                'titulo' => 'Envíos',
                'valor' => '24h',
                'etiqueta' => 'entrega exprés',
                'color' => '#10B981'
            ],
            [
                'icon' => 'pi-shield',
                'titulo' => 'Compra discreta',
                'valor' => '100%',
                'etiqueta' => 'privacidad garantizada',
                'color' => '#8B5CF6'
            ],
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
            $search = $request->busqueda;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', '%' . $search . '%')
                  ->orWhere('descripcion', 'LIKE', '%' . $search . '%')
                  ->orWhere('categoria', 'LIKE', '%' . $search . '%');
            });
        }

        // Filtro por categoría
        if ($request->has('categoria') && !empty($request->categoria)) {
            $query->where('categoria', $request->categoria);
        }

        // Filtro por precio
        $precioMin = $request->has('precio_min') ? (float)$request->precio_min : 200;
        $precioMax = $request->has('precio_max') ? (float)$request->precio_max : 5000;
        $query->whereBetween('precio', [$precioMin, $precioMax]);

        // Filtro por marcas
        if ($request->has('marcas') && !empty($request->marcas)) {
            $marcas = explode(',', $request->marcas);
            $query->whereIn('marca', $marcas);
        }

        // Filtro solo destacados (usando etiquetas)
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
        $perPage = $request->get('per_page', 12);
        $productos = $query->paginate($perPage);

        // Formatear productos para la vista
        $items = $productos->map(function($producto) {
            $badge = $this->getBadge($producto);
            $precioOriginal = $this->getPrecioOriginal($producto);

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => (float)$producto->precio,
                'precioOriginal' => $precioOriginal,
                'descuento' => $precioOriginal ? round((($precioOriginal - $producto->precio) / $precioOriginal) * 100) : 0,
                'imagen' => $producto->imagen_principal ?? '/images/shared/placeholder.jpg',
                'rating' => (float)($producto->calificacion ?? 0),
                'resenas' => $producto->itemsPedido()->count() ?? 0,
                'badge' => $badge,
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
        if (in_array('envio_gratis', $etiquetas)) {
            return 'ENVÍO GRATIS';
        }
        if (in_array('destacado', $etiquetas)) {
            return 'DESTACADO';
        }
        
        return null;
    }

    /**
     * Obtiene el precio original para mostrar descuento
     */
    private function getPrecioOriginal($producto)
    {
        if (isset($producto->precio_original) && $producto->precio_original > $producto->precio) {
            return (float)$producto->precio_original;
        }

        $etiquetas = is_array($producto->etiquetas) ? $producto->etiquetas : json_decode($producto->etiquetas, true);
        if (in_array('oferta', $etiquetas)) {
            $descuento = 0.15 + (rand(0, 20) / 100);
            return round($producto->precio / (1 - $descuento), 2);
        }

        return null;
    }

    /**
     * Obtiene productos recomendados para el usuario
     */
    private function getRecomendados($usuario)
    {
        if ($usuario) {
            $categoriasCompradas = $usuario->pedidos()
                ->whereHas('items', function($q) {
                    $q->whereHas('producto');
                })
                ->get()
                ->flatMap(function($pedido) {
                    return $pedido->items->pluck('producto.categoria');
                })
                ->filter()
                ->unique()
                ->take(3)
                ->toArray();

            if (!empty($categoriasCompradas)) {
                $query = Producto::activos()
                    ->whereIn('categoria', $categoriasCompradas)
                    ->orderBy('calificacion', 'desc')
                    ->limit(5);
                
                return $query->get()
                    ->map(function($producto) {
                        return [
                            'id' => $producto->id,
                            'nombre' => $producto->nombre,
                            'precio' => (float)$producto->precio,
                            'imagen' => $producto->imagen_principal ?? '/images/shared/placeholder.jpg',
                            'rating' => (float)($producto->calificacion ?? 0),
                            'resenas' => $producto->itemsPedido()->count() ?? 0,
                        ];
                    })
                    ->toArray();
            }
        }

        $query = Producto::activos()
            ->orderBy('calificacion', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5);

        return $query->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => (float)$producto->precio,
                    'imagen' => $producto->imagen_principal ?? '/images/shared/placeholder.jpg',
                    'rating' => (float)($producto->calificacion ?? 0),
                    'resenas' => $producto->itemsPedido()->count() ?? 0,
                ];
            })
            ->toArray();
    }

    /**
     * Obtiene colecciones destacadas
     */
    private function getColecciones()
    {
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
        $categorias = Producto::activos()
            ->select('categoria')
            ->distinct()
            ->whereNotNull('categoria')
            ->where('categoria', '!=', '')
            ->pluck('categoria');

        if ($categorias->isEmpty()) {
            return [
                ['label' => 'Lencería', 'icon' => 'pi-heart'],
                ['label' => 'Juguetes sexuales', 'icon' => 'pi-circle-fill'],
                ['label' => 'Aceites y masajes', 'icon' => 'pi-droplet'],
                ['label' => 'Juegos para parejas', 'icon' => 'pi-users'],
                ['label' => 'Accesorios', 'icon' => 'pi-cog'],
                ['label' => 'Bienestar íntimo', 'icon' => 'pi-heart-fill'],
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
            'juguetes sexuales' => 'pi-circle-fill',
            'juguetes' => 'pi-circle-fill',
            'aceites y masajes' => 'pi-droplet',
            'aceites' => 'pi-droplet',
            'masajes' => 'pi-droplet',
            'juegos para parejas' => 'pi-users',
            'juegos' => 'pi-users',
            'accesorios' => 'pi-cog',
            'bienestar intimo' => 'pi-heart-fill',
            'bienestar' => 'pi-heart-fill',
            'ropa interior' => 'pi-user',
            'ropa' => 'pi-user',
            'vibradores' => 'pi-circle-fill',
            'consoladores' => 'pi-circle-fill',
            'anillos' => 'pi-circle',
            'lubricantes' => 'pi-droplet',
            'velas' => 'pi-star',
            'aromaterapia' => 'pi-star',
            'libros' => 'pi-book',
            'juegos de cartas' => 'pi-book',
            'kits' => 'pi-box',
            'regalos' => 'pi-gift',
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

        // Intentar obtener carrito de la sesión
        $carrito = session()->get('carrito', []);
        
        if (!empty($carrito)) {
            return $carrito;
        }

        return [];
    }

    /**
     * Obtiene notificaciones del usuario
     */
    private function getNotificaciones($usuario)
    {
        if (!$usuario) return 0;
        return 0;
    }

    /**
     * Obtiene favoritos del usuario
     */
    private function getFavoritos($usuario)
    {
        if (!$usuario) return 0;
        return 0;
    }

    /**
     * Obtiene mensajes del usuario
     */
    private function getMensajes($usuario)
    {
        if (!$usuario) return 0;
        return 0;
    }

    // ===================================================================
    // MÉTODOS PARA PRODUCTO DETALLE
    // ===================================================================

    /**
     * Obtiene descripción corta del producto
     */
    private function getDescripcionCorta($producto)
    {
        if ($producto->descripcion_corta) {
            return $producto->descripcion_corta;
        }

        $descripcion = strip_tags($producto->descripcion);
        if (strlen($descripcion) > 150) {
            return substr($descripcion, 0, 150) . '...';
        }
        return $descripcion;
    }

    /**
     * Obtiene colores disponibles del producto
     */
    private function getColoresProducto($producto)
    {
        if ($producto->variantes_formateadas && isset($producto->variantes_formateadas['colores'])) {
            return $producto->variantes_formateadas['colores'];
        }

        return [
            ['nombre' => 'Rojo', 'valor' => '#c81e3a'],
            ['nombre' => 'Negro', 'valor' => '#1a1a1a'],
            ['nombre' => 'Rosa palo', 'valor' => '#f2d9d9'],
        ];
    }

    /**
     * Obtiene tallas disponibles del producto
     */
    private function getTallasProducto($producto)
    {
        if ($producto->variantes_formateadas && isset($producto->variantes_formateadas['tallas'])) {
            return $producto->variantes_formateadas['tallas'];
        }

        return ['S', 'M', 'L', 'XL'];
    }

    /**
     * Obtiene características del producto
     */
    private function getCaracteristicas($producto)
    {
        if ($producto->caracteristicas) {
            $caracteristicas = is_array($producto->caracteristicas) ? 
                $producto->caracteristicas : 
                json_decode($producto->caracteristicas, true);
            return $caracteristicas;
        }

        return [
            ['etiqueta' => 'Incluye', 'valor' => 'Brasier con varilla + Panty a juego'],
            ['etiqueta' => 'Material', 'valor' => 'Encaje 90% poliamida, 10% elastano'],
            ['etiqueta' => 'Color', 'valor' => 'Rojo intenso'],
            ['etiqueta' => 'Ideal para', 'valor' => 'Cenas románticas, aniversarios, ocasiones especiales'],
        ];
    }

    /**
     * Obtiene destacados del producto
     */
    private function getDestacadosProducto($producto)
    {
        if ($producto->destacados) {
            $destacados = is_array($producto->destacados) ? 
                $producto->destacados : 
                json_decode($producto->destacados, true);
            return $destacados;
        }

        return [
            ['icon' => 'pi-heart', 'titulo' => 'Comodidad y ajuste perfecto', 'desc' => 'Tirantes ajustables y cierre de múltiples posiciones para un mejor soporte.'],
            ['icon' => 'pi-sparkles', 'titulo' => 'Diseño que enamora', 'desc' => 'Detalles en encaje floral que realzan tu figura con delicadeza.'],
            ['icon' => 'pi-eye', 'titulo' => 'Sensualidad en cada detalle', 'desc' => 'Transparencias estratégicas que despiertan la imaginación.'],
        ];
    }

    /**
     * Obtiene productos relacionados
     */
    private function getProductosRelacionados($producto)
    {
        $query = Producto::activos()
            ->where('id', '!=', $producto->id)
            ->where(function($q) use ($producto) {
                if ($producto->categoria) {
                    $q->where('categoria', $producto->categoria);
                }
                if ($producto->marca) {
                    $q->orWhere('marca', $producto->marca);
                }
            })
            ->limit(4);

        $productos = $query->get();

        if ($productos->isEmpty()) {
            $productos = Producto::activos()
                ->where('id', '!=', $producto->id)
                ->orderBy('calificacion', 'desc')
                ->limit(4)
                ->get();
        }

        return $productos->map(function($p) {
            $precioOriginal = $this->getPrecioOriginal($p);
            return [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'imagen' => $p->imagen_principal ?? '/images/shared/placeholder.jpg',
                'rating' => (float)($p->calificacion ?? 0),
                'resenas' => $p->itemsPedido()->count() ?? 0,
                'precio' => (float)$p->precio,
                'precioOriginal' => $precioOriginal,
                'descuento' => $precioOriginal ? round((($precioOriginal - $p->precio) / $precioOriginal) * 100) : 0,
                'categoria' => $p->categoria,
            ];
        })->toArray();
    }

    // ===================================================================
    // MÉTODOS PARA PEDIDOS
    // ===================================================================

    /**
     * Genera un número de pedido único
     */
    private function generarNumeroPedido()
    {
        $prefijo = 'CF-';
        $ano = date('Y');
        $mes = date('m');
        $dia = date('d');
        
        $ultimo = Pedido::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimo) {
            $partes = explode('-', $ultimo->numero_pedido);
            $secuencia = intval(end($partes)) + 1;
        } else {
            $secuencia = 1;
        }

        return $prefijo . $ano . $mes . $dia . '-' . str_pad($secuencia, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Guarda una dirección en el perfil del usuario
     */
    private function guardarDireccionUsuario($usuario, $direccion)
    {
        if (method_exists($usuario, 'direcciones')) {
            $existe = $usuario->direcciones()
                ->where('calle', $direccion['calle'])
                ->where('colonia', $direccion['colonia'])
                ->where('ciudad', $direccion['ciudad'])
                ->where('estado', $direccion['estado'])
                ->where('codigo_postal', $direccion['codigo_postal'])
                ->exists();

            if (!$existe) {
                $usuario->direcciones()->create([
                    'nombre' => $direccion['destinatario'] . ' - ' . $direccion['calle'],
                    'destinatario' => $direccion['destinatario'],
                    'calle' => $direccion['calle'],
                    'colonia' => $direccion['colonia'],
                    'ciudad' => $direccion['ciudad'],
                    'estado' => $direccion['estado'],
                    'codigo_postal' => $direccion['codigo_postal'],
                    'pais' => $direccion['pais'] ?? 'México',
                    'telefono' => $direccion['telefono'] ?? '',
                    'referencias' => $direccion['referencias'] ?? '',
                    'principal' => $usuario->direcciones()->count() === 0,
                ]);
            }
        }
    }

    /**
     * Obtiene el texto del estado
     */
    private function getEstadoTexto($estado)
    {
        $estados = [
            'carrito' => 'Carrito',
            'pagado' => 'Pagado',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];
        return $estados[$estado] ?? $estado;
    }

    /**
     * Obtiene el color del estado
     */
    private function getColorEstado($estado)
    {
        $colores = [
            'carrito' => 'gray',
            'pagado' => 'blue',
            'enviado' => 'orange',
            'entregado' => 'green',
            'cancelado' => 'red',
        ];
        return $colores[$estado] ?? 'gray';
    }
}