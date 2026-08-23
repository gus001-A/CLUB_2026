<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Creador;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SuscripcionCreadorController extends Controller
{
    /**
     * Descuentos aplicados sobre el precio mensual base, según el plan
     * elegido — entre más largo el compromiso, mayor el descuento.
     */
    private const DESCUENTO_PLAN = [
        'mensual' => 0,
        'trimestral' => 0.10,
        'semestral' => 0.15,
        'anual' => 0.25,
    ];

    private const MESES_PLAN = [
        'mensual' => 1,
        'trimestral' => 3,
        'semestral' => 6,
        'anual' => 12,
    ];

    /**
     * GET /creador/{creador}/{slug}/suscripcion
     */
    public function mostrar(Creador $creador, string $slug)
    {
        $usuario = Auth::user();

        // Verificar si ya está suscrito
        $yaSuscrito = Suscripcion::where('usuario_id', $usuario->id)
            ->where('creador_id', $creador->id)
            ->where('estado', 'activa')
            ->exists();

        if ($yaSuscrito) {
            return redirect()->route('creador.comunidad')
                ->with('info', 'Ya tienes una suscripción activa con este creador.');
        }

        $creador->load('usuario');

        // Obtener avatar del creador
        $avatar = $creador->usuario?->foto_principal ?? $creador->usuario?->avatar ?? null;
        
        // Si el avatar es una ruta de storage, convertir a URL
        if ($avatar && !filter_var($avatar, FILTER_VALIDATE_URL)) {
            $avatar = asset('storage/' . $avatar);
        }

        if (!$avatar) {
            $avatar = '/images/shared/avatar-default.jpg';
        }

        $precioBase = (float) ($creador->precios['suscripcion'] ?? 9.99);

        $planes = collect(self::DESCUENTO_PLAN)->map(function ($descuento, $plan) use ($precioBase) {
            $meses = self::MESES_PLAN[$plan];
            $precioTotal = round($precioBase * $meses * (1 - $descuento), 2);
            return [
                'clave' => $plan,
                'nombre' => ucfirst($plan),
                'meses' => $meses,
                'precio_mensual_base' => $precioBase,
                'descuento_pct' => $descuento * 100,
                'precio_total' => $precioTotal,
                'precio_equivalente_mensual' => round($precioTotal / $meses, 2),
            ];
        })->values();

        // Obtener el nombre del creador para el slug (si no viene en la URL)
        $nombreCreador = $creador->usuario->nombre ?? 'creador';
        $slugGenerado = Str::slug($nombreCreador);

        return Inertia::render('Creador/Suscripcion', [
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'email' => $usuario->email,
                'avatar' => $usuario->avatar,
            ],
            'creador' => [
                'id' => $creador->id,
                'nombre' => $creador->usuario->nombre ?? 'Creador',
                'avatar' => $avatar,
                'biografia' => $creador->biografia ?? 'Creador de contenido exclusivo',
                'categorias' => $creador->categorias ?? [],
                'verificado' => $creador->esta_verificado ?? false,
                'total_suscriptores' => $creador->total_suscriptores ?? 0,
                'total_contenidos' => $creador->total_contenidos ?? 0,
                'slug' => $slugGenerado,
            ],
            'planes' => $planes,
        ]);
    }

    /**
     * POST /creador/{creador}/suscripcion/procesar
     */
    public function procesar(Request $request, Creador $creador)
    {
        $usuario = Auth::user();

        $data = $request->validate([
            'plan' => ['required', 'in:mensual,trimestral,semestral,anual'],
            'metodo_pago' => ['required', 'in:tarjeta,oxxo,mercadopago'],
            // Tarjeta
            'numero_tarjeta' => ['nullable', 'string', 'min:16', 'max:19'],
            'nombre_tarjeta' => ['nullable', 'string', 'max:100'],
            'expiracion' => ['nullable', 'string', 'size:5', 'regex:/^\d{2}\/\d{2}$/'],
            'cvv' => ['nullable', 'string', 'min:3', 'max:4'],
            // OXXO
            'nombre_completo' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            // Mercado Pago
            'email_mercadopago' => ['nullable', 'email', 'max:100'],
        ]);

        // Validar según método de pago
        if ($data['metodo_pago'] === 'tarjeta') {
            $request->validate([
                'numero_tarjeta' => ['required', 'string', 'min:16', 'max:19'],
                'nombre_tarjeta' => ['required', 'string', 'max:100'],
                'expiracion' => ['required', 'string', 'size:5', 'regex:/^\d{2}\/\d{2}$/'],
                'cvv' => ['required', 'string', 'min:3', 'max:4'],
            ]);
        } elseif ($data['metodo_pago'] === 'oxxo') {
            $request->validate([
                'nombre_completo' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:100'],
                'telefono' => ['required', 'string', 'max:20'],
            ]);
        } elseif ($data['metodo_pago'] === 'mercadopago') {
            $request->validate([
                'email_mercadopago' => ['required', 'email', 'max:100'],
            ]);
        }

        $yaSuscrito = Suscripcion::where('usuario_id', $usuario->id)
            ->where('creador_id', $creador->id)
            ->where('estado', 'activa')
            ->exists();

        if ($yaSuscrito) {
            return back()->with('error', 'Ya tienes una suscripción activa con este creador.');
        }

        $precioBase = (float) ($creador->precios['suscripcion'] ?? 9.99);
        $meses = self::MESES_PLAN[$data['plan']];
        $descuento = self::DESCUENTO_PLAN[$data['plan']];
        $precioTotal = round($precioBase * $meses * (1 - $descuento), 2);

        try {
            $suscripcion = DB::transaction(function () use ($usuario, $creador, $data, $precioTotal, $meses) {
                $metadatos = [
                    'metodo_pago' => $data['metodo_pago'],
                    'fecha_pago' => now()->toISOString(),
                ];

                // Guardar datos según método de pago
                if ($data['metodo_pago'] === 'tarjeta') {
                    $metadatos['ultimos_digitos'] = substr(preg_replace('/\s+/', '', $data['numero_tarjeta']), -4);
                    $metadatos['nombre_tarjeta'] = $data['nombre_tarjeta'];
                } elseif ($data['metodo_pago'] === 'oxxo') {
                    $metadatos['nombre_completo'] = $data['nombre_completo'];
                    $metadatos['email'] = $data['email'];
                    $metadatos['telefono'] = $data['telefono'];
                    $metadatos['referencia'] = 'OXXO-' . strtoupper(Str::random(10));
                    $metadatos['fecha_vencimiento'] = now()->addDays(3)->toISOString();
                } elseif ($data['metodo_pago'] === 'mercadopago') {
                    $metadatos['email_mercadopago'] = $data['email_mercadopago'];
                    $metadatos['preference_id'] = 'MP-' . strtoupper(Str::random(15));
                }

                return Suscripcion::create([
                    'creador_id' => $creador->id,
                    'usuario_id' => $usuario->id,
                    'plan' => $data['plan'],
                    'precio' => $precioTotal,
                    'fecha_inicio' => now(),
                    'fecha_renovacion' => now()->addMonths($meses),
                    'estado' => 'activa',
                    'pago_id' => 'SUB-' . strtoupper(Str::random(10)),
                    'metadatos' => $metadatos,
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Error al procesar suscripción:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Ocurrió un error al procesar tu suscripción. Intenta de nuevo.');
        }

        $mensaje = '¡Listo! Ya eres suscriptor. Ahora puedes ver todo el contenido exclusivo.';
        
        if ($data['metodo_pago'] === 'oxxo') {
            $mensaje = '¡Suscripción iniciada! Realiza el pago en OXXO con la referencia proporcionada para activar tu suscripción.';
        } elseif ($data['metodo_pago'] === 'mercadopago') {
            $mensaje = '¡Suscripción iniciada! Serás redirigido a Mercado Pago para completar el pago.';
        }

        return redirect()->route('creador.comunidad')
            ->with('success', $mensaje);
    }
}