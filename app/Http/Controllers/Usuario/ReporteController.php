<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReporteController extends Controller
{
    /**
     * Catálogo de motivos válidos — la fuente de verdad la comparten el
     * backend (para validar) y el frontend (para pintar las opciones del
     * modal). Si agregas un motivo nuevo, agrégalo también en el array
     * `motivosReporte` de Mensajes.vue.
     */
    public const MOTIVOS_VALIDOS = [
        'spam',
        'lenguaje_inapropiado',
        'menor_edad',
        'acoso',
        'perfil_falso',
        'contenido_no_solicitado',
        'amenazas',
        'estafa',
        'informacion_privada',
        'discriminacion',
        'venta_no_autorizada',
        'otro',
    ];

    /**
     * POST /reportes
     * Reporta a otro usuario (por ahora, pensado para usarse desde el
     * chat — reportable_type/reportable_id apuntan al propio usuario
     * reportado, usando la relación polimórfica ya definida en el modelo).
     */
    public function store(Request $request)
    {
        $reportante = Auth::user();

        $data = $request->validate([
            'reportado_id' => ['required', 'integer', 'exists:users,id'],
            'tipo' => ['required', 'string', 'in:' . implode(',', self::MOTIVOS_VALIDOS)],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        if ((int) $data['reportado_id'] === $reportante->id) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes reportarte a ti mismo.',
            ], 422);
        }

        // Evita que la misma persona mande 10 reportes duplicados seguidos
        // contra el mismo usuario mientras el primero sigue sin revisarse.
        $yaExisteUnoPendiente = Reporte::where('reporta_id', $reportante->id)
            ->where('reportado_id', $data['reportado_id'])
            ->where('estado', 'pendiente')
            ->exists();

        if ($yaExisteUnoPendiente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes un reporte pendiente contra este usuario. Nuestro equipo lo está revisando.',
            ], 422);
        }

        try {
            $reportado = User::findOrFail($data['reportado_id']);

            $reporte = Reporte::create([
                'reporta_id' => $reportante->id,
                'reportado_id' => $reportado->id,
                'reportable_type' => User::class,
                'reportable_id' => $reportado->id,
                'tipo' => $data['tipo'],
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => 'pendiente',
                'metadatos' => [
                    'origen' => 'chat',
                    'reportado_en' => now()->toISOString(),
                ],
            ]);

            Log::info('🚩 Nuevo reporte de usuario', [
                'reporte_id' => $reporte->id,
                'reporta_id' => $reportante->id,
                'reportado_id' => $reportado->id,
                'tipo' => $data['tipo'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reporte enviado. Gracias por ayudarnos a mantener la comunidad segura.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error al crear reporte', [
                'message' => $e->getMessage(),
                'reporta_id' => $reportante->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el reporte. Intenta de nuevo.',
            ], 500);
        }
    }
}