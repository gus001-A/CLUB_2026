<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventoController extends Controller
{
    /**
     * Muestra la página principal de eventos (Índice)
     */
    public function index()
    {
        // Evento destacado
        $destacado = Evento::with(['organizador'])
            ->publicados()
            ->destacados()
            ->proximos()
            ->first();

        if (!$destacado) {
            $destacado = Evento::with(['organizador'])
                ->publicados()
                ->proximos()
                ->orderBy('fecha', 'asc')
                ->first();
        }

        $destacadoFormateado = $destacado ? $this->formatearEventoParaIndex($destacado) : null;

        // Todos los eventos públicos
        $eventos = Evento::with(['organizador'])
            ->publicados()
            ->proximos()
            ->orderBy('fecha', 'asc')
            ->limit(12)
            ->get();

        $eventosFormateados = $eventos->map(function ($evento) {
            return $this->formatearEventoParaIndex($evento);
        });

        // Categorías disponibles
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

        if (empty($categorias)) {
            $categorias = $this->getCategoriasDefault();
        }

        // Próximos eventos recomendados
        $proximos = Evento::with(['organizador'])
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
                    'imagen' => $this->getImagenEvento($evento),
                    'titulo' => $evento->nombre,
                    'lugar' => $evento->ciudad,
                    'hora' => $evento->hora_formateada ?? '--:--',
                    'vip' => $evento->destacado || ($evento->precio && $evento->precio >= 1000),
                ];
            });

        return Inertia::render('Usuario/Eventos/Eventos', [
            'destacado' => $destacadoFormateado,
            'eventos' => $eventosFormateados,
            'categorias' => $categorias,
            'proximos' => $proximos,
        ]);
    }

    /**
     * Muestra un evento específico (Detalle)
     */
    public function show($id)
    {
        $evento = Evento::with(['organizador', 'reservas.usuario'])
            ->publicados()
            ->findOrFail($id);

        // Formatear evento para el detalle
        $eventoFormateado = $this->formatearEventoParaDetalle($evento);

        // Obtener asistentes (reservas confirmadas)
        $asistentes = $evento->reservas()
            ->where('estado', 'confirmada')
            ->with('usuario')
            ->limit(10)
            ->get()
            ->map(function ($reserva) {
                $usuario = $reserva->usuario;
                return [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre ?? $usuario->nickname ?? 'Usuario',
                    'avatar_url' => $usuario->avatar_url ?? null,
                    'verificado' => $usuario->verificado ?? false,
                ];
            })
            ->toArray();

        // Eventos relacionados
        $relacionados = Evento::with(['organizador'])
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
                return $this->formatearEventoParaIndex($e);
            });

        return Inertia::render('Usuario/Eventos/VerEvento', [
            'evento' => $eventoFormateado,
            'asistentes' => $asistentes,
            'eventosRelacionados' => $relacionados,
        ]);
    }

    /**
     * Busca eventos según filtros
     */
    public function buscar(Request $request)
    {
        $query = Evento::with(['organizador'])
            ->publicados()
            ->proximos();

        if ($request->filled('busqueda')) {
            $search = $request->busqueda;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                    ->orWhere('descripcion', 'LIKE', "%{$search}%")
                    ->orWhere('ciudad', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('ciudad') && $request->ciudad !== 'Todas') {
            $query->where('ciudad', $request->ciudad);
        }

        if ($request->filled('tipo') && $request->tipo !== 'Todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('categoria') && $request->categoria !== 'Todas') {
            $query->where('categoria', $request->categoria);
        }

        if ($request->boolean('soloVip')) {
            $query->where(function ($q) {
                $q->where('precio', '>=', 1000)
                    ->orWhere('destacado', true);
            });
        }

        $orden = $request->get('orden', 'proximos');
        if ($orden === 'proximos') {
            $query->orderBy('fecha', 'asc');
        } elseif ($orden === 'populares') {
            $query->withCount('reservas')->orderBy('reservas_count', 'desc');
        } elseif ($orden === 'precio') {
            $query->orderBy('precio', 'asc');
        }

        $eventos = $query->paginate(12)->through(function ($evento) {
            return $this->formatearEventoParaIndex($evento);
        });

        return response()->json($eventos);
    }

    /**
     * Obtiene las ciudades disponibles
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
     * Obtener la imagen del evento
     */
    private function getImagenEvento(Evento $evento)
    {
        if (!$evento->imagen) {
            return '/images/eventos/default-event.jpg';
        }

        // Si es una URL completa
        if (filter_var($evento->imagen, FILTER_VALIDATE_URL)) {
            return $evento->imagen;
        }

        // Si ya tiene storage/ o /storage/
        if (str_starts_with($evento->imagen, 'storage/') || str_starts_with($evento->imagen, '/storage/')) {
            return asset($evento->imagen);
        }

        // Si tiene la ruta completa en el storage
        if (str_starts_with($evento->imagen, 'eventos/')) {
            return asset('storage/' . $evento->imagen);
        }

        // Por defecto, asumir que está en storage/eventos/
        return asset('storage/eventos/' . $evento->imagen);
    }

    /**
     * Obtener la imagen de galería del evento
     */
    private function getImagenesGaleria(Evento $evento)
    {
        // Si el evento tiene imágenes de galería en un campo JSON
        if ($evento->galeria && is_array($evento->galeria)) {
            return array_map(function ($imagen) {
                if (filter_var($imagen, FILTER_VALIDATE_URL)) {
                    return $imagen;
                }
                return asset('storage/' . $imagen);
            }, $evento->galeria);
        }

        // Si solo tiene una imagen principal, usarla en la galería
        return [$this->getImagenEvento($evento)];
    }

    /**
     * Formatea un evento para el índice
     */
    private function formatearEventoParaIndex(Evento $evento)
    {
        return [
            'id' => $evento->id,
            'dia' => $evento->fecha ? $evento->fecha->format('d') : '--',
            'mes' => $evento->fecha ? strtoupper($evento->fecha->format('M')) : '---',
            'imagen' => $this->getImagenEvento($evento),
            'titulo' => $evento->nombre,
            'vip' => $evento->destacado || ($evento->precio && $evento->precio >= 1000),
            'ciudad' => $evento->ciudad ?? 'Ciudad de México',
            'hora' => $evento->hora_formateada ?? '--:--',
            'precio' => $evento->precio,
            'descripcion_corta' => $evento->descripcion_corta ?? $this->truncateText($evento->descripcion, 120),
            'cupos_disponibles' => $evento->cupos_disponibles,
            'cupos_totales' => $evento->capacidad ?? 50,
            'esta_completo' => $evento->esta_completo,
            'destacado' => $evento->destacado,
            'favorito' => false, // TODO: Implementar favoritos
        ];
    }

    /**
     * Formatea un evento para el detalle
     */
    private function formatearEventoParaDetalle(Evento $evento)
    {
        $fechaFormateada = '';
        if ($evento->fecha) {
            $diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            $fechaFormateada = $diasSemana[$evento->fecha->dayOfWeek] . ', ' . 
                              $evento->fecha->day . ' de ' . 
                              $meses[$evento->fecha->month - 1] . ' de ' . 
                              $evento->fecha->year;
        }

        return [
            'id' => $evento->id,
            'titulo' => $evento->nombre,
            'imagen_url' => $this->getImagenEvento($evento),
            'galeria' => $this->getImagenesGaleria($evento),
            'dia' => $evento->fecha ? $evento->fecha->format('d') : '--',
            'mes' => $evento->fecha ? strtoupper($evento->fecha->format('M')) : '---',
            'fecha' => $evento->fecha,
            'fecha_completa' => $fechaFormateada,
            'hora' => $evento->hora_formateada ?? '23:00 hrs',
            'ciudad' => $evento->ciudad ?? 'Ciudad de México',
            'ubicacion' => $evento->ubicacion ?? 'Locación privada',
            'ubicacion_detalle' => $evento->ubicacion_detalle ?? $evento->ubicacion ?? 'Locación privada en CDMX',
            'ubicacion_nota' => $evento->ubicacion_nota ?? 'Se comparte al confirmar tu asistencia',
            'precio' => $evento->precio ?? 1290,
            'moneda' => $evento->moneda ?? 'MXN',
            'cupos_disponibles' => $evento->cupos_disponibles,
            'cupos_totales' => $evento->capacidad ?? 50,
            'tipo_evento' => $evento->tipo ?? 'Fiesta privada',
            'codigo_vestimenta' => $evento->codigo_vestimenta ?? 'Elegante / Casual sofisticado',
            'descripcion' => $evento->descripcion,
            'descripcion_corta' => $evento->descripcion_corta ?? $this->truncateText($evento->descripcion, 150),
            'destacado' => $evento->destacado,
            'categoria' => $evento->categoria,
            'esta_completo' => $evento->esta_completo,
            'organizador' => $evento->organizador ? [
                'id' => $evento->organizador->id,
                'nombre' => $evento->organizador->nombre ?? $evento->organizador->nickname ?? 'Organizador',
                'avatar_url' => $evento->organizador->avatar_url ?? null,
                'verificado' => $evento->organizador->verificado ?? false,
                'descripcion' => $evento->organizador->descripcion ?? 'Creamos experiencias exclusivas para conectar, disfrutar y vivir tu fantasía.',
            ] : null,
            'favorito' => false, // TODO: Implementar favoritos
            'mapa_url' => $evento->mapa_url ?? '/images/eventos/mapa-default.jpg',
        ];
    }

    /**
     * Trunca un texto a una longitud específica
     */
    private function truncateText($text, $length = 120)
    {
        if (!$text) return '';
        if (strlen($text) <= $length) return $text;
        
        $text = substr($text, 0, $length);
        $lastSpace = strrpos($text, ' ');
        
        if ($lastSpace !== false) {
            $text = substr($text, 0, $lastSpace);
        }
        
        return $text . '...';
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
     * Categorías por defecto
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