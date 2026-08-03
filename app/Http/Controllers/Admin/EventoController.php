<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class EventoController extends Controller
{
    public function index(Request $request): Response
    {
        $hoy = now()->toDateString();

        // 1. Capturar el periodo de las estadísticas (por defecto 'mes')
        $periodoStats = $request->string('periodo_stats', 'mes')->value();

        $queryStats = Evento::query();

        if ($periodoStats === 'dia') {
            $queryStats->whereDate('fecha', $hoy);
        } elseif ($periodoStats === 'semana') {
            $queryStats->whereBetween('fecha', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($periodoStats === 'anio') {
            $queryStats->whereYear('fecha', now()->year);
        } else {
            // 'mes' actual
            $queryStats->whereMonth('fecha', now()->month)
                    ->whereYear('fecha', now()->year);
        }

        // Estadísticas filtradas dinámicamente por el periodo seleccionado
        $eventosPeriodo = (clone $queryStats)->get();

        $totalPeriodo = $eventosPeriodo->count();
        $enVivoPeriodo = $eventosPeriodo->filter(fn ($e) => $this->estadoDisplay($e, $hoy) === 'en_vivo')->count();
        $proximosPeriodo = $eventosPeriodo->filter(fn ($e) => $this->estadoDisplay($e, $hoy) === 'programado')->count();
        $completadosPeriodo = $eventosPeriodo->filter(fn ($e) => $this->estadoDisplay($e, $hoy) === 'completado')->count();

        // Contadores generales para las tarjetas superiores (todos los eventos, con la misma lógica de fecha+hora)
        $todosLosEventos = Evento::all();

        $total = $todosLosEventos->count();
        $proximos = $todosLosEventos->filter(fn ($e) => $this->estadoDisplay($e, $hoy) === 'programado')->count();
        $enVivo = $todosLosEventos->filter(fn ($e) => $this->estadoDisplay($e, $hoy) === 'en_vivo')->count();
        $completados = $todosLosEventos->filter(fn ($e) => $this->estadoDisplay($e, $hoy) === 'completado')->count();

        $query = Evento::query();

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('ciudad', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->string('estado')->value()) {
            $query->where('estado', $estado);
        }

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('tipo', $tipo);
        }

        $eventos = $query->orderBy('fecha')->paginate(7)->withQueryString();
        $eventos->through(fn ($e) => [
            'id' => $e->id,
            'nombre' => $e->nombre,
            'imagen' => $e->imagen,
            'tipo' => $e->tipo,
            'fecha' => $e->fecha,
            'hora_formateada' => $e->hora_formateada,
            'ciudad' => $e->ciudad,
            'estado_display' => $this->estadoDisplay($e, $hoy),
        ]);

        // --- Calendario del mes solicitado (o el actual) ---
        $mes = $request->integer('mes') ?: now()->month;
        $anio = $request->integer('anio') ?: now()->year;
        $inicioMes = now()->setDate($anio, $mes, 1)->startOfMonth();
        $finMes = $inicioMes->copy()->endOfMonth();

        $eventosDelMes = Evento::whereBetween('fecha', [$inicioMes->toDateString(), $finMes->toDateString()])->get();
        $diasMarcados = $eventosDelMes->groupBy(fn ($e) => $e->fecha->day)->map(function ($grupo) use ($hoy) {
            return $grupo->map(fn ($e) => $this->estadoDisplay($e, $hoy))->unique()->values();
        });

        return Inertia::render('Admin/Eventos/Index', [
            'stats' => [
                'total' => $total,
                'nuevosEsteMes' => Evento::where('created_at', '>=', now()->startOfMonth())->count(),
                'proximos' => $proximos,
                'enVivo' => $enVivo,
                'completados' => $completados,
            ],
            'eventos' => $eventos,
            'filtros' => $request->only(['q', 'estado', 'tipo', 'periodo_stats']),
            'calendario' => [
                'mes' => $mes,
                'anio' => $anio,
                'nombreMes' => match ((int) $inicioMes->month) {
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
                } . ' ' . $inicioMes->year,
                'dias' => $diasMarcados,
            ],
            'proximosEventos' => Evento::where('estado', 'publicado')
                ->where('fecha', '>=', $hoy)
                ->orderBy('fecha')
                ->take(3)
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'imagen' => $e->imagen,
                    'fecha' => $e->fecha,
                    'hora_formateada' => $e->hora_formateada,
                    'ciudad' => $e->ciudad,
                    'estado_display' => $this->estadoDisplay($e, $hoy),
                ]),
            'estadisticas' => [
                'total' => $totalPeriodo,
                'enVivo' => $enVivoPeriodo,
                'programados' => $proximosPeriodo,
                'completados' => $completadosPeriodo,
                'asistentesTotales' => Reserva::aprobadas()->sum('asistentes'),
                'reservasTotales' => Reserva::aprobadas()->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Eventos/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'fecha' => ['required', 'date'],
            'hora' => ['required'],
            'ciudad' => ['required', 'string', 'max:255'],
            'zona_ubicacion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'tipo' => ['required', 'in:vip,general'],
            'categoria' => ['nullable', 'string', 'max:255'],
            'codigo_vestimenta' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:borrador,publicado,cancelado,completo'],
        ]);

        $evento = Evento::create($data + ['organizador_id' => \Illuminate\Support\Facades\Auth::guard('admin')->id()]);

        return redirect()->route('admin.eventos.index')->with('success', "Evento \"{$evento->nombre}\" creado correctamente.");
    }

    public function show(Evento $evento): Response
    {
        $evento->load('organizador:id,nombre');
        $evento->estado_display = $this->estadoDisplay($evento, now()->toDateString());

        return Inertia::render('Admin/Eventos/Show', [
            'evento' => $evento,
        ]);
    }
    public function edit(Evento $evento): Response
    {
        return Inertia::render('Admin/Eventos/Edit', [
            'evento' => $evento,
        ]);
    }

    public function update(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'fecha' => ['required', 'date'],
            'hora' => ['required'],
            'ciudad' => ['required', 'string', 'max:255'],
            'zona_ubicacion' => ['nullable', 'string', 'max:255'],
            'precio' => ['required', 'numeric', 'min:0'],
            'capacidad' => ['nullable', 'integer', 'min:1'],
            'tipo' => ['required', 'in:vip,general'],
            'categoria' => ['nullable', 'string', 'max:255'],
            'codigo_vestimenta' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', 'in:borrador,publicado,cancelado,completo'],
        ]);

        $evento->update($data);

        return redirect()->route('admin.eventos.index')->with('success', "Evento \"{$evento->nombre}\" actualizado correctamente.");
    }

    public function destroy(Evento $evento)
    {
        $nombre = $evento->nombre;
        $evento->delete();

        return back()->with('success', "Evento \"{$nombre}\" eliminado correctamente.");
    }

    private function estadoDisplay(Evento $e, string $hoy): string
    {
        if ($e->estado === 'cancelado') return 'cancelado';
        if ($e->estado === 'completo') return 'completado';
        if ($e->estado === 'borrador') return 'borrador';

        // publicado: combinamos fecha + hora para saber si ya empezó de verdad
        $horaTexto = $e->hora instanceof \Carbon\Carbon
            ? $e->hora->format('H:i:s')
            : \Carbon\Carbon::parse($e->hora)->format('H:i:s');

        $inicio = \Carbon\Carbon::parse($e->fecha->toDateString() . ' ' . $horaTexto);

        if (now()->lt($inicio)) {
            return 'programado'; // aún no llega la hora
        }

        return 'en_vivo'; // ya pasó la hora de inicio y nadie lo ha marcado como completado
    }
}