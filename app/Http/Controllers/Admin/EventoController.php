<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Reserva;
use App\Models\FotosEvento;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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
            'cantidad_fotos' => $e->fotos()->count(),
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
                    'fotos_count' => $e->fotos()->count(),
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
        Log::info('=== INICIO store evento ===');

        try {
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
                'imagen' => ['nullable', 'image', 'max:2048'], // 2MB max
                'fotos' => ['nullable', 'array'],
                'fotos.*.file' => ['nullable', 'image', 'max:2048'],
                'fotos.*.nombre' => ['nullable', 'string', 'max:255'],
            ]);

            Log::info('Datos validados', ['user_id' => auth()->id()]);

            // Crear el evento
            $evento = Evento::create($data + ['organizador_id' => null]);

            Log::info('Evento creado', ['evento_id' => $evento->id, 'nombre' => $evento->nombre]);

            // ============================================================
            // PROCESAR IMAGEN PRINCIPAL
            // ============================================================
            if ($request->hasFile('imagen')) {
                $imagenPath = $request->file('imagen')->store('eventos/' . $evento->id, 'public');
                $evento->update(['imagen' => $imagenPath]);
                Log::info('Imagen principal guardada', [
                    'evento_id' => $evento->id,
                    'path' => $imagenPath
                ]);
            }

            // ============================================================
            // PROCESAR FOTOS ADICIONALES
            // ============================================================
            if ($request->has('fotos') && is_array($request->fotos)) {
                $fotosGuardadas = 0;
                foreach ($request->fotos as $index => $fotoData) {
                    try {
                        $nombreImagen = $fotoData['nombre'] ?? 'foto_' . ($index + 1);

                        if (isset($fotoData['file']) && $fotoData['file'] instanceof \Illuminate\Http\UploadedFile) {
                            $path = $fotoData['file']->store('eventos/' . $evento->id . '/fotos', 'public');
                            
                            FotosEvento::create([
                                'evento_id' => $evento->id,
                                'nombre_imagen' => $nombreImagen,
                                'ruta' => $path,
                                'usuario_subio' => auth()->id(),
                                'fecha_subida' => now(),
                            ]);
                            
                            $fotosGuardadas++;
                            Log::debug('Foto adicional guardada', [
                                'evento_id' => $evento->id,
                                'path' => $path,
                                'nombre' => $nombreImagen
                            ]);
                        } elseif (isset($fotoData['url']) && filter_var($fotoData['url'], FILTER_VALIDATE_URL)) {
                            // Si se proporciona una URL (para compatibilidad)
                            FotosEvento::create([
                                'evento_id' => $evento->id,
                                'nombre_imagen' => $nombreImagen,
                                'ruta' => $fotoData['url'],
                                'usuario_subio' => auth()->id(),
                                'fecha_subida' => now(),
                            ]);
                            
                            $fotosGuardadas++;
                            Log::debug('Foto adicional guardada como URL', [
                                'evento_id' => $evento->id,
                                'url' => $fotoData['url']
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al guardar foto adicional', [
                            'evento_id' => $evento->id,
                            'index' => $index,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Log::info('Fotos adicionales guardadas', [
                    'evento_id' => $evento->id,
                    'total_guardadas' => $fotosGuardadas
                ]);
            }

            Log::info('=== FIN store evento - EXITOSO ===', ['evento_id' => $evento->id]);

            return redirect()->route('admin.eventos.index')->with('success', "Evento \"{$evento->nombre}\" creado correctamente.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ERROR DE VALIDACION en store evento', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('ERROR en store evento', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al crear el evento: ' . $e->getMessage());
        }
    }

    public function show(Evento $evento): Response
    {
        $hoy = now()->toDateString();
        $evento->estado_display = $this->estadoDisplay($evento, $hoy);

        // Cargar las fotos del evento
        $fotos = $evento->fotos()->orderBy('created_at', 'asc')->get()->map(function($foto) {
            return [
                'id' => $foto->id,
                'nombre_imagen' => $foto->nombre_imagen,
                'ruta' => $foto->ruta,
                'url_completa' => asset('storage/' . $foto->ruta),
                'fecha_subida' => $foto->fecha_subida_formateada,
                'usuario_subio' => $foto->usuario ? $foto->usuario->name : 'Desconocido',
            ];
        });

        // Estadísticas de fotos
        $statsFotos = [
            'total' => $fotos->count(),
            'fecha_primera' => $fotos->first() ? $fotos->first()['fecha_subida'] : null,
            'fecha_ultima' => $fotos->last() ? $fotos->last()['fecha_subida'] : null,
        ];

        return Inertia::render('Admin/Eventos/Show', [
            'evento' => array_merge($evento->toArray(), [
                'estado_display' => $evento->estado_display,
                'fotos' => $fotos,
                'stats_fotos' => $statsFotos,
            ]),
        ]);
    }

    public function edit(Evento $evento): Response
    {
        // Cargar fotos del evento para el formulario de edición
        $fotos = $evento->fotos()->orderBy('created_at', 'asc')->get()->map(function($foto) {
            return [
                'id' => $foto->id,
                'nombre_imagen' => $foto->nombre_imagen,
                'ruta' => $foto->ruta,
                'url' => asset('storage/' . $foto->ruta),
                'fecha_subida' => $foto->fecha_subida_formateada,
            ];
        });

        return Inertia::render('Admin/Eventos/Edit', [
            'evento' => array_merge($evento->toArray(), [
                'fotos' => $fotos,
            ]),
        ]);
    }

    public function update(Request $request, Evento $evento)
    {
        Log::info('=== INICIO update evento ===', ['evento_id' => $evento->id]);

        try {
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
                'imagen' => ['nullable', 'image', 'max:2048'],
                'fotos' => ['nullable', 'array'],
                'fotos.*.file' => ['nullable', 'image', 'max:2048'],
                'fotos.*.nombre' => ['nullable', 'string', 'max:255'],
                'fotos_eliminar' => ['nullable', 'array'],
                'fotos_eliminar.*' => ['integer', 'exists:fotos_eventos,id'],
            ]);

            Log::info('Datos validados para update', ['evento_id' => $evento->id]);

            // ============================================================
            // ACTUALIZAR IMAGEN PRINCIPAL
            // ============================================================
            if ($request->hasFile('imagen')) {
                // Eliminar imagen anterior
                if ($evento->imagen && Storage::disk('public')->exists($evento->imagen)) {
                    Storage::disk('public')->delete($evento->imagen);
                    Log::debug('Imagen principal anterior eliminada', [
                        'evento_id' => $evento->id,
                        'path' => $evento->imagen
                    ]);
                }
                
                $imagenPath = $request->file('imagen')->store('eventos/' . $evento->id, 'public');
                $data['imagen'] = $imagenPath;
                
                Log::info('Imagen principal actualizada', [
                    'evento_id' => $evento->id,
                    'new_path' => $imagenPath
                ]);
            }

            // Actualizar evento
            $evento->update($data);
            Log::info('Evento actualizado', ['evento_id' => $evento->id]);

            // ============================================================
            // ELIMINAR FOTOS SELECCIONADAS
            // ============================================================
            if ($request->has('fotos_eliminar') && is_array($request->fotos_eliminar)) {
                $fotosEliminar = FotosEvento::whereIn('id', $request->fotos_eliminar)
                    ->where('evento_id', $evento->id)
                    ->get();

                foreach ($fotosEliminar as $foto) {
                    // Eliminar archivo físico
                    if (Storage::disk('public')->exists($foto->ruta)) {
                        Storage::disk('public')->delete($foto->ruta);
                        Log::debug('Foto eliminada del storage', [
                            'evento_id' => $evento->id,
                            'foto_id' => $foto->id,
                            'path' => $foto->ruta
                        ]);
                    }
                    $foto->delete();
                }
                
                Log::info('Fotos eliminadas', [
                    'evento_id' => $evento->id,
                    'cantidad' => $fotosEliminar->count()
                ]);
            }

            // ============================================================
            // AGREGAR NUEVAS FOTOS
            // ============================================================
            if ($request->has('fotos') && is_array($request->fotos)) {
                $fotosGuardadas = 0;
                foreach ($request->fotos as $index => $fotoData) {
                    // Si la foto tiene ID, es una foto existente (no hacer nada)
                    if (isset($fotoData['id'])) {
                        continue;
                    }

                    try {
                        $nombreImagen = $fotoData['nombre'] ?? 'foto_' . ($index + 1);

                        if (isset($fotoData['file']) && $fotoData['file'] instanceof \Illuminate\Http\UploadedFile) {
                            $path = $fotoData['file']->store('eventos/' . $evento->id . '/fotos', 'public');
                            
                            FotosEvento::create([
                                'evento_id' => $evento->id,
                                'nombre_imagen' => $nombreImagen,
                                'ruta' => $path,
                                'usuario_subio' => auth()->id(),
                                'fecha_subida' => now(),
                            ]);
                            
                            $fotosGuardadas++;
                            Log::debug('Nueva foto adicional guardada', [
                                'evento_id' => $evento->id,
                                'path' => $path,
                                'nombre' => $nombreImagen
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al guardar nueva foto en update', [
                            'evento_id' => $evento->id,
                            'index' => $index,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                if ($fotosGuardadas > 0) {
                    Log::info('Nuevas fotos guardadas en update', [
                        'evento_id' => $evento->id,
                        'total_guardadas' => $fotosGuardadas
                    ]);
                }
            }

            Log::info('=== FIN update evento - EXITOSO ===', ['evento_id' => $evento->id]);

            return redirect()->route('admin.eventos.index')->with('success', "Evento \"{$evento->nombre}\" actualizado correctamente.");

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ERROR DE VALIDACION en update evento', [
                'evento_id' => $evento->id,
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('ERROR en update evento', [
                'evento_id' => $evento->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al actualizar el evento: ' . $e->getMessage());
        }
    }

    public function destroy(Evento $evento)
    {
        Log::info('=== INICIO destroy evento ===', ['evento_id' => $evento->id]);

        try {
            $nombre = $evento->nombre;

            // ============================================================
            // ELIMINAR TODAS LAS FOTOS DEL EVENTO
            // ============================================================
            $fotos = $evento->fotos;
            foreach ($fotos as $foto) {
                // Eliminar archivo físico
                if (Storage::disk('public')->exists($foto->ruta)) {
                    Storage::disk('public')->delete($foto->ruta);
                    Log::debug('Foto eliminada del storage', [
                        'evento_id' => $evento->id,
                        'foto_id' => $foto->id,
                        'path' => $foto->ruta
                    ]);
                }
                $foto->delete();
            }
            
            // Eliminar imagen principal
            if ($evento->imagen && Storage::disk('public')->exists($evento->imagen)) {
                Storage::disk('public')->delete($evento->imagen);
                Log::debug('Imagen principal eliminada del storage', [
                    'evento_id' => $evento->id,
                    'path' => $evento->imagen
                ]);
            }

            // Eliminar la carpeta del evento si está vacía
            $carpetaEvento = 'eventos/' . $evento->id;
            if (Storage::disk('public')->exists($carpetaEvento)) {
                Storage::disk('public')->deleteDirectory($carpetaEvento);
                Log::debug('Carpeta del evento eliminada', [
                    'evento_id' => $evento->id,
                    'folder' => $carpetaEvento
                ]);
            }

            // Eliminar el evento
            $evento->delete();

            Log::info('=== FIN destroy evento - EXITOSO ===', [
                'evento_id' => $evento->id,
                'nombre' => $nombre,
                'fotos_eliminadas' => $fotos->count()
            ]);

            return back()->with('success', "Evento \"{$nombre}\" eliminado correctamente.");

        } catch (\Exception $e) {
            Log::error('ERROR en destroy evento', [
                'evento_id' => $evento->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al eliminar el evento: ' . $e->getMessage());
        }
    }

    // ============================================================
    // MÉTODOS ADICIONALES PARA FOTOS
    // ============================================================

    /**
     * Subir foto a un evento existente
     */
    public function subirFoto(Request $request, Evento $evento)
    {
        Log::info('=== INICIO subirFoto ===', ['evento_id' => $evento->id]);

        try {
            $request->validate([
                'foto' => ['required', 'image', 'max:2048'],
                'nombre' => ['nullable', 'string', 'max:255'],
            ]);

            $nombreImagen = $request->nombre ?? 'foto_' . now()->timestamp;
            $path = $request->file('foto')->store('eventos/' . $evento->id . '/fotos', 'public');

            $foto = FotosEvento::create([
                'evento_id' => $evento->id,
                'nombre_imagen' => $nombreImagen,
                'ruta' => $path,
                'usuario_subio' => auth()->id(),
                'fecha_subida' => now(),
            ]);

            Log::info('Foto subida exitosamente', [
                'evento_id' => $evento->id,
                'foto_id' => $foto->id,
                'path' => $path
            ]);

            return response()->json([
                'success' => true,
                'foto' => [
                    'id' => $foto->id,
                    'nombre_imagen' => $foto->nombre_imagen,
                    'url' => asset('storage/' . $foto->ruta),
                    'fecha_subida' => $foto->fecha_subida_formateada,
                ],
                'message' => 'Foto subida correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('ERROR en subirFoto', [
                'evento_id' => $evento->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar foto de un evento
     */
    public function eliminarFoto(Request $request, Evento $evento, $fotoId)
    {
        Log::info('=== INICIO eliminarFoto ===', [
            'evento_id' => $evento->id,
            'foto_id' => $fotoId
        ]);

        try {
            $foto = FotosEvento::where('evento_id', $evento->id)
                ->where('id', $fotoId)
                ->first();

            if (!$foto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto no encontrada'
                ], 404);
            }

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($foto->ruta)) {
                Storage::disk('public')->delete($foto->ruta);
            }

            $foto->delete();

            Log::info('Foto eliminada exitosamente', [
                'evento_id' => $evento->id,
                'foto_id' => $fotoId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('ERROR en eliminarFoto', [
                'evento_id' => $evento->id,
                'foto_id' => $fotoId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la foto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener fotos de un evento (API)
     */
    public function obtenerFotos(Evento $evento)
    {
        $fotos = $evento->fotos()->orderBy('created_at', 'desc')->get()->map(function($foto) {
            return [
                'id' => $foto->id,
                'nombre_imagen' => $foto->nombre_imagen,
                'url' => asset('storage/' . $foto->ruta),
                'fecha_subida' => $foto->fecha_subida_formateada,
                'usuario_subio' => $foto->usuario ? $foto->usuario->name : 'Desconocido',
            ];
        });

        return response()->json([
            'success' => true,
            'fotos' => $fotos,
            'total' => $fotos->count()
        ]);
    }

    // ============================================================
    // MÉTODO PRIVADO PARA ESTADO
    // ============================================================

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