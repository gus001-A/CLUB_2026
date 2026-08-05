<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\FotosEvento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventoController extends Controller
{
    /**
     * Muestra la página principal de eventos
     */
    public function index()
    {
        // Evento destacado (el primero que tenga destacado = true)
        $destacado = Evento::with(['organizador', 'fotos'])
            ->publicados()
            ->destacados()
            ->proximos()
            ->first();

        // Si no hay destacado, tomar el más reciente
        if (!$destacado) {
            $destacado = Evento::with(['organizador', 'fotos'])
                ->publicados()
                ->proximos()
                ->orderBy('fecha', 'asc')
                ->first();
        }

        // Formatear el destacado para la vista
        $destacadoFormateado = $destacado ? $this->formatearEvento($destacado) : null;

        // Todos los eventos públicos (con sus fotos)
        $eventos = Evento::with(['organizador', 'fotos'])
            ->publicados()
            ->proximos()
            ->orderBy('fecha', 'asc')
            ->limit(12)
            ->get();

        // Formatear eventos
        $eventosFormateados = $eventos->map(function ($evento) {
            return $this->formatearEvento($evento);
        });

        // Categorías disponibles (extraer de los eventos existentes)
        $categorias = Evento::select('categoria')
            ->whereNotNull('categoria')
            ->publicados()
            ->distinct()
            ->get()
            ->pluck('categoria')
            ->map(function ($categoria) {
                return [
                    'titulo' => $categoria,
                    'icon' => $this->getIconoCategoria($categoria),
                    'imagen' => $this->getImagenCategoria($categoria),
                ];
            })
            ->toArray();

        // Si no hay categorías, usar datos por defecto
        if (empty($categorias)) {
            $categorias = $this->getCategoriasDefault();
        }

        // Próximos eventos recomendados (para el sidebar)
        $proximos = Evento::with(['organizador', 'fotos'])
            ->publicados()
            ->proximos()
            ->orderBy('fecha', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($evento) {
                return [
                    'id' => $evento->id,
                    'dia' => $evento->fecha ? $evento->fecha->format('d') : '--',
                    'mes' => $evento->fecha ? strtoupper($evento->fecha->format('M')) : '---',
                    'imagen' => $evento->url_foto_principal ?? '/images/eventos/default-event.jpg',
                    'titulo' => $evento->nombre,
                    'lugar' => $evento->ciudad,
                    'hora' => $evento->hora_formateada ?? '--:--',
                ];
            });

        return Inertia::render('Usuario/Eventos', [
            'destacado' => $destacadoFormateado,
            'eventos' => $eventosFormateados,
            'categorias' => $categorias,
            'proximos' => $proximos,
        ]);
    }

    /**
     * Muestra un evento específico
     */
    public function show($id)
    {
        $evento = Evento::with(['organizador', 'fotos', 'reservas'])
            ->publicados()
            ->findOrFail($id);

        $eventoFormateado = $this->formatearEvento($evento);

        // Obtener eventos relacionados (misma ciudad o categoría)
        $relacionados = Evento::with(['organizador', 'fotos'])
            ->publicados()
            ->proximos()
            ->where('id', '!=', $id)
            ->where(function ($query) use ($evento) {
                $query->where('ciudad', $evento->ciudad)
                    ->orWhere('categoria', $evento->categoria);
            })
            ->limit(4)
            ->get()
            ->map(function ($e) {
                return $this->formatearEvento($e);
            });

        return Inertia::render('EventoDetalle', [
            'evento' => $eventoFormateado,
            'relacionados' => $relacionados,
        ]);
    }

    /**
     * Busca eventos según filtros (para API o carga dinámica)
     */
    public function buscar(Request $request)
    {
        $query = Evento::with(['organizador', 'fotos'])
            ->publicados()
            ->proximos();

        // Filtro por búsqueda
        if ($request->filled('busqueda')) {
            $search = $request->busqueda;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                    ->orWhere('descripcion', 'LIKE', "%{$search}%")
                    ->orWhere('ciudad', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por ciudad
        if ($request->filled('ciudad') && $request->ciudad !== 'Todas') {
            $query->where('ciudad', $request->ciudad);
        }

        // Filtro por tipo
        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por categoría
        if ($request->filled('categoria') && $request->categoria !== 'Todas') {
            $query->where('categoria', $request->categoria);
        }

        // Filtro solo VIP (eventos con precio > 1000 o marcados como destacados)
        if ($request->boolean('soloVip')) {
            $query->where(function ($q) {
                $q->where('precio', '>=', 1000)
                    ->orWhere('destacado', true);
            });
        }

        // Ordenar
        $orden = $request->get('orden', 'proximos');
        if ($orden === 'proximos') {
            $query->orderBy('fecha', 'asc');
        } elseif ($orden === 'populares') {
            $query->withCount('reservas')->orderBy('reservas_count', 'desc');
        } elseif ($orden === 'precio') {
            $query->orderBy('precio', 'asc');
        }

        $eventos = $query->paginate(12)->through(function ($evento) {
            return $this->formatearEvento($evento);
        });

        return response()->json($eventos);
    }

    /**
     * Obtiene las ciudades disponibles para filtros
     */
    public function ciudades()
    {
        $ciudades = Evento::select('ciudad')
            ->publicados()
            ->whereNotNull('ciudad')
            ->distinct()
            ->get()
            ->pluck('ciudad')
            ->toArray();

        return response()->json([
            'ciudades' => array_merge(['Todas'], $ciudades)
        ]);
    }

    /**
     * Formatea un evento para la vista
     */
    private function formatearEvento(Evento $evento)
    {
        // Obtener la foto principal
        $imagen = $evento->url_foto_principal;

        // Si no tiene foto principal, usar la primera foto de la relación
        if (!$imagen && $evento->fotos->isNotEmpty()) {
            $primeraFoto = $evento->fotos->first();
            if ($primeraFoto) {
                $imagen = $primeraFoto->url_completa;
            }
        }

        // Si aún no hay imagen, usar la imagen por defecto del evento
        if (!$imagen) {
            $imagen = $evento->imagen 
                ? (str_starts_with($evento->imagen, 'http') ? $evento->imagen : asset('storage/' . $evento->imagen))
                : '/images/eventos/default-event.jpg';
        }

        // Formatear fecha
        $dia = $evento->fecha ? $evento->fecha->format('d') : '--';
        $mes = $evento->fecha ? strtoupper($evento->fecha->format('M')) : '---';

        // Construir fecha completa
        $fechaCompleta = '';
        if ($evento->fecha) {
            $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            $fechaCompleta = $diasSemana[$evento->fecha->dayOfWeek] . ', ' . 
                            $evento->fecha->day . ' de ' . 
                            $meses[$evento->fecha->month - 1] . ' · ' . 
                            ($evento->hora_formateada ?? '--:--') . ' hrs';
        }

        return [
            'id' => $evento->id,
            'dia' => $dia,
            'mes' => $mes,
            'imagen' => $imagen,
            'titulo' => $evento->nombre,
            'vip' => $evento->destacado || ($evento->precio && $evento->precio >= 1000),
            'ciudad' => $evento->ciudad,
            'hora' => $evento->hora_formateada ?? '--:--',
            'desc' => $evento->descripcion,
            'fecha' => $fechaCompleta,
            'precio' => $evento->precio,
            'capacidad' => $evento->capacidad,
            'cupos_disponibles' => $evento->cupos_disponibles,
            'esta_completo' => $evento->esta_completo,
            'tipo' => $evento->tipo,
            'categoria' => $evento->categoria,
            'codigo_vestimenta' => $evento->codigo_vestimenta,
            'organizador' => $evento->organizador ? [
                'id' => $evento->organizador->id,
                'nombre' => $evento->organizador->nombre ?? $evento->organizador->nickname ?? 'Organizador',
                'avatar' => $evento->organizador->avatar ?? '/images/shared/avatar-default.jpg',
            ] : null,
            'fotos' => $evento->fotos_recientes_url ?? [],
            'cantidad_fotos' => $evento->cantidad_fotos ?? 0,
            'favorito' => false, // Esto se manejará con el sistema de favoritos del usuario
        ];
    }

    /**
     * Devuelve el icono según la categoría
     */
    private function getIconoCategoria($categoria)
    {
        $iconos = [
            'Fiestas privadas' => 'pi-crown',
            'Jacuzzi Nights' => 'pi-circle',
            'Club Nights' => 'pi-volume-up',
            'Eventos VIP' => 'pi-star',
            'Viajes temáticos' => 'pi-send',
            'Cenas exclusivas' => 'pi-bookmark',
            'Fiesta' => 'pi-calendar',
            'Concierto' => 'pi-volume-up',
            'Cena' => 'pi-cutlery',
            'After' => 'pi-moon',
            'Deportivo' => 'pi-sports',
            'Cultural' => 'pi-palette',
        ];

        return $iconos[$categoria] ?? 'pi-calendar';
    }

    /**
     * Devuelve la imagen por defecto según la categoría
     */
    private function getImagenCategoria($categoria)
    {
        $imagenes = [
            'Fiestas privadas' => '/images/eventos/cat-fiestas-privadas.jpg',
            'Jacuzzi Nights' => '/images/eventos/cat-jacuzzi.jpg',
            'Club Nights' => '/images/eventos/cat-club-nights.jpg',
            'Eventos VIP' => '/images/eventos/cat-eventos-vip.jpg',
            'Viajes temáticos' => '/images/eventos/cat-viajes.jpg',
            'Cenas exclusivas' => '/images/eventos/cat-cenas.jpg',
        ];

        return $imagenes[$categoria] ?? '/images/eventos/default-category.jpg';
    }

    /**
     * Categorías por defecto si no hay datos
     */
    private function getCategoriasDefault()
    {
        return [
            ['icon' => 'pi-crown', 'titulo' => 'Fiestas privadas', 'imagen' => '/images/eventos/cat-fiestas-privadas.jpg'],
            ['icon' => 'pi-circle', 'titulo' => 'Jacuzzi Nights', 'imagen' => '/images/eventos/cat-jacuzzi.jpg'],
            ['icon' => 'pi-volume-up', 'titulo' => 'Club Nights', 'imagen' => '/images/eventos/cat-club-nights.jpg'],
            ['icon' => 'pi-star', 'titulo' => 'Eventos VIP', 'imagen' => '/images/eventos/cat-eventos-vip.jpg'],
            ['icon' => 'pi-send', 'titulo' => 'Viajes temáticos', 'imagen' => '/images/eventos/cat-viajes.jpg'],
            ['icon' => 'pi-bookmark', 'titulo' => 'Cenas exclusivas', 'imagen' => '/images/eventos/cat-cenas.jpg'],
        ];
    }
}