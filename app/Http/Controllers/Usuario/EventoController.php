<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

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

        // Todos los eventos públicos (solo 12 para la vista inicial)
        $eventos = Evento::with(['organizador'])
            ->publicados()
            ->proximos()
            ->orderBy('fecha', 'asc')
            ->limit(12)
            ->get();

        $eventosFormateados = $eventos->map(function ($evento) {
            return $this->formatearEventoParaIndex($evento);
        })->values()->toArray();

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

        // Próximos eventos (últimos 3)
        $proximos = Evento::with(['organizador'])
            ->publicados()
            ->proximos()
            ->orderBy('fecha', 'asc')
            ->limit(3)
            ->get()
            ->map(function ($evento) {
                $fecha = Carbon::parse($evento->fecha);
                $hora = $evento->hora ? Carbon::parse($evento->hora) : null;
                
                return [
                    'id' => $evento->id,
                    'dia' => $fecha->format('d'),
                    'mes' => strtoupper($this->getMesEspanol($fecha->format('M'))),
                    'imagen' => $this->getImagenEvento($evento),
                    'titulo' => $evento->nombre,
                    'lugar' => $evento->ciudad,
                    'hora' => $evento->hora ? Carbon::parse($evento->hora)->format('H:i') : '--:--',
                    'hora_formateada' => $hora ? $hora->format('g:i A') : 'Horario por definir',
                    'vip' => $evento->destacado || ($evento->tipo === 'vip'),
                    'tipo' => $evento->tipo,
                    'categoria' => $evento->categoria,
                    'precio' => (float) $evento->precio,
                    'precio_formateado' => $evento->precio > 0 ? '$' . number_format($evento->precio, 0, ',', '.') : 'GRATIS',
                ];
            })->values()->toArray();

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

        // 🔥 VERIFICAR SI EL USUARIO AUTENTICADO TIENE RESERVA
        $usuarioId = auth()->id();
        $reservaExistente = null;
        $tieneReserva = false;

        if ($usuarioId) {
            $reservaExistente = Reserva::where('evento_id', $id)
                ->where('usuario_id', $usuarioId)
                ->whereNotIn('estado', ['cancelada', 'rechazada'])
                ->first();

            $tieneReserva = $reservaExistente !== null;
            
            // 🔥 DEBUG - puedes ver esto en los logs
            \Log::info('Verificando reserva', [
                'usuario_id' => $usuarioId,
                'evento_id' => $id,
                'tiene_reserva' => $tieneReserva,
                'reserva_id' => $reservaExistente?->id,
                'reserva_estado' => $reservaExistente?->estado,
            ]);
        }

        // Formatear evento para el detalle
        $eventoFormateado = $this->formatearEventoParaDetalle($evento);

        // Obtener asistentes (reservas confirmadas)
        $asistentes = $evento->reservas()
            ->whereIn('estado', ['aprobada', 'confirmada', 'pendiente'])
            ->with('usuario')
            ->limit(10)
            ->get()
            ->map(function ($reserva) {
                $usuario = $reserva->usuario;
                return [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre ?? $usuario->apodo ?? 'Usuario',
                    'avatar_url' => $usuario->avatar ?? null,
                    'verificado' => $usuario->estado === 'verificado',
                ];
            })
            ->toArray();

        // Contar asistentes totales
        $totalAsistentes = $evento->reservas()
            ->whereIn('estado', ['aprobada', 'confirmada', 'pendiente'])
            ->count();

        // Contar creadores que asistirán (usuarios con rol 'creador')
        $creadoresAsistentes = $evento->reservas()
            ->whereIn('estado', ['aprobada', 'confirmada', 'pendiente'])
            ->whereHas('usuario', function ($query) {
                $query->where('rol', 'creador');
            })
            ->count();

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

        // 🔥 RETORNAR CON LAS PROPS DE RESERVA
        return Inertia::render('Usuario/Eventos/VerEvento', [
            'evento' => $eventoFormateado,
            'asistentes' => $asistentes,
            'totalAsistentes' => $totalAsistentes,
            'creadoresAsistentes' => $creadoresAsistentes,
            'eventosRelacionados' => $relacionados,
            'tieneReserva' => $tieneReserva,
            'reserva' => $reservaExistente ? [
                'id' => $reservaExistente->id,
                'folio' => $reservaExistente->folio,
                'estado' => $reservaExistente->estado,
                'asistentes' => $reservaExistente->asistentes,
                'total' => (float) $reservaExistente->total,
                'tipo_acceso' => $reservaExistente->tipo_acceso,
                'created_at' => $reservaExistente->created_at,
                'codigo_qr' => $reservaExistente->codigo_qr,
            ] : null,
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

        if ($request->filled('ciudad') && $request->ciudad !== 'TODAS') {
            $query->where('ciudad', $request->ciudad);
        }

        if ($request->filled('tipo') && $request->tipo !== 'TODOS') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('categoria') && $request->categoria !== 'Todas') {
            $query->where('categoria', $request->categoria);
        }

        if ($request->boolean('soloVip')) {
            $query->where(function ($q) {
                $q->where('precio', '>=', 1000)
                    ->orWhere('destacado', true)
                    ->orWhere('tipo', 'vip');
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

        if (filter_var($evento->imagen, FILTER_VALIDATE_URL)) {
            return $evento->imagen;
        }

        if (str_starts_with($evento->imagen, 'storage/') || str_starts_with($evento->imagen, '/storage/')) {
            return asset($evento->imagen);
        }

        if (str_starts_with($evento->imagen, 'eventos/')) {
            return asset('storage/' . $evento->imagen);
        }

        return asset('storage/eventos/' . $evento->imagen);
    }

    /**
     * Obtener la imagen de galería del evento
     */
    private function getImagenesGaleria(Evento $evento)
    {
        if ($evento->metadatos && isset($evento->metadatos['galeria']) && is_array($evento->metadatos['galeria'])) {
            return array_map(function ($imagen) {
                if (filter_var($imagen, FILTER_VALIDATE_URL)) {
                    return $imagen;
                }
                return asset('storage/' . $imagen);
            }, $evento->metadatos['galeria']);
        }

        return [$this->getImagenEvento($evento)];
    }

    /**
     * Formatea un evento para el índice
     */
    private function formatearEventoParaIndex(Evento $evento)
    {
        $fecha = Carbon::parse($evento->fecha);
        $hora = $evento->hora ? Carbon::parse($evento->hora) : null;

        $reservasConfirmadas = $evento->reservas()->whereIn('estado', ['pendiente', 'confirmada', 'aprobada'])->count();
        $disponible = $evento->capacidad - $reservasConfirmadas;
        $estaCompleto = $disponible <= 0;

        return [
            'id' => $evento->id,
            'dia' => $fecha->format('d'),
            'mes' => strtoupper($this->getMesEspanol($fecha->format('M'))),
            'fecha_completa' => $this->getFechaCompletaEspanol($fecha),
            'imagen' => $this->getImagenEvento($evento),
            'titulo' => $evento->nombre,
            'vip' => $evento->destacado || ($evento->tipo === 'vip'),
            'ciudad' => $evento->ciudad ?? 'Ciudad de México',
            'hora' => $hora ? $hora->format('H:i') : '--:--',
            'hora_formateada' => $hora ? $hora->format('g:i A') : 'Horario por definir',
            'precio' => (float) $evento->precio,
            'precio_formateado' => $evento->precio > 0 ? '$' . number_format($evento->precio, 0, ',', '.') : 'GRATIS',
            'descripcion_corta' => $this->truncateText($evento->descripcion, 120),
            'descripcion' => $evento->descripcion,
            'cupos_disponibles' => max(0, $disponible),
            'cupos_totales' => $evento->capacidad ?? 50,
            'esta_completo' => $estaCompleto,
            'destacado' => $evento->destacado,
            'tipo' => $evento->tipo,
            'categoria' => $evento->categoria,
            'fecha' => $evento->fecha,
            'ubicacion_lat' => (float) $evento->ubicacion_lat,
            'ubicacion_lng' => (float) $evento->ubicacion_lng,
        ];
    }

    /**
     * Formatea un evento para el detalle
     */
    private function formatearEventoParaDetalle(Evento $evento)
    {
        $fecha = Carbon::parse($evento->fecha);
        $hora = $evento->hora ? Carbon::parse($evento->hora) : null;

        $reservasConfirmadas = $evento->reservas()->whereIn('estado', ['pendiente', 'confirmada', 'aprobada'])->count();
        $disponible = $evento->capacidad - $reservasConfirmadas;
        $porcentajeOcupado = $evento->capacidad > 0 ? round(($reservasConfirmadas / $evento->capacidad) * 100) : 0;

        // Contar creadores que asistirán
        $creadoresAsistentes = $evento->reservas()
            ->whereIn('estado', ['pendiente', 'confirmada', 'aprobada'])
            ->whereHas('usuario', function ($query) {
                $query->where('rol', 'creador');
            })
            ->count();

        return [
            'id' => $evento->id,
            'titulo' => $evento->nombre,
            'imagen_url' => $this->getImagenEvento($evento),
            'galeria' => $this->getImagenesGaleria($evento),
            'dia' => $fecha->format('d'),
            'mes' => strtoupper($this->getMesEspanol($fecha->format('M'))),
            'fecha_completa' => $this->getFechaCompletaEspanol($fecha),
            'fecha' => $evento->fecha,
            'hora' => $hora ? $hora->format('H:i') : '23:00',
            'hora_formateada' => $hora ? $hora->format('g:i A') : '23:00 hrs',
            'ciudad' => $evento->ciudad ?? 'Ciudad de México',
            'ubicacion' => $evento->zona_ubicacion ?? 'Locación privada',
            'ubicacion_detalle' => $evento->zona_ubicacion ?? 'Locación privada',
            'ubicacion_nota' => 'Se comparte al confirmar tu asistencia',
            'precio' => (float) $evento->precio,
            'precio_formateado' => $evento->precio > 0 ? '$' . number_format($evento->precio, 0, ',', '.') : 'GRATIS',
            'moneda' => 'MXN',
            'cupos_disponibles' => max(0, $disponible),
            'cupos_totales' => $evento->capacidad ?? 50,
            'porcentaje_ocupado' => $porcentajeOcupado,
            'esta_completo' => $disponible <= 0,
            'tipo_evento' => $this->getTipoEventoNombre($evento->tipo),
            'tipo' => $evento->tipo,
            'categoria' => $evento->categoria,
            'codigo_vestimenta' => $evento->codigo_vestimenta ?? 'Elegante / Casual sofisticado',
            'descripcion' => $evento->descripcion,
            'descripcion_corta' => $this->truncateText($evento->descripcion, 150),
            'destacado' => (bool) $evento->destacado,
            'vip' => (bool) ($evento->destacado || $evento->tipo === 'vip'),
            'organizador' => $evento->organizador ? [
                'id' => $evento->organizador->id,
                'nombre' => $evento->organizador->nombre ?? $evento->organizador->apodo ?? 'Organizador',
                'avatar_url' => $evento->organizador->avatar ?? null,
                'verificado' => $evento->organizador->estado === 'verificado',
                'descripcion' => 'Creamos experiencias exclusivas para conectar, disfrutar y vivir tu fantasía.',
            ] : null,
            'mapa_url' => '/images/eventos/mapa-default.jpg',
            'ubicacion_lat' => (float) $evento->ubicacion_lat,
            'ubicacion_lng' => (float) $evento->ubicacion_lng,
            'capacidad' => $evento->capacidad ?? 'Ilimitado',
            'estado' => $evento->estado,
            'created_at' => $evento->created_at,
            'updated_at' => $evento->updated_at,
            'zona_ubicacion' => $evento->zona_ubicacion,
            'codigo_vestimenta' => $evento->codigo_vestimenta,
            'total_asistentes' => $reservasConfirmadas,
            'creadores_asistentes' => $creadoresAsistentes,
        ];
    }

    /**
     * Obtiene el nombre del tipo de evento en español
     */
    private function getTipoEventoNombre($tipo)
    {
        $tipos = [
            'vip' => 'VIP',
            'general' => 'General',
            'premium' => 'Premium',
            'exclusivo' => 'Exclusivo',
        ];
        return $tipos[$tipo] ?? ucfirst($tipo);
    }

    /**
     * Obtiene el mes en español
     */
    private function getMesEspanol($mes)
    {
        $meses = [
            'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar',
            'Apr' => 'Abr', 'May' => 'May', 'Jun' => 'Jun',
            'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Sep',
            'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic',
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
        ];
        return $meses[$mes] ?? $mes;
    }

    /**
     * Obtiene la fecha completa en español
     */
    private function getFechaCompletaEspanol($fecha)
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        
        return $dias[$fecha->dayOfWeek] . ', ' . $fecha->day . ' de ' . $meses[$fecha->month - 1] . ' de ' . $fecha->year;
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
            'Nocturna' => 'pi-moon',
            'Social' => 'pi-users',
            'VIP' => 'pi-star',
            'Fiestas privadas' => 'pi-crown',
            'Jacuzzi' => 'pi-circle',
            'Club nights' => 'pi-volume-up',
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
            'Nocturna' => '/images/eventos/cat-nocturna.jpg',
            'Social' => '/images/eventos/cat-social.jpg',
            'VIP' => '/images/eventos/cat-vip.jpg',
            'Fiestas privadas' => '/images/eventos/cat-fiestas-privadas.jpg',
            'Jacuzzi' => '/images/eventos/cat-jacuzzi.jpg',
            'Club nights' => '/images/eventos/cat-club-nights.jpg',
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
            ['icon' => 'pi-moon', 'titulo' => 'Nocturna', 'imagen' => '/images/eventos/cat-nocturna.jpg'],
            ['icon' => 'pi-users', 'titulo' => 'Social', 'imagen' => '/images/eventos/cat-social.jpg'],
            ['icon' => 'pi-star', 'titulo' => 'VIP', 'imagen' => '/images/eventos/cat-vip.jpg'],
            ['icon' => 'pi-crown', 'titulo' => 'Fiestas privadas', 'imagen' => '/images/eventos/cat-fiestas-privadas.jpg'],
            ['icon' => 'pi-send', 'titulo' => 'Viajes temáticos', 'imagen' => '/images/eventos/cat-viajes.jpg'],
            ['icon' => 'pi-bookmark', 'titulo' => 'Cenas exclusivas', 'imagen' => '/images/eventos/cat-cenas.jpg'],
        ];
    }
}