<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Creador;
use App\Models\User;
use App\Models\Contenido;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PerfilCreadorPublicoController extends Controller
{
    /**
     * GET /creador/{id}/{slug}
     * Muestra el perfil público de un creador
     */
    public function show($id, $slug = null)
    {
        // Buscar el creador
        $creador = Creador::with(['usuario'])
            ->where('id', $id)
            ->first();

        if (!$creador) {
            abort(404, 'Creador no encontrado');
        }

        // Verificar que el creador esté verificado (opcional, puedes quitarlo)
        if (!$creador->esta_verificado) {
            abort(404, 'Creador no disponible');
        }

        // Usuario autenticado
        $usuario = Auth::user();

        // Verificar si el usuario está suscrito a este creador
        $estaSuscrito = false;
        $suscripcionActiva = null;

        if ($usuario) {
            $suscripcion = Suscripcion::where('usuario_id', $usuario->id)
                ->where('creador_id', $creador->id)
                ->where('estado', 'activa')
                ->first();

            $estaSuscrito = $suscripcion !== null;
            $suscripcionActiva = $suscripcion;
        }

        // Obtener contenidos del creador (solo los publicados)
        $contenidosQuery = Contenido::where('creador_id', $creador->id)
            ->where('estado', 'publicado')
            ->with(['likes'])
            ->withCount(['likes as total_likes', 'comentarios as total_comentarios']);

        // Si el usuario NO está suscrito, mostrar solo contenido gratuito
        if (!$estaSuscrito) {
            $contenidosQuery->where('es_premium', false);
        }

        $contenidos = $contenidosQuery->orderBy('created_at', 'desc')
            ->paginate(12);

        // ✅ CORREGIDO: Pasar $estaSuscrito en el use del map
        $contenidosFormateados = $contenidos->map(function ($contenido) use ($usuario, $estaSuscrito) {
            return [
                'id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'descripcion' => $contenido->descripcion,
                'texto' => $contenido->texto,
                'es_premium' => $contenido->es_premium,
                'imagen' => $contenido->imagen_url ?? null,
                'archivos' => $contenido->archivos ?? [],
                'total_likes' => $contenido->total_likes ?? 0,
                'total_comentarios' => $contenido->total_comentarios ?? 0,
                'yo_le_di_like' => $usuario ? $contenido->likes->contains('usuario_id', $usuario->id) : false,
                'usuario_esta_suscrito' => $estaSuscrito,
                'created_at' => $contenido->created_at,
                'tiempo' => $contenido->created_at ? $contenido->created_at->diffForHumans() : null,
            ];
        });

        // Estadísticas del creador
        $estadisticas = [
            'total_contenidos' => $creador->total_contenidos,
            'total_suscriptores' => $creador->total_suscriptores,
            'total_ganancias' => $creador->total_ganancias,
        ];

        // Planes de suscripción disponibles
        $planes = $this->getPlanesSuscripcion($creador);

        // Categorías del creador
        $categorias = $creador->categorias ?? [];

        // Verificar si el usuario es el creador (para mostrar opciones de edición)
        $esMiPerfil = $usuario && $usuario->id === $creador->usuario_id;

        return inertia('Usuario/PerfilCreador/Index', [
            'creador' => [
                'id' => $creador->id,
                'usuario_id' => $creador->usuario_id,
                'biografia' => $creador->biografia,
                'categorias' => $categorias,
                'esta_verificado' => $creador->esta_verificado,
                'es_premium' => $creador->es_premium,
                'usuario' => [
                    'id' => $creador->usuario->id,
                    'nombre' => $creador->usuario->nombre,
                    'apodo' => $creador->usuario->apodo,
                    'avatar' => $creador->usuario->avatar,
                    'foto_principal' => $creador->usuario->foto_principal,
                    'ciudad' => $creador->usuario->ciudad,
                    'fecha_nacimiento' => $creador->usuario->fecha_nacimiento?->format('d/m/Y'),
                    'esta_activo' => $creador->usuario->esta_activo,
                    'created_at' => $creador->usuario->created_at,
                ],
                'precio_suscripcion' => $creador->precio_suscripcion,
                'planes' => $planes,
            ],
            'contenidos' => $contenidosFormateados,
            'estadisticas' => $estadisticas,
            'estaSuscrito' => $estaSuscrito,
            'suscripcionActiva' => $suscripcionActiva,
            'esMiPerfil' => $esMiPerfil,
        ]);
    }

    /**
     * GET /creador/{id}/contenidos
     * Carga más contenidos del creador (para scroll infinito o paginación)
     */
    public function contenidos(Request $request, $id)
    {
        $creador = Creador::find($id);

        if (!$creador || !$creador->esta_verificado) {
            return response()->json(['mensaje' => 'Creador no encontrado'], 404);
        }

        $usuario = Auth::user();

        // Verificar suscripción
        $estaSuscrito = false;
        if ($usuario) {
            $suscripcion = Suscripcion::where('usuario_id', $usuario->id)
                ->where('creador_id', $creador->id)
                ->where('estado', 'activa')
                ->first();
            $estaSuscrito = $suscripcion !== null;
        }

        $contenidosQuery = Contenido::where('creador_id', $creador->id)
            ->where('estado', 'publicado')
            ->with(['likes'])
            ->withCount(['likes as total_likes', 'comentarios as total_comentarios']);

        if (!$estaSuscrito) {
            $contenidosQuery->where('es_premium', false);
        }

        $contenidos = $contenidosQuery->orderBy('created_at', 'desc')
            ->paginate(12);

        // ✅ CORREGIDO: Pasar $estaSuscrito en el use del map
        $contenidosFormateados = $contenidos->map(function ($contenido) use ($usuario, $estaSuscrito) {
            return [
                'id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'descripcion' => $contenido->descripcion,
                'texto' => $contenido->texto,
                'es_premium' => $contenido->es_premium,
                'imagen' => $contenido->imagen_url ?? null,
                'archivos' => $contenido->archivos ?? [],
                'total_likes' => $contenido->total_likes ?? 0,
                'total_comentarios' => $contenido->total_comentarios ?? 0,
                'yo_le_di_like' => $usuario ? $contenido->likes->contains('usuario_id', $usuario->id) : false,
                'usuario_esta_suscrito' => $estaSuscrito,
                'created_at' => $contenido->created_at,
                'tiempo' => $contenido->created_at ? $contenido->created_at->diffForHumans() : null,
            ];
        });

        return response()->json([
            'ok' => true,
            'contenidos' => $contenidosFormateados,
            'pagination' => [
                'current_page' => $contenidos->currentPage(),
                'last_page' => $contenidos->lastPage(),
                'per_page' => $contenidos->perPage(),
                'total' => $contenidos->total(),
            ],
        ]);
    }

    /**
     * Obtiene los planes de suscripción del creador
     */
    private function getPlanesSuscripcion($creador)
    {
        $planes = [];

        // Si el creador tiene precio de suscripción configurado
        if ($creador->precio_suscripcion) {
            $planes[] = [
                'id' => 'mensual',
                'nombre' => 'Mensual',
                'precio' => $creador->precio_suscripcion,
                'dias' => 30,
                'descripcion' => 'Acceso a todo el contenido exclusivo',
                'popular' => true,
            ];

            // Plan trimestral (descuento)
            $precioTrimestral = round($creador->precio_suscripcion * 2.7, 2);
            $planes[] = [
                'id' => 'trimestral',
                'nombre' => 'Trimestral',
                'precio' => $precioTrimestral,
                'dias' => 90,
                'descripcion' => '3 meses de acceso con 10% de descuento',
                'popular' => false,
            ];

            // Plan anual (descuento mayor)
            $precioAnual = round($creador->precio_suscripcion * 9.6, 2);
            $planes[] = [
                'id' => 'anual',
                'nombre' => 'Anual',
                'precio' => $precioAnual,
                'dias' => 365,
                'descripcion' => 'Ahorra 20% con el plan anual',
                'popular' => false,
            ];
        }

        return $planes;
    }
}