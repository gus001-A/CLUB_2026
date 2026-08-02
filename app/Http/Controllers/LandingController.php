<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    /**
     * Muestra la página de inicio con los eventos próximos.
     */
    public function index()
    {
        // Obtener eventos próximos (fecha >= hoy) y publicados
        $eventosProximos = Evento::publicados()
            ->proximos()
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->take(3) // Limitamos a 3 eventos
            ->get()
            ->map(function ($evento) {
                return [
                    'id' => $evento->id,
                    'dia' => $evento->fecha ? $evento->fecha->format('d') : null,
                    'mes' => $evento->fecha ? strtoupper($evento->fecha->format('M')) : null,
                    'titulo' => $evento->nombre,
                    'ubicacion' => $evento->ciudad,
                    'hora' => $evento->hora ? $evento->hora->format('H:i') . ' hrs' : 'Horario por definir',
                    'texto' => $evento->descripcion,
                    'imagen' => $evento->imagen ? '/storage/' . $evento->imagen : '/images/events/default.jpg',
                ];
            });

        // Verificar si hay eventos próximos
        $hayEventos = $eventosProximos->count() > 0;

        return Inertia::render('Landing', [
            'eventosProximos' => $eventosProximos,
            'hayEventos' => $hayEventos,
        ]);
    }
}