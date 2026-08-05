<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CodigoInvitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InvitacionController extends Controller
{
    public function index(Request $request): Response
    {
        $ahora = now();

        $total = CodigoInvitacion::count();
        $aceptadas = CodigoInvitacion::whereNotNull('usado_en')->count();
        $expiradas = CodigoInvitacion::whereNull('usado_en')
            ->whereNotNull('expira_en')
            ->where('expira_en', '<=', $ahora)
            ->count();
        $pendientes = $total - $aceptadas - $expiradas;

        $query = CodigoInvitacion::with('creadoPorAdmin:id,nombre');

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_destinatario', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->string('estado')->value()) {
            match ($estado) {
                'aceptada' => $query->whereNotNull('usado_en'),
                'pendiente' => $query->whereNull('usado_en')->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', $ahora))->whereColumn('contador_usos', '<', 'usos_maximos'),
                'expirada' => $query->whereNull('usado_en')->whereNotNull('expira_en')->where('expira_en', '<=', $ahora),
                default => null,
            };
        }

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('metadata->tipo', $tipo);
        }

        if ($desde = $request->date('desde')) {
            $query->where('created_at', '>=', $desde->startOfDay());
        }

        if ($hasta = $request->date('hasta')) {
            $query->where('created_at', '<=', $hasta->endOfDay());
        }

        $invitaciones = $query->latest()->paginate(3)->withQueryString();
        $invitaciones->through(fn ($c) => [
            'id' => $c->id,
            'codigo' => $c->codigo,
            'nombre_destinatario' => $c->nombre_destinatario,
            'email' => $c->email,
            'tipo' => $c->metadata['tipo'] ?? 'registro',
            'creado_por' => $c->creadoPorAdmin?->nombre ?? 'Administrador',
            'created_at' => $c->created_at,
            'estado' => $this->estadoDisplay($c),
        ]);

        $enlacesActivos = CodigoInvitacion::where('usos_maximos', '>', 1)
            ->where('esta_activo', true)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'codigo' => $c->codigo,
                'url' => url("/invitar/{$c->codigo}"),
                'tipo' => $c->metadata['tipo'] ?? 'registro',
                'usos' => $c->contador_usos,
                'usos_maximos' => $c->usos_maximos,
                'created_at' => $c->created_at,
                'activo' => $c->esta_activo && ! ($c->expira_en && now()->greaterThan($c->expira_en)),
            ]);

        return Inertia::render('Admin/Invitaciones/Index', [
            'stats' => [
                'enviadas' => $total,
                'aceptadas' => $aceptadas,
                'pendientes' => max($pendientes, 0),
                'expiradas' => $expiradas,
                'tasaAceptacion' => $total > 0 ? round(($aceptadas / $total) * 100) : 0,
            ],
            'invitaciones' => $invitaciones,
            'enlacesActivos' => $enlacesActivos,
            'filtros' => $request->only(['q', 'estado', 'tipo', 'desde', 'hasta']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Invitaciones/Create', [
            'invitacionesRecientes' => CodigoInvitacion::latest()
                ->take(5)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'nombre_destinatario' => $c->nombre_destinatario,
                    'email' => $c->email,
                    'created_at' => $c->created_at,
                    'estado' => $this->estadoDisplay($c),
                ]),
        ]);
    }

    public function codigos(Request $request): Response
    {
        $query = CodigoInvitacion::with('creadoPorAdmin:id,nombre');

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_destinatario', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%");
            });
        }

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('metadata->tipo', $tipo);
        }

        if ($estado = $request->string('estado')->value()) {
            $ahora = now();
            match ($estado) {
                'aceptada' => $query->whereNotNull('usado_en'),
                'pendiente' => $query->whereNull('usado_en')->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', $ahora))->whereColumn('contador_usos', '<', 'usos_maximos'),
                'expirada' => $query->whereNull('usado_en')->whereNotNull('expira_en')->where('expira_en', '<=', $ahora),
                'utilizada' => $query->whereColumn('contador_usos', '>=', 'usos_maximos'),
                default => null,
            };
        }

        $codigos = $query->latest()->paginate(15)->withQueryString();
        $codigos->through(fn ($c) => [
            'id' => $c->id,
            'codigo' => $c->codigo,
            'nombre_destinatario' => $c->nombre_destinatario,
            'email' => $c->email,
            'tipo' => $c->metadata['tipo'] ?? 'registro',
            'usos' => $c->contador_usos,
            'usos_maximos' => $c->usos_maximos,
            'expira_en' => $c->expira_en,
            'created_at' => $c->created_at,
            'creado_por' => $c->creadoPorAdmin?->nombre ?? 'Administrador',
            'estado' => $this->estadoDisplay($c),
        ]);

        return Inertia::render('Admin/Invitaciones/Codigos', [
            'codigos' => $codigos,
            'filtros' => $request->only(['q', 'tipo', 'estado']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_destinatario' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'tipo' => ['required', 'in:registro,premium,evento'],
            'vigencia_dias' => ['required', 'integer', 'min:1', 'max:365'],
            'usos_maximos' => ['required', 'integer', 'min:1', 'max:100'],
            'mensaje' => ['nullable', 'string', 'max:250'],
            // El código lo genera el formulario (lo que el admin ve/copia/comparte
            // antes de enviar) — lo validamos único para no chocar con otro ya
            // generado, y si por lo que sea no llega, el modelo genera uno propio.
            'codigo' => ['nullable', 'string', 'max:20', Rule::unique(CodigoInvitacion::class, 'codigo')],
        ]);

        $codigo = CodigoInvitacion::create([
            'codigo' => $data['codigo'] ?? null,
            'nombre_destinatario' => $data['nombre_destinatario'],
            'email' => $data['email'],
            'expira_en' => now()->addDays($data['vigencia_dias']),
            'usos_maximos' => $data['usos_maximos'],
            'creado_por_admin_id' => Auth::guard('admin')->id(),
            'metadata' => [
                'tipo' => $data['tipo'],
                'telefono' => $data['telefono'] ?? null,
                'mensaje' => $data['mensaje'] ?? null,
            ],
        ]);

        return redirect()->route('admin.invitaciones.index')
            ->with('success', "Invitación creada. Código: {$codigo->codigo}");
    }

    public function destroy(CodigoInvitacion $invitacion)
    {
        $invitacion->update(['esta_activo' => false]);

        return back()->with('success', 'Invitación desactivada.');
    }

    private function estadoDisplay(CodigoInvitacion $c): string
    {
        if ($c->usado_en) return 'aceptada';
        if ($c->expira_en && now()->greaterThan($c->expira_en)) return 'expirada';
        if ($c->contador_usos >= $c->usos_maximos) return 'utilizada';
        return 'pendiente';
    }
}