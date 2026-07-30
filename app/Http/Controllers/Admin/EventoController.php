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
        } else {
            // 'mes' actual
            $queryStats->whereMonth('fecha', now()->month)
                       ->whereYear('fecha', now()->year);
        }

        // Estadísticas filtradas dinámicamente por el periodo seleccionado
        $totalPeriodo = (clone $queryStats)->count();
        $enVivoPeriodo = (clone $queryStats)->where('estado', 'publicado')->whereDate('fecha', $hoy)->count();
        $proximosPeriodo = (clone $queryStats)->where('estado', 'publicado')->where('fecha', '>', $hoy)->count();
        $completadosPeriodo = (clone $queryStats)->whereIn('estado', ['completo', 'publicado'])->where('fecha', '<', $hoy)->count(); // o los que correspondan a tu lógica

        // Contadores generales para las tarjetas superiores (KPIs globales o del mes actual)
        $total = Evento::count();
        $proximos = Evento::where('estado', 'publicado')->where('fecha', '>', $hoy)->count();
        $enVivo = Evento::where('estado', 'publicado')->where('fecha', $hoy)->count();
        $completados = Evento::where('estado', 'completo')->count();

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
                'nombreMes' => $inicioMes->translatedFormat('F Y'),
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

        $evento = Evento::create($data + ['organizador_id' => null]);

        return redirect()->route('admin.eventos.index')->with('success', "Evento \"{$evento->nombre}\" creado correctamente.");
    }

    private function estadoDisplay(Evento $e, string $hoy): string
    {
        if ($e->estado === 'cancelado') return 'cancelado';
        if ($e->estado === 'completo') return 'completado';
        if ($e->estado === 'borrador') return 'borrador';
        // publicado:
        if ($e->fecha->toDateString() === $hoy) return 'en_vivo';
        if ($e->fecha->toDateString() > $hoy) return 'programado';
        return 'completado';
    }
}