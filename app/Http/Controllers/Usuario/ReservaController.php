<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Reserva;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

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

        // Formatear fecha en ESPAÑOL
        $fechaFormateada = 'Fecha por confirmar';
        $fechaCompleta = 'Fecha por confirmar';
        
        if ($evento->fecha) {
            Carbon::setLocale('es');
            $fechaCarbon = Carbon::parse($evento->fecha);
            $fechaFormateada = ucfirst($fechaCarbon->translatedFormat('l j \d\e F \d\e Y'));
            $fechaCompleta = $fechaCarbon->format('d/m/Y');
        }

        return Inertia::render('Usuario/Eventos/Reservar', [
            'evento' => [
                'id' => $evento->id,
                'titulo' => $evento->nombre,
                'descripcion' => $evento->descripcion ?? 'Descripción del evento',
                'imagen' => $this->getImagenEvento($evento),
                'fecha' => $fechaFormateada,
                'fecha_completa' => $fechaCompleta,
                'hora' => $evento->hora_formateada ?? '23:00 hrs',
                'hora_completa' => $evento->hora ? $evento->hora->format('H:i') : '23:00',
                'ciudad' => $evento->ciudad ?? 'Ciudad de México',
                'zona_ubicacion' => $evento->zona_ubicacion ?? 'Zona exclusiva',
                'ubicacion_lat' => $evento->ubicacion_lat,
                'ubicacion_lng' => $evento->ubicacion_lng,
                'lugaresDisponibles' => $evento->cupos_disponibles,
                'lugaresTotales' => $evento->capacidad ?? 50,
                'precio' => $evento->precio ?? 1290,
                'cargoServicio' => round(($evento->precio ?? 1290) * 0.20),
                'moneda' => 'MXN',
                'tipo' => $evento->tipo ?? 'social',
                'categoria' => $evento->categoria ?? 'experiencia',
                'codigo_vestimenta' => $evento->codigo_vestimenta ?? 'Smart casual / Formal',
                'ubicacion' => $evento->ubicacion ?? 'Locación privada',
                'ubicacion_nota' => $evento->ubicacion_nota ?? 'La ubicación exacta se comparte después de la confirmación.',
                'organizador_nombre' => $evento->organizador?->nombre ?? 'Club Social',
                'organizador_avatar' => $evento->organizador?->avatar_url ?? '/images/default-avatar.jpg',
                'incluye' => $this->getIncluyeEvento($evento),
                'estado_actual' => $evento->estado_actual,
                'destacado' => $evento->destacado ?? false,
                'metadatos' => $evento->metadatos ?? [],
            ],
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre ?? $usuario->nickname ?? 'Usuario',
                'email' => $usuario->email,
                'telefono' => $usuario->telefono ?? '',
                'avatar' => $this->getAvatarUrl($usuario),
                'verificado' => $usuario->verificado ?? false,
            ],
            'config' => [
                'max_asistentes' => 10,
                'min_asistentes' => 1,
                'tipos_acceso' => ['vip', 'general'],
                'perfiles' => ['personal', 'pareja'],
                'cargo_servicio' => round(($evento->precio ?? 1290) * 0.20),
            ]
        ]);
    }

    /**
     * 🔥 PROCESA EL PAGO Y CREA LA RESERVA
     */
    public function procesarPago(Request $request)
    {
        $request->validate([
            'evento_id' => 'required|exists:eventos,id',
            'num_asistentes' => 'required|integer|min:1|max:10',
            'tipo_acceso' => 'required|in:vip,general',
            'titular_nombre' => 'required|string|max:100',
            'titular_email' => 'required|email|max:100',
            'titular_telefono' => 'required|string|max:20',
            'acompanantes' => 'nullable|string',
            'comentarios' => 'nullable|string|max:500',
            'metodo' => 'required|string|in:tarjeta,oxxo',
            'numero_tarjeta' => 'required_if:metodo,tarjeta|string|min:16|max:19',
            'nombre_tarjeta' => 'required_if:metodo,tarjeta|string|max:100',
            'expiracion' => 'required_if:metodo,tarjeta|string|size:5|regex:/^\d{2}\/\d{2}$/',
            'cvv' => 'required_if:metodo,tarjeta|string|min:3|max:4',
            'total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'cargo_servicio' => 'required|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        $evento = Evento::publicados()->findOrFail($request->evento_id);

        // Verificar disponibilidad
        if ($evento->esta_completo) {
            return back()->with('error', 'Lo sentimos, el evento ya no tiene cupos disponibles');
        }

        if ($evento->cupos_disponibles < $request->num_asistentes) {
            return back()->with('error', 'Solo quedan ' . $evento->cupos_disponibles . ' cupos disponibles');
        }

        // Verificar que el usuario no tenga una reserva activa
        $reservaExistente = Reserva::where('evento_id', $request->evento_id)
            ->where('usuario_id', Auth::id())
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->first();

        if ($reservaExistente) {
            return back()->with('error', 'Ya tienes una reserva activa para este evento');
        }

        try {
            DB::beginTransaction();

            $pagoExitoso = true;
            
            if (!$pagoExitoso) {
                return back()->with('error', 'El pago fue rechazado. Por favor, intenta con otro método.');
            }

            $pagoId = 'PAY-' . strtoupper(Str::random(10));
            $folio = $this->generarFolio();

            // 🔥 Decodificar acompañantes si vienen como JSON
            $acompanantes = [];
            if ($request->acompanantes) {
                $acompanantes = json_decode($request->acompanantes, true);
                if (!is_array($acompanantes)) {
                    $acompanantes = [];
                }
            }

            // 🔥 Generar código QR usando el folio
            $codigoQr = $this->generarCodigoQR($folio);

            // 🔥 CREAR LA RESERVA
            $reserva = Reserva::create([
                'evento_id' => $request->evento_id,
                'usuario_id' => Auth::id(),
                'folio' => $folio,
                'asistentes' => $request->num_asistentes,
                'tipo_acceso' => $request->tipo_acceso,
                'pago_id' => $pagoId,
                'codigo_qr' => $codigoQr, // 🔥 Guardamos el código QR generado
                'estado' => 'aprobada',
                'total' => $request->total,
                'metadatos' => [
                    'titular' => [
                        'nombre' => $request->titular_nombre,
                        'email' => $request->titular_email,
                        'telefono' => $request->titular_telefono,
                    ],
                    'acompanantes' => $acompanantes,
                    'comentarios' => $request->comentarios,
                    'precio_unitario' => $request->precio_unitario,
                    'cargo_servicio' => $request->cargo_servicio,
                    'subtotal' => $request->subtotal,
                    'fecha_reserva' => now()->toISOString(),
                    'pago' => [
                        'fecha' => now()->toISOString(),
                        'ultimos_digitos' => $request->metodo === 'tarjeta' ? substr($request->numero_tarjeta, -4) : null,
                        'tipo' => $request->metodo,
                        'referencia' => $pagoId,
                    ],
                ],
            ]);

            // Actualizar cupos disponibles
            $evento->decrement('cupos_disponibles', $request->num_asistentes);

            DB::commit();

            return redirect()->route('eventos.reserva.comprobante', $reserva->id)
                ->with('success', '¡Pago exitoso! Tu reserva ha sido confirmada.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al procesar pago:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 NUEVO: Muestra el comprobante de una reserva exitosa
     */
    public function comprobante($reservaId)
    {
        $reserva = Reserva::with(['evento', 'usuario'])
            ->where('usuario_id', Auth::id())
            ->where('estado', 'aprobada')
            ->findOrFail($reservaId);

        $evento = $reserva->evento;

        // 🔥 Formatear fecha correctamente
        $fechaFormateada = 'Fecha por confirmar';
        if ($evento->fecha) {
            Carbon::setLocale('es');
            $fechaCarbon = Carbon::parse($evento->fecha);
            $fechaFormateada = ucfirst($fechaCarbon->translatedFormat('l j \d\e F \d\e Y'));
        }

        return Inertia::render('Usuario/Eventos/Comprobante', [
            'reserva' => [
                'id' => $reserva->id,
                'folio' => $reserva->folio,
                'codigo_qr' => $reserva->codigo_qr, // 🔥 Enviamos el código QR guardado
                'asistentes' => $reserva->asistentes,
                'tipo_acceso' => $reserva->tipo_acceso,
                'total' => $reserva->total,
                'estado' => $reserva->estado,
                'fecha_reserva' => $reserva->created_at->format('d/m/Y H:i'),
                'moneda' => 'MXN',
                'metadatos' => $reserva->metadatos,
            ],
            'evento' => [
                'id' => $evento->id,
                'titulo' => $evento->nombre,
                'descripcion' => $evento->descripcion ?? 'Descripción del evento',
                'fecha' => $fechaFormateada,
                'fecha_original' => $evento->fecha,
                'hora' => $evento->hora_formateada ?? '23:00 hrs',
                'ciudad' => $evento->ciudad ?? 'Ciudad de México',
                'ubicacion' => $evento->ubicacion ?? 'Por confirmar',
                'ubicacion_detalle' => $evento->ubicacion_detalle ?? $evento->zona_ubicacion ?? '',
                'colonia' => $evento->colonia ?? '',
                'codigo_postal' => $evento->codigo_postal ?? '',
                'ubicacion_nota' => $evento->ubicacion_nota ?? 'La ubicación exacta se enviará por correo 24 horas antes del evento.',
                'imagen' => $this->getImagenEvento($evento),
                'codigo_vestimenta' => $evento->codigo_vestimenta ?? 'Smart casual / Formal',
                'tipo' => $evento->tipo ?? 'social',
            ]
        ]);
    }

    /**
     * 🔥 NUEVO: Exportar comprobante en PDF
     */
    public function exportarPdf($reservaId)
    {
        $reserva = Reserva::with(['evento', 'usuario'])
            ->where('usuario_id', Auth::id())
            ->where('estado', 'aprobada')
            ->findOrFail($reservaId);

        $evento = $reserva->evento;

        // Formatear fecha
        Carbon::setLocale('es');
        $fechaCarbon = Carbon::parse($evento->fecha);
        $fechaFormateada = ucfirst($fechaCarbon->translatedFormat('l j \d\e F \d\e Y'));

        // Obtener datos del titular
        $titularNombre = $reserva->metadatos['titular']['nombre'] ?? $reserva->usuario->nombre ?? 'No especificado';
        
        // Obtener nombres de acompañantes
        $nombresAcompanantes = 'Ninguno';
        if (!empty($reserva->metadatos['acompanantes'])) {
            $nombres = array_column($reserva->metadatos['acompanantes'], 'nombre');
            $nombresFiltrados = array_filter($nombres);
            $nombresAcompanantes = !empty($nombresFiltrados) ? implode(', ', $nombresFiltrados) : 'Ninguno';
        }

        // Perfil de acompañante
        $perfilAcompanante = match($reserva->asistentes) {
            1 => 'Solo',
            2 => 'Pareja',
            default => 'Grupo'
        };

        // Método de pago
        $metodoPago = 'Tarjeta';
        if (isset($reserva->metadatos['pago']['tipo'])) {
            $metodoPago = match($reserva->metadatos['pago']['tipo']) {
                'oxxo' => 'OXXO',
                'paypal' => 'PayPal',
                default => 'Tarjeta terminada en ' . ($reserva->metadatos['pago']['ultimos_digitos'] ?? '****')
            };
        }

        // Calcular totales
        $precioUnitario = $reserva->metadatos['precio_unitario'] ?? 0;
        $cargoServicio = $reserva->metadatos['cargo_servicio'] ?? 0;
        $subtotal = $precioUnitario * $reserva->asistentes;

        // 🔥 Generar QR Code usando el código guardado
        $qrCode = $this->generarQRCodeParaPDF($reserva->codigo_qr, $reserva->folio);

        $data = [
            'reserva' => $reserva,
            'evento' => $evento,
            'fechaFormateada' => $fechaFormateada,
            'titularNombre' => $titularNombre,
            'nombresAcompanantes' => $nombresAcompanantes,
            'perfilAcompanante' => $perfilAcompanante,
            'metodoPago' => $metodoPago,
            'precioUnitario' => $precioUnitario,
            'cargoServicio' => $cargoServicio,
            'subtotal' => $subtotal,
            'qrCode' => $qrCode,
        ];

        $pdf = Pdf::loadView('pdf.reserva', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 150,
            'defaultPaperSize' => 'a4',
        ]);

        return $pdf->download('Comprobante_Reserva_' . $reserva->folio . '.pdf');
    }

    /**
     * 🔥 NUEVO: Método para cancelar una reserva
     */
    public function cancelar($reservaId)
    {
        $reserva = Reserva::where('usuario_id', Auth::id())
            ->where('estado', 'aprobada')
            ->findOrFail($reservaId);

        try {
            DB::beginTransaction();

            // Devolver cupos al evento
            $reserva->evento->increment('cupos_disponibles', $reserva->asistentes);

            // Actualizar estado de la reserva
            $reserva->update(['estado' => 'cancelada']);

            DB::commit();

            return redirect()->route('eventos.show', $reserva->evento_id)
                ->with('success', 'Reserva cancelada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al cancelar reserva:', [
                'error' => $e->getMessage(),
                'reserva_id' => $reservaId
            ]);
            
            return back()->with('error', 'Error al cancelar la reserva.');
        }
    }

    /**
     * 🔥 NUEVO: Obtener URL del avatar correctamente
     */
    private function getAvatarUrl($usuario)
    {
        if (!$usuario) {
            return '/images/shared/avatar-default.jpg';
        }

        $avatar = $usuario->avatar ?? null;
        
        if (!$avatar) {
            return '/images/shared/avatar-default.jpg';
        }

        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        if (str_starts_with($avatar, 'storage/') || str_starts_with($avatar, '/storage/')) {
            return asset($avatar);
        }

        return asset('storage/' . ltrim($avatar, '/'));
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
        // 🔥 Generamos una URL de QR usando el folio
        return 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=0&data=' . urlencode($folio);
    }

    /**
     * Genera código QR en base64 para el PDF
     */
    private function generarQRCodeParaPDF($codigoQr, $folio)
    {
        // 🔥 Si tenemos un código QR guardado, lo usamos
        if ($codigoQr && filter_var($codigoQr, FILTER_VALIDATE_URL)) {
            try {
                if (function_exists('curl_init')) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $codigoQr);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $imageData = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    if ($httpCode === 200 && $imageData) {
                        return base64_encode($imageData);
                    }
                }
                
                $imageData = @file_get_contents($codigoQr);
                if ($imageData) {
                    return base64_encode($imageData);
                }
            } catch (\Exception $e) {
                \Log::warning('Error generando QR para PDF:', ['error' => $e->getMessage(), 'folio' => $folio]);
            }
        }
        
        // Fallback: generar con API
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=10&data=' . urlencode($folio);
        try {
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $imageData = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200 && $imageData) {
                    return base64_encode($imageData);
                }
            }
            
            $imageData = @file_get_contents($url);
            if ($imageData) {
                return base64_encode($imageData);
            }
        } catch (\Exception $e) {
            \Log::warning('Error generando QR para PDF (fallback):', ['error' => $e->getMessage(), 'folio' => $folio]);
        }
        
        return null;
    }

    /**
     * Obtiene los ítems incluidos en el evento
     */
    private function getIncluyeEvento($evento)
    {
        $incluye = [];
        
        if ($evento->incluye_cocktail ?? true) {
            $incluye[] = ['icon' => 'pi-glass', 'texto' => 'Cóctel de bienvenida'];
        }
        if ($evento->incluye_musica ?? true) {
            $incluye[] = ['icon' => 'pi-volume-up', 'texto' => 'Música en vivo & DJ'];
        }
        if ($evento->incluye_seguridad ?? true) {
            $incluye[] = ['icon' => 'pi-shield', 'texto' => 'Seguridad y discreción'];
        }
        if ($evento->incluye_networking ?? true) {
            $incluye[] = ['icon' => 'pi-users', 'texto' => 'Networking selecto'];
        }
        if ($evento->incluye_lounge ?? true) {
            $incluye[] = ['icon' => 'pi-home', 'texto' => 'Áreas privadas y lounge'];
        }
        if ($evento->incluye_estacionamiento ?? true) {
            $incluye[] = ['icon' => 'pi-car', 'texto' => 'Estacionamiento VIP'];
        }
        if ($evento->incluye_fotografo ?? true) {
            $incluye[] = ['icon' => 'pi-camera', 'texto' => 'Fotógrafo profesional'];
        }
        if ($evento->incluye_barra_libre ?? true) {
            $incluye[] = ['icon' => 'pi-wine', 'texto' => 'Barra libre premium'];
        }
        
        if (empty($incluye)) {
            $incluye = [
                ['icon' => 'pi-glass', 'texto' => 'Cóctel de bienvenida'],
                ['icon' => 'pi-volume-up', 'texto' => 'Música en vivo & DJ'],
                ['icon' => 'pi-shield', 'texto' => 'Seguridad y discreción'],
                ['icon' => 'pi-users', 'texto' => 'Networking selecto'],
                ['icon' => 'pi-home', 'texto' => 'Áreas privadas y lounge'],
                ['icon' => 'pi-car', 'texto' => 'Estacionamiento VIP'],
                ['icon' => 'pi-camera', 'texto' => 'Fotógrafo profesional'],
                ['icon' => 'pi-wine', 'texto' => 'Barra libre premium'],
            ];
        }
        
        return $incluye;
    }
}