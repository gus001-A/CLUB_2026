<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LandingController extends Controller
{
    /**
     * Muestra la página de inicio con los eventos próximos.
     */
    public function index()
    {
        try {
            // Configurar locale a español para Carbon
            Carbon::setLocale('es');
            setlocale(LC_TIME, 'spanish');

            // Obtener eventos próximos (fecha >= hoy) y publicados
            $eventosProximos = Evento::publicados()
                ->proximos()
                ->orderBy('fecha', 'asc')
                ->orderBy('hora', 'asc')
                ->take(3) // Limitamos a 3 eventos
                ->get()
                ->map(function ($evento) {
                    // Generar URL de la imagen con asset()
                    $imagenUrl = !empty($evento->imagen) 
                        ? asset('storage/' . $evento->imagen) 
                        : asset('images/events/default.jpg');

                    // FORMATO DE FECHAS EN ESPAÑOL
                    $fecha = $evento->fecha;
                    
                    // Día (ej: 13)
                    $dia = $fecha ? $fecha->format('d') : null;
                    
                    // Mes ABREVIADO en español (ej: AGO, SEP, OCT)
                    $mes = $fecha ? strtoupper($fecha->translatedFormat('M')) : null;
                    
                    // Mes COMPLETO en español (ej: Agosto, Septiembre)
                    $mesCompleto = $fecha ? ucfirst($fecha->translatedFormat('F')) : null;
                    
                    // Día de la semana en español (ej: Lunes, Martes)
                    $diaSemana = $fecha ? ucfirst($fecha->translatedFormat('l')) : null;
                    
                    // Fecha completa en español (ej: 13 de Agosto de 2026)
                    $fechaCompleta = $fecha ? $fecha->translatedFormat('d \d\e F \d\e Y') : null;
                    
                    // Fecha corta en español (ej: 13/08/2026)
                    $fechaCorta = $fecha ? $fecha->format('d/m/Y') : null;

                    return [
                        'id' => $evento->id,
                        'dia' => $dia,
                        'mes' => $mes, // Ahora en español: AGO, SEP, etc.
                        'mesCompleto' => $mesCompleto,
                        'diaSemana' => $diaSemana,
                        'fechaCompleta' => $fechaCompleta,
                        'fechaCorta' => $fechaCorta,
                        'titulo' => $evento->nombre,
                        'ubicacion' => $evento->ciudad,
                        'hora' => $evento->hora ? $evento->hora->format('H:i') . ' hrs' : 'Horario por definir',
                        'texto' => $evento->descripcion,
                        'imagen' => $imagenUrl,
                    ];
                });

            // Verificar si hay eventos próximos
            $hayEventos = $eventosProximos->count() > 0;

            // Log para depuración
            Log::info('Eventos próximos cargados', ['count' => $eventosProximos->count()]);

            return Inertia::render('Landing', [
                'eventosProximos' => $eventosProximos,
                'hayEventos' => $hayEventos,
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar eventos próximos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // En caso de error, mostrar página con eventos vacíos
            return Inertia::render('Landing', [
                'eventosProximos' => [],
                'hayEventos' => false,
            ]);
        }
    }
}