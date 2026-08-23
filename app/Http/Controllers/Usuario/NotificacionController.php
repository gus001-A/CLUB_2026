<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class NotificacionController extends Controller
{
    /**
     * GET /notificaciones
     * Usado por el panel desplegable (togglePanelNotificaciones -> obtenerNotificaciones).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $notificaciones = Notificacion::where('usuario_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn (Notificacion $n) => $n->toFeedPayload());

        $noLeidas = Notificacion::where('usuario_id', $user->id)->noLeidas()->count();

        // 🔎 DIAGNÓSTICO — bórralo una vez que confirmes qué estaba pasando.
        // Revisa storage/logs/laravel.log después de recargar el panel de
        // notificaciones: te dice EXACTAMENTE qué usuario_id se está
        // consultando, cuántas filas encontró, y si la petición se
        // reconoció como JSON o como visita normal de página.
        Log::info('🔔 NotificacionController@index', [
            'usuario_autenticado_id' => $user->id,
            'usuario_autenticado_nombre' => $user->nombre ?? $user->apodo ?? null,
            'notificaciones_encontradas' => $notificaciones->count(),
            'no_leidas' => $noLeidas,
            'wantsJson' => $request->wantsJson(),
            'ajax' => $request->ajax(),
            'accept_header' => $request->header('Accept'),
            'x_requested_with' => $request->header('X-Requested-With'),
        ]);

        // Si la petición pide JSON (el panel desplegable usa axios), respondemos JSON.
        // Si es una visita normal de página (el link "Ver todas las notificaciones"),
        // renderizamos una página completa.
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'notificaciones' => $notificaciones,
                'no_leidas' => $noLeidas,
            ]);
        }

        return Inertia::render('Usuario/Notificaciones', [
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apodo' => $user->apodo,
                'avatar' => $user->avatar,
                'rol' => $user->rol ?? 'usuario',
            ],
            'notificaciones' => $notificaciones,
            'no_leidas' => $noLeidas,
        ]);
    }

    /**
     * POST /notificaciones/marcar-leidas
     */
    public function marcarLeidas()
    {
        $user = Auth::user();

        Notificacion::where('usuario_id', $user->id)
            ->noLeidas()
            ->update(['leida' => true, 'leida_en' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * POST /notificaciones/{id}/marcar-leida
     */
    public function marcarLeida($id)
    {
        $user = Auth::user();

        $notificacion = Notificacion::where('id', $id)
            ->where('usuario_id', $user->id)
            ->first();

        if (!$notificacion) {
            return response()->json(['ok' => false, 'message' => 'Notificación no encontrada'], 404);
        }

        if (!$notificacion->leida) {
            $notificacion->update(['leida' => true, 'leida_en' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * GET /notificaciones/nuevas
     * Usado por el polling cada 30s (verificarNuevasNotificaciones). Devuelve
     * cuántas notificaciones NUEVAS llegaron desde la última vez que este
     * mismo usuario consultó este endpoint — no el total de no leídas — que
     * es justo lo que el frontend espera para poder hacer
     * "notificacionesNoLeidas.value += response.data.nuevas".
     */
    public function nuevas()
    {
        $user = Auth::user();
        $cacheKey = "notif_last_check_{$user->id}";

        $ultimaRevision = Cache::get($cacheKey);

        // 🔧 En la primera consulta (sin marca de tiempo previa todavía) NO
        // se debe contar el total de no leídas: el frontend ya sincronizó
        // ese número al montar el layout (obtenerNotificaciones()), y este
        // endpoint solo reporta el DELTA desde la última consulta —
        // devolver el total aquí también causaría un doble conteo la
        // primera vez que corre el polling.
        if (!$ultimaRevision) {
            Cache::put($cacheKey, now(), now()->addDay());
            return response()->json(['ok' => true, 'nuevas' => 0]);
        }

        $nuevas = Notificacion::where('usuario_id', $user->id)
            ->where('created_at', '>', $ultimaRevision)
            ->count();

        Cache::put($cacheKey, now(), now()->addDay());

        return response()->json(['ok' => true, 'nuevas' => $nuevas]);
    }
}