<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MensajeSoporte;
use App\Models\Soporte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SoporteController extends Controller
{
    /**
     * Pantalla única: lista de conversaciones de soporte + la activa,
     * elegida por query param ?soporte=ID (o la más reciente si no se pasa).
     */
    public function index(Request $request): Response
    {
        $query = Soporte::with(['usuario', 'administrador:id,nombre', 'reporte:id,tipo,descripcion']);

        if ($search = $request->string('q')->trim()->value()) {
            $query->whereHas('usuario', fn ($u) => $u->where('nombre', 'like', "%{$search}%")->orWhere('apodo', 'like', "%{$search}%"));
        }

        if ($request->string('filtro')->value() === 'no-leidos') {
            $query->whereHas('mensajes', fn ($q) => $q->where('leido', false)->whereNotNull('usuario_id'));
        } elseif ($request->string('filtro')->value() === 'cerrados') {
            $query->where('estado', 'cerrado');
        } else {
            // por defecto ocultamos las cerradas del listado "Todos", salvo que se busque algo
            if (! $search) {
                $query->where('estado', 'abierto');
            }
        }

        $soportes = $query->orderByDesc('ultimo_mensaje_en')->orderByDesc('created_at')->take(50)->get();

        $conversaciones = $soportes->map(fn (Soporte $s) => $this->resumenSoporte($s));

        $soporteActivoId = $request->integer('soporte') ?: $conversaciones->first()['id'] ?? null;
        $soporteActivo = $soporteActivoId ? $soportes->firstWhere('id', $soporteActivoId) : null;

        $mensajes = [];
        if ($soporteActivo) {
            MensajeSoporte::where('soporte_id', $soporteActivo->id)
                ->deUsuario()
                ->noLeidos()
                ->update(['leido' => true, 'leido_en' => now()]);

            $mensajes = $this->mensajesDeSoporte($soporteActivo);
        }

        return Inertia::render('Admin/Mensajes/Index', [
            'stats' => [
                'total' => Soporte::count(),
                'mensajesHoy' => MensajeSoporte::whereDate('created_at', now()->toDateString())->count(),
                'abiertos' => Soporte::where('estado', 'abierto')->count(),
                'sinLeer' => MensajeSoporte::deUsuario()->noLeidos()->distinct('soporte_id')->count('soporte_id'),
            ],
            'conversaciones' => $conversaciones,
            'soporteActivoId' => $soporteActivoId,
            'mensajes' => $mensajes,
            'filtros' => $request->only(['q', 'filtro']),
        ]);
    }

    /**
     * Inicia (o reabre) una conversación de soporte con un usuario,
     * opcionalmente ligada a un reporte. Se llamará desde Reportes.
     */
    public function iniciar(Request $request)
    {
        $data = $request->validate([
            'usuario_id' => ['required', 'exists:users,id'],
            'reporte_id' => ['nullable', 'exists:reportes,id'],
            'asunto' => ['nullable', 'string', 'max:255'],
        ]);

        $admin = Auth::guard('admin')->user();

        // Si ya existe una conversación abierta con este usuario (para este
        // reporte, o general si no hay reporte), la reutilizamos.
        $soporte = Soporte::where('usuario_id', $data['usuario_id'])
            ->when($data['reporte_id'] ?? null, fn ($q, $reporteId) => $q->where('reporte_id', $reporteId))
            ->where('estado', 'abierto')
            ->first();

        if (! $soporte) {
            $soporte = Soporte::create([
                'usuario_id' => $data['usuario_id'],
                'administrador_id' => $admin->id,
                'reporte_id' => $data['reporte_id'] ?? null,
                'asunto' => $data['asunto'] ?? (! empty($data['reporte_id']) ? "Reporte #{$data['reporte_id']}" : 'Consulta de soporte'),
                'origen' => ! empty($data['reporte_id']) ? 'reporte' : 'manual',
                'estado' => 'abierto',
            ]);
        }

        return redirect()->route('admin.mensajes.index', ['soporte' => $soporte->id]);
    }

    public function enviar(Request $request, Soporte $soporte)
    {
        $data = $request->validate([
            'texto' => ['required', 'string', 'max:5000'],
        ]);

        $admin = Auth::guard('admin')->user();

        MensajeSoporte::create([
            'soporte_id' => $soporte->id,
            'usuario_id' => null,
            'administrador_id' => $admin->id,
            'texto' => $data['texto'],
            'leido' => false,
            'estado' => 'enviado',
        ]);

        $soporte->update([
            'administrador_id' => $admin->id,
            'ultimo_mensaje_en' => now(),
        ]);

        return redirect()->route('admin.mensajes.index', array_merge($request->only(['q', 'filtro']), ['soporte' => $soporte->id]))
            ->with('success', 'Mensaje enviado.');
    }

    /**
     * Marca una conversación como cerrada (caso atendido).
     */
    public function cerrar(Soporte $soporte)
    {
        $soporte->update(['estado' => 'cerrado']);

        return back()->with('success', 'Conversación cerrada.');
    }

    private function resumenSoporte(Soporte $soporte): array
    {
        $ultimoMensaje = $soporte->ultimo_mensaje;

        return [
            'id' => $soporte->id,
            'usuario' => $soporte->usuario ? $this->datosUsuario($soporte->usuario) : null,
            'asunto' => $soporte->asunto,
            'origen' => $soporte->origen,
            'estado' => $soporte->estado,
            'reporteId' => $soporte->reporte_id,
            'administradorNombre' => $soporte->administrador?->nombre,
            'ultimoMensaje' => $ultimoMensaje ? str($ultimoMensaje->texto)->limit(70)->value() : 'Sin mensajes',
            'ultimoMensajeEn' => $soporte->ultimo_mensaje_en?->diffForHumans(),
            'noLeidos' => $soporte->mensajes()->where('leido', false)->whereNotNull('usuario_id')->count(),
        ];
    }

    private function mensajesDeSoporte(Soporte $soporte): array
    {
        return $soporte->mensajes()
            ->with(['usuario', 'administrador:id,nombre'])
            ->orderBy('created_at')
            ->get()
            ->map(function (MensajeSoporte $mensaje) {
                $esAdmin = $mensaje->es_admin;
                $usuario = $mensaje->usuario;

                return [
                    'id' => $mensaje->id,
                    'texto' => $mensaje->texto,
                    'esAdmin' => $esAdmin,
                    'remitenteNombre' => $esAdmin
                        ? ($mensaje->administrador->nombre ?? 'Soporte')
                        : ($usuario->nombre ?? $usuario->apodo ?? 'Usuario'),
                    'avatar' => $esAdmin ? null : $this->avatarDeUsuario($usuario),
                    'tiempo' => $mensaje->created_at->diffForHumans(),
                    'fecha' => $mensaje->created_at->format('d-m-Y H:i'),
                    'leido' => $mensaje->leido,
                    'archivosAdjuntos' => $mensaje->archivos_adjuntos ?? [],
                ];
            })
            ->all();
    }

    private function datosUsuario(User $user): array
    {
        return [
            'id' => $user->id,
            'nombre' => $user->nombre ?? $user->apodo ?? 'Usuario',
            'avatar' => $this->avatarDeUsuario($user),
        ];
    }

    private function avatarDeUsuario(?User $user): string
    {
        if (! $user) {
            return '/images/shared/avatar-default.jpg';
        }

        if ($user->foto_principal) {
            if (str_starts_with($user->foto_principal, 'http') || str_starts_with($user->foto_principal, '/')) {
                return $user->foto_principal;
            }
            return '/storage/' . $user->foto_principal;
        }

        $perfil = $user->perfil ?? null;
        if ($perfil) {
            $foto = $perfil->fotos()->where('es_principal', true)->first();
            if ($foto) {
                $url = $foto->url ?? $foto->ruta_foto ?? null;
                if ($url) {
                    if (str_starts_with($url, 'http') || str_starts_with($url, '/')) {
                        return $url;
                    }
                    return '/storage/' . $url;
                }
            }
        }

        return '/images/shared/avatar-default.jpg';
    }
}