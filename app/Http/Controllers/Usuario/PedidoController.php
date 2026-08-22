<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\ItemPedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PedidoController extends Controller
{
    /**
     * Muestra la lista de pedidos del usuario
     */
    public function index(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver tus pedidos.');
        }

        // Obtener notificaciones, favoritos y mensajes
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        // Obtener pedidos del usuario
        $pedidos = Pedido::with(['items.producto'])
            ->where('usuario_id', $usuario->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Formatear pedidos para la vista
        $pedidosFormateados = $pedidos->map(function($pedido) {
            return $this->formatearPedido($pedido);
        });

        $usuarioData = $this->getUsuarioData($usuario);

        return Inertia::render('Usuario/MisPedidos', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'pedidos' => [
                'data' => $pedidosFormateados,
                'current_page' => $pedidos->currentPage(),
                'last_page' => $pedidos->lastPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
            ],
        ]);
    }

    /**
     * Muestra los detalles de un pedido específico
     */
    public function show($id)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver los detalles del pedido.');
        }

        $pedido = Pedido::with(['items.producto'])
            ->where('usuario_id', $usuario->id)
            ->findOrFail($id);

        $usuarioData = $this->getUsuarioData($usuario);
        $notificaciones = $this->getNotificaciones($usuario);
        $favoritos = $this->getFavoritos($usuario);
        $mensajes = $this->getMensajes($usuario);

        $pedidoFormateado = $this->formatearPedido($pedido);

        return Inertia::render('Usuario/PedidoDetalle', [
            'usuario' => $usuarioData,
            'notificaciones' => $notificaciones,
            'favoritos' => $favoritos,
            'mensajes' => $mensajes,
            'pedido' => $pedidoFormateado,
        ]);
    }

    /**
     * Cancela un pedido
     */
    public function cancelar($id)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['error' => 'Debes iniciar sesión.'], 401);
        }

        $pedido = Pedido::where('usuario_id', $usuario->id)
            ->whereIn('estado', ['pagado', 'carrito'])
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
                'message' => '✅ Pedido cancelado exitosamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => '❌ Error al cancelar el pedido: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtiene los pedidos del usuario (para API)
     */
    public function obtenerPedidos(Request $request)
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['error' => 'Debes iniciar sesión.'], 401);
        }

        $query = Pedido::with(['items.producto'])
            ->where('usuario_id', $usuario->id);

        // Filtro por estado
        if ($request->has('estado') && !empty($request->estado)) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento
        $orden = $request->get('orden', 'reciente');
        switch ($orden) {
            case 'antiguo':
                $query->orderBy('created_at', 'asc');
                break;
            case 'mayor_monto':
                $query->orderBy('total', 'desc');
                break;
            case 'menor_monto':
                $query->orderBy('total', 'asc');
                break;
            case 'reciente':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $pedidos = $query->paginate(10);

        return response()->json([
            'success' => true,
            'pedidos' => $pedidos->map(function($pedido) {
                return $this->formatearPedido($pedido);
            }),
            'paginacion' => [
                'current_page' => $pedidos->currentPage(),
                'last_page' => $pedidos->lastPage(),
                'per_page' => $pedidos->perPage(),
                'total' => $pedidos->total(),
            ],
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

    /**
     * Formatea un pedido para la vista
     */
    private function formatearPedido($pedido)
    {
        return [
            'id' => $pedido->id,
            'numero' => $pedido->numero_pedido,
            'subtotal' => (float)$pedido->subtotal,
            'envio' => (float)$pedido->envio,
            'total' => (float)$pedido->total,
            'estado' => $pedido->estado,
            'estado_texto' => $this->getEstadoTexto($pedido->estado),
            'color_estado' => $this->getColorEstado($pedido->estado),
            'metodo_pago' => $pedido->metodo_pago,
            'metodo_pago_texto' => $this->getMetodoPagoTexto($pedido->metodo_pago),
            'direccion_envio' => $pedido->direccion_envio,
            'items' => $pedido->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'nombre' => $item->producto->nombre ?? $item->metadatos['nombre'] ?? 'Producto',
                    'imagen' => $item->producto->imagen_principal ?? $item->metadatos['imagen'] ?? '/images/shared/placeholder.jpg',
                    'cantidad' => $item->cantidad,
                    'precio' => (float)$item->precio,
                    'total' => (float)$item->total,
                    'variante' => $item->variante,
                ];
            }),
            'created_at' => $pedido->created_at,
            'updated_at' => $pedido->updated_at,
        ];
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

    /**
     * Obtiene el texto del método de pago
     */
    private function getMetodoPagoTexto($metodo)
    {
        $metodos = [
            'tarjeta_credito' => 'Tarjeta de crédito',
            'tarjeta_debito' => 'Tarjeta de débito',
            'paypal' => 'PayPal',
            'oxxo' => 'OXXO',
            'transferencia' => 'Transferencia',
            'mercado_pago' => 'Mercado Pago',
        ];
        return $metodos[$metodo] ?? $metodo;
    }

    /**
     * Obtiene el texto del método de envío
     */
    private function getEnvioTexto($envio)
    {
        if ($envio == 0) {
            return 'Gratis';
        }
        return '$' . number_format($envio, 2) . ' MXN';
    }
}