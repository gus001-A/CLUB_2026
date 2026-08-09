<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ReservaController extends Controller
{
    /**
     * Muestra el formulario de reserva para un evento
     */
    public function crear($eventoId)
    {
        $evento = Evento::with(['organizador'])
            ->publicados()
            ->findOrFail($eventoId);

        // Verificar si el evento tiene cupos disponibles
        if ($evento->esta_completo) {
            return redirect()->route('eventos.show', $eventoId)
                ->with('error', 'Este evento ya no tiene cupos disponibles');
        }

        // Verificar si el usuario ya tiene una reserva activa
        $reservaExistente = Reserva::where('evento_id', $eventoId)
            ->where('usuario_id', Auth::id())
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->first();

        if ($reservaExistente) {
            return redirect()->route('eventos.show', $eventoId)
                ->with('info', 'Ya tienes una reserva para este evento');
        }

        // Obtener datos del usuario
        $usuario = Auth::user();

        return Inertia::render('Usuario/Eventos/Reservar', [
            'evento' => [
                'id' => $evento->id,
                'titulo' => $evento->nombre,
                'imagen' => $this->getImagenEvento($evento),
                'fecha' => $evento->fecha ? $evento->fecha->format('l d \d\e F') : 'Fecha por confirmar',
                'hora' => $evento->hora_formateada ?? '23:00 hrs',
                'ciudad' => $evento->ciudad ?? 'Ciudad de México',
                'lugaresDisponibles' => $evento->cupos_disponibles,
                'lugaresTotales' => $evento->capacidad ?? 50,
                'precio' => $evento->precio ?? 1290,
                'cargoServicio' => round(($evento->precio ?? 1290) * 0.20), // 20% de cargo por servicio
                'moneda' => $evento->moneda ?? 'MXN',
                'ubicacion' => $evento->ubicacion ?? 'Locación privada',
                'ubicacion_nota' => $evento->ubicacion_nota ?? 'La ubicación exacta se comparte después de la confirmación.',
                'organizador_nombre' => $evento->organizador?->nombre ?? 'Organizador',
                'incluye' => $this->getIncluyeEvento($evento),
            ],
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre ?? $usuario->nickname ?? 'Usuario',
                'email' => $usuario->email,
                'telefono' => $usuario->telefono ?? '',
                'avatar' => $usuario->avatar_url ?? '/images/default-avatar.jpg',
                'verificado' => $usuario->verificado ?? false,
            ],
            'config' => [
                'max_asistentes' => 10,
                'min_asistentes' => 1,
                'tipos_acceso' => ['vip', 'general'],
                'perfiles' => ['personal', 'pareja'],
            ]
        ]);
    }

    /**
     * Procesa la reserva del evento (primer paso - guarda datos de reserva)
     */
    public function store(Request $request, $eventoId)
    {
        $request->validate([
            'num_asistentes' => 'required|integer|min:1|max:10',
            'tipo_acceso' => 'required|in:vip,general',
            'perfil' => 'required|in:personal,pareja',
            'titular.nombre' => 'required|string|max:100',
            'titular.email' => 'required|email|max:100',
            'titular.telefono' => 'required|string|max:20',
            'acompanante.nombre' => 'nullable|string|max:100',
            'acompanante.email' => 'nullable|email|max:100',
            'acompanante.telefono' => 'nullable|string|max:20',
            'comentarios' => 'nullable|string|max:500',
            'terminos' => 'required|accepted',
            'privacidad' => 'required|accepted',
            'reglasEvento' => 'required|accepted',
        ]);

        $evento = Evento::publicados()->findOrFail($eventoId);

        // Verificar disponibilidad
        if ($evento->esta_completo) {
            return back()->with('error', 'Lo sentimos, el evento ya no tiene cupos disponibles');
        }

        // Verificar que haya suficiente cupo
        if ($evento->cupos_disponibles < $request->num_asistentes) {
            return back()->with('error', 'Solo quedan ' . $evento->cupos_disponibles . ' cupos disponibles');
        }

        // Verificar que el usuario no tenga una reserva activa
        $reservaExistente = Reserva::where('evento_id', $eventoId)
            ->where('usuario_id', Auth::id())
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->first();

        if ($reservaExistente) {
            return back()->with('error', 'Ya tienes una reserva activa para este evento');
        }

        try {
            DB::beginTransaction();

            // Calcular total
            $precioUnitario = $evento->precio ?? 1290;
            $cargoServicio = round($precioUnitario * 0.20);
            $total = ($precioUnitario * $request->num_asistentes) + $cargoServicio;

            // Generar folio único
            $folio = $this->generarFolio();

            // Crear la reserva
            $reserva = Reserva::create([
                'evento_id' => $eventoId,
                'usuario_id' => Auth::id(),
                'folio' => $folio,
                'asistentes' => $request->num_asistentes,
                'tipo_acceso' => $request->tipo_acceso,
                'pago_id' => null, // Se actualizará después del pago
                'codigo_qr' => $this->generarCodigoQR($folio),
                'estado' => 'pendiente',
                'total' => $total,
                'metadatos' => [
                    'perfil' => $request->perfil,
                    'titular' => $request->titular,
                    'acompanante' => $request->acompanante,
                    'comentarios' => $request->comentarios,
                    'precio_unitario' => $precioUnitario,
                    'cargo_servicio' => $cargoServicio,
                    'fecha_reserva' => now()->toISOString(),
                    'fecha_expiracion' => now()->addHours(24)->toISOString(),
                ],
            ]);

            // Actualizar cupos disponibles (reserva provisional)
            $evento->decrement('cupos_disponibles', $request->num_asistentes);

            DB::commit();

            // Redirigir a la confirmación/pago
            return redirect()->route('eventos.reserva.pago', $reserva->id)
                ->with('success', '¡Reserva creada! Procede al pago para confirmar.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear reserva:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Ocurrió un error al procesar tu reserva. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Muestra la página de pago
     */
    public function pago($reservaId)
    {
        $reserva = Reserva::with(['evento', 'usuario'])
            ->where('usuario_id', Auth::id())
            ->findOrFail($reservaId);

        // Verificar que la reserva esté pendiente
        if ($reserva->estado !== 'pendiente') {
            return redirect()->route('eventos.reservas')
                ->with('info', 'Esta reserva ya ha sido procesada');
        }

        // Verificar si la reserva expiró
        $fechaExpiracion = $reserva->metadatos['fecha_expiracion'] ?? null;
        if ($fechaExpiracion && now()->gt($fechaExpiracion)) {
            $reserva->estado = 'cancelada';
            $reserva->save();
            return redirect()->route('eventos.reservas')
                ->with('error', 'La reserva ha expirado. Por favor, realiza una nueva reserva.');
        }

        $evento = $reserva->evento;

        return Inertia::render('Usuario/Eventos/Pago', [
            'reserva' => [
                'id' => $reserva->id,
                'folio' => $reserva->folio,
                'asistentes' => $reserva->asistentes,
                'tipo_acceso' => $reserva->tipo_acceso,
                'total' => $reserva->total,
                'estado' => $reserva->estado,
                'moneda' => $evento->moneda ?? 'MXN',
                'metadatos' => $reserva->metadatos,
            ],
            'evento' => [
                'id' => $evento->id,
                'titulo' => $evento->nombre,
                'fecha' => $evento->fecha ? $evento->fecha->format('l d \d\e F') : 'Fecha por confirmar',
                'hora' => $evento->hora_formateada ?? '23:00 hrs',
                'ciudad' => $evento->ciudad ?? 'Ciudad de México',
                'imagen' => $this->getImagenEvento($evento),
                'ubicacion' => $evento->ubicacion ?? 'Locación privada',
                'ubicacion_nota' => $evento->ubicacion_nota ?? 'La ubicación exacta se comparte después de la confirmación.',
            ],
            'usuario' => [
                'nombre' => Auth::user()->nombre ?? Auth::user()->nickname ?? 'Usuario',
                'email' => Auth::user()->email,
            ],
            'config' => [
                'metodos_pago' => ['tarjeta', 'oxxo', 'paypal'],
                'cargo_servicio' => round(($evento->precio ?? 1290) * 0.20),
            ]
        ]);
    }

    /**
     * Procesa el pago y confirma la reserva
     */
    public function procesarPago(Request $request, $reservaId)
    {
        $request->validate([
            'numero_tarjeta' => 'required|string|min:16|max:19',
            'nombre_tarjeta' => 'required|string|max:100',
            'expiracion' => 'required|string|size:7|regex:/^\d{2}\/\d{2}$/',
            'cvv' => 'required|string|min:3|max:4',
        ]);

        $reserva = Reserva::with(['evento'])
            ->where('usuario_id', Auth::id())
            ->where('estado', 'pendiente')
            ->findOrFail($reservaId);

        // Verificar si la reserva expiró
        $fechaExpiracion = $reserva->metadatos['fecha_expiracion'] ?? null;
        if ($fechaExpiracion && now()->gt($fechaExpiracion)) {
            $reserva->estado = 'cancelada';
            $reserva->save();
            return back()->with('error', 'La reserva ha expirado. Por favor, realiza una nueva reserva.');
        }

        try {
            DB::beginTransaction();

            // Aquí iría la lógica de procesamiento de pago con el gateway
            // Por ahora simulamos el pago exitoso
            $pagoId = 'PAY-' . strtoupper(Str::random(10));

            // Actualizar la reserva
            $reserva->pago_id = $pagoId;
            $reserva->estado = 'aprobada';
            
            // Agregar datos de pago a metadatos
            $metadatos = $reserva->metadatos ?? [];
            $metadatos['pago'] = [
                'fecha' => now()->toISOString(),
                'ultimos_digitos' => substr($request->numero_tarjeta, -4),
                'tipo' => 'tarjeta',
                'referencia' => $pagoId,
            ];
            $reserva->metadatos = $metadatos;
            $reserva->save();

            DB::commit();

            return redirect()->route('eventos.reserva.exito', $reserva->id)
                ->with('success', '¡Pago exitoso! Tu reserva ha sido confirmada.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al procesar pago:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Ocurrió un error al procesar el pago. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Muestra la página de éxito después del pago
     */
    public function exito($reservaId)
    {
        $reserva = Reserva::with(['evento', 'usuario'])
            ->where('usuario_id', Auth::id())
            ->where('estado', 'aprobada')
            ->findOrFail($reservaId);

        $evento = $reserva->evento;

        return Inertia::render('Usuario/Eventos/ReservaExitosa', [
            'reserva' => [
                'id' => $reserva->id,
                'folio' => $reserva->folio,
                'codigo_qr' => $reserva->codigo_qr,
                'asistentes' => $reserva->asistentes,
                'tipo_acceso' => $reserva->tipo_acceso,
                'total' => $reserva->total,
                'estado' => $reserva->estado,
                'fecha_reserva' => $reserva->created_at->format('d/m/Y H:i'),
                'moneda' => $evento->moneda ?? 'MXN',
                'metadatos' => $reserva->metadatos,
            ],
            'evento' => [
                'id' => $evento->id,
                'titulo' => $evento->nombre,
                'fecha' => $evento->fecha ? $evento->fecha->format('l d \d\e F') : 'Fecha por confirmar',
                'hora' => $evento->hora_formateada ?? '23:00 hrs',
                'ciudad' => $evento->ciudad ?? 'Ciudad de México',
                'ubicacion' => $evento->ubicacion ?? 'Por confirmar',
                'ubicacion_nota' => $evento->ubicacion_nota ?? 'La ubicación exacta se enviará por correo 24 horas antes del evento.',
                'imagen' => $this->getImagenEvento($evento),
            ],
            'pasos' => [
                [
                    'titulo' => 'Reserva confirmada',
                    'descripcion' => 'Tu pago ha sido procesado exitosamente y tu lugar está asegurado.',
                    'icono' => 'check-circle',
                    'completado' => true,
                ],
                [
                    'titulo' => 'Revisa tu correo',
                    'descripcion' => 'Hemos enviado los detalles de tu reserva a tu correo electrónico.',
                    'icono' => 'mail',
                    'completado' => true,
                ],
                [
                    'titulo' => 'Detalles finales',
                    'descripcion' => 'Recibirás la ubicación exacta 24 horas antes del evento.',
                    'icono' => 'map-pin',
                    'completado' => false,
                ],
            ]
        ]);
    }

    /**
     * Obtiene las reservas del usuario
     */
    public function misReservas()
    {
        $reservas = Reserva::with(['evento'])
            ->where('usuario_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reserva) {
                $evento = $reserva->evento;
                return [
                    'id' => $reserva->id,
                    'folio' => $reserva->folio,
                    'codigo_qr' => $reserva->codigo_qr,
                    'evento' => [
                        'id' => $evento->id,
                        'titulo' => $evento->nombre,
                        'fecha' => $evento->fecha ? $evento->fecha->format('d/m/Y') : '--/--/----',
                        'hora' => $evento->hora_formateada ?? '--:--',
                        'ciudad' => $evento->ciudad ?? 'Ciudad de México',
                        'imagen' => $this->getImagenEvento($evento),
                    ],
                    'asistentes' => $reserva->asistentes,
                    'tipo_acceso' => $reserva->tipo_acceso,
                    'total' => $reserva->total,
                    'estado' => $reserva->estado,
                    'fecha_reserva' => $reserva->created_at->format('d/m/Y H:i'),
                    'moneda' => $evento->moneda ?? 'MXN',
                ];
            });

        $estadisticas = [
            'total' => $reservas->count(),
            'pendientes' => $reservas->where('estado', 'pendiente')->count(),
            'aprobadas' => $reservas->where('estado', 'aprobada')->count(),
            'canceladas' => $reservas->where('estado', 'cancelada')->count(),
        ];

        return Inertia::render('Usuario/Eventos/MisReservas', [
            'reservas' => $reservas,
            'estadisticas' => $estadisticas,
        ]);
    }

    /**
     * Cancela una reserva
     */
    public function cancelar($reservaId)
    {
        $reserva = Reserva::where('usuario_id', Auth::id())
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->findOrFail($reservaId);

        try {
            DB::beginTransaction();

            // Devolver los cupos al evento si la reserva estaba aprobada o pendiente
            if ($reserva->evento) {
                $reserva->evento->increment('cupos_disponibles', $reserva->asistentes);
            }

            $reserva->estado = 'cancelada';
            
            // Agregar datos de cancelación a metadatos
            $metadatos = $reserva->metadatos ?? [];
            $metadatos['cancelacion'] = [
                'fecha' => now()->toISOString(),
                'motivo' => 'Cancelado por el usuario',
            ];
            $reserva->metadatos = $metadatos;
            $reserva->save();

            DB::commit();

            return redirect()->route('eventos.reservas')
                ->with('success', 'Reserva cancelada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al cancelar reserva:', ['error' => $e->getMessage()]);
            return back()->with('error', 'No se pudo cancelar la reserva');
        }
    }

    /**
     * Verifica la disponibilidad de un evento
     */
    public function verificarDisponibilidad($eventoId)
    {
        $evento = Evento::publicados()->findOrFail($eventoId);

        return response()->json([
            'disponible' => !$evento->esta_completo,
            'cupos_disponibles' => $evento->cupos_disponibles,
            'capacidad' => $evento->capacidad ?? 50,
            'porcentaje' => $evento->capacidad > 0 
                ? round((($evento->capacidad - $evento->cupos_disponibles) / $evento->capacidad) * 100)
                : 0,
        ]);
    }

    /**
     * Obtener la imagen del evento
     */
    private function getImagenEvento($evento)
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
     * Genera un folio único para la reserva
     */
    private function generarFolio()
    {
        $prefix = 'CLUB';
        $date = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        $unique = $prefix . '-' . $date . '-' . $random;
        
        // Verificar que no exista
        while (Reserva::where('folio', $unique)->exists()) {
            $random = strtoupper(Str::random(4));
            $unique = $prefix . '-' . $date . '-' . $random;
        }
        
        return $unique;
    }

    /**
     * Genera un código QR para la reserva
     */
    private function generarCodigoQR($folio)
    {
        // Generar un hash único para el QR
        $data = $folio . '|' . now()->timestamp;
        return base64_encode(hash('sha256', $data, true));
    }

    /**
     * Obtiene los ítems incluidos en el evento
     */
    private function getIncluyeEvento($evento)
    {
        $incluye = [];
        
        // Elementos base
        if ($evento->incluye_cocktail ?? true) {
            $incluye[] = ['icon' => 'pi-th-large', 'texto' => 'Cóctel de bienvenida'];
        }
        if ($evento->incluye_musica ?? true) {
            $incluye[] = ['icon' => 'pi-circle', 'texto' => 'Música en vivo & DJ'];
        }
        if ($evento->incluye_seguridad ?? true) {
            $incluye[] = ['icon' => 'pi-shield', 'texto' => 'Seguridad y discreción'];
        }
        if ($evento->incluye_networking ?? true) {
            $incluye[] = ['icon' => 'pi-users', 'texto' => 'Networking selecto'];
        }
        if ($evento->incluye_lounge ?? true) {
            $incluye[] = ['icon' => 'pi-directions-alt', 'texto' => 'Áreas privadas y lounge'];
        }
        
        // Si no hay elementos personalizados, usar los predeterminados
        if (empty($incluye)) {
            $incluye = [
                ['icon' => 'pi-th-large', 'texto' => 'Cóctel de bienvenida'],
                ['icon' => 'pi-circle', 'texto' => 'Música en vivo & DJ'],
                ['icon' => 'pi-shield', 'texto' => 'Seguridad y discreción'],
                ['icon' => 'pi-users', 'texto' => 'Networking selecto'],
                ['icon' => 'pi-directions-alt', 'texto' => 'Áreas privadas y lounge'],
            ];
        }
        
        return $incluye;
    }
}