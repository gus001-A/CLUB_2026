<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\CodigoInvitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Autoservicio de invitaciones para usuarios normales — a diferencia de
 * App\Http\Controllers\Admin\InvitacionController (que usaste como
 * referencia), aquí NO se manda a un destinatario específico ni requiere
 * guard de admin: cualquier usuario logueado puede generar tantos códigos
 * como quiera (dentro de un límite razonable por solicitud) para
 * compartirlos con quien quiera.
 *
 * Como CodigoInvitacion no tiene una columna dedicada para "qué usuario
 * normal lo generó" (solo 'creado_por_admin_id', que apunta a
 * Administrador), la autoría se guarda dentro de la columna 'metadata'
 * (ya es un array/JSON — el mismo patrón que el controlador de admin ya
 * usa para guardar 'tipo', 'telefono', 'mensaje').
 */
class InvitacionController extends Controller
{
    /** Cuántos códigos como máximo se pueden generar de un solo golpe. */
    private const MAX_POR_LOTE = 20;

    public function index(): Response
    {
        $usuario = Auth::user();

        $codigos = CodigoInvitacion::where('metadata->creado_por_usuario_id', $usuario->id)
            ->latest()
            ->get()
            ->map(fn (CodigoInvitacion $c) => $this->formatearCodigo($c));

        return Inertia::render('Usuario/Invitaciones', [
            'codigos' => $codigos,
            'stats' => [
                'total' => $codigos->count(),
                'disponibles' => $codigos->where('estado', 'Disponible')->count(),
                'usados' => $codigos->whereIn('estado', ['Usado', 'Agotado'])->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();

        $data = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1', 'max:' . self::MAX_POR_LOTE],
            'vigencia_dias' => ['required', 'integer', 'min:1', 'max:365'],
            'usos_maximos' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $generados = [];

        for ($i = 0; $i < $data['cantidad']; $i++) {
            $generados[] = CodigoInvitacion::create([
                'expira_en' => now()->addDays($data['vigencia_dias']),
                'usos_maximos' => $data['usos_maximos'],
                'metadata' => [
                    'tipo' => 'referido',
                    'creado_por_usuario_id' => $usuario->id,
                ],
            ]);
        }

        $mensaje = count($generados) === 1
            ? "¡Listo! Tu código de invitación: {$generados[0]->codigo}"
            : count($generados) . ' códigos de invitación generados correctamente.';

        return back()->with('success', $mensaje);
    }

    public function destroy(CodigoInvitacion $invitacion)
    {
        $usuario = Auth::user();

        abort_unless(
            ($invitacion->metadata['creado_por_usuario_id'] ?? null) === $usuario->id,
            403,
            'No puedes desactivar un código que no generaste tú.'
        );

        $invitacion->update(['esta_activo' => false]);

        return back()->with('success', 'Código desactivado.');
    }

    private function formatearCodigo(CodigoInvitacion $c): array
    {
        return [
            'id' => $c->id,
            'codigo' => $c->codigo,
            'url' => url("/invitar/{$c->codigo}"),
            'usos' => $c->contador_usos,
            'usos_maximos' => $c->usos_maximos,
            'expira_en' => $c->expira_en,
            'dias_restantes' => $c->dias_restantes,
            'created_at' => $c->created_at,
            // Reutiliza los accesores que ya tiene el modelo — mismo
            // criterio de estado que usa el panel de admin, sin duplicar lógica.
            'estado' => $c->etiqueta_estado,
            'estado_color' => $c->estado_color,
        ];
    }
}