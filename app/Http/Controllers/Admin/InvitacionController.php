<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvitacionEventoMail;
use App\Mail\InvitacionRegistroMail;
use App\Models\CodigoInvitacion;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InvitacionController extends Controller
{
    public function index(Request $request): Response
    {
        $ahora = now();

        $desactivadas = CodigoInvitacion::whereNull('usado_en')->where('esta_activo', false)->count();
        // OJO: no es simplemente CodigoInvitacion::count() — eso incluía
        // las desactivadas manualmente, que no deben contarse para nada
        // (ni aquí, ni en la tasa de aceptación). Las "aceptadas" también
        // pueden tener esta_activo=false (se pone así al agotar sus usos),
        // así que no basta con filtrar por esta_activo=true a secas —
        // restamos solo las que de verdad están desactivadas.
        $total = CodigoInvitacion::count() - $desactivadas;
        $aceptadas = CodigoInvitacion::whereNotNull('usado_en')->count();
        $expiradas = CodigoInvitacion::whereNull('usado_en')
            ->where('esta_activo', true)
            ->whereNotNull('expira_en')
            ->where('expira_en', '<=', $ahora)
            ->count();
        // No vencidas ni aceptadas, pero ya alcanzaron su límite de usos —
        // mismo criterio que estadoDisplay(). Antes no se restaban de
        // "pendientes", así que un código ya utilizado se contaba (mal)
        // como pendiente en los KPIs y en la dona de resumen.
        // OJO: whereColumn no detecta contador_usos NULL (registros creados
        // antes del fix del modelo que defaultea contador_usos a 0) — con
        // NULL esta condición nunca es verdadera, así que la excluimos
        // aparte con whereNotNull para no contarla por accidente aquí.
        $utilizadas = CodigoInvitacion::whereNull('usado_en')
            ->where('esta_activo', true)
            ->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', $ahora))
            ->whereNotNull('contador_usos')
            ->whereColumn('contador_usos', '>=', 'usos_maximos')
            ->count();
        // $desactivadas ya NO está incluida en $total (se restó arriba),
        // así que aquí ya no hay que volver a restarla.
        $pendientes = $total - $aceptadas - $expiradas - $utilizadas;

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
                'desactivada' => $query->whereNull('usado_en')->where('esta_activo', false),
                'pendiente' => $query->whereNull('usado_en')->where('esta_activo', true)->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', $ahora))->where(fn ($q) => $q->whereNull('contador_usos')->orWhereColumn('contador_usos', '<', 'usos_maximos')),
                'expirada' => $query->whereNull('usado_en')->where('esta_activo', true)->whereNotNull('expira_en')->where('expira_en', '<=', $ahora),
                'utilizada' => $query->whereNull('usado_en')->where('esta_activo', true)->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', $ahora))->whereNotNull('contador_usos')->whereColumn('contador_usos', '>=', 'usos_maximos'),
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
                'utilizadas' => $utilizadas,
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
            // Para el selector de "¿a cuál evento invita?" cuando el tipo
            // de invitación es 'evento' — solo eventos publicados y que
            // todavía no han pasado.
            'eventos' => Evento::publicados()
                ->proximos()
                ->orderBy('fecha')
                ->get(['id', 'nombre', 'fecha', 'ciudad'])
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'nombre' => $e->nombre,
                    'fecha' => $e->fecha_formateada,
                    'ciudad' => $e->ciudad,
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
                'desactivada' => $query->whereNull('usado_en')->where('esta_activo', false),
                'pendiente' => $query->whereNull('usado_en')->where('esta_activo', true)->where(fn ($q) => $q->whereNull('expira_en')->orWhere('expira_en', '>', $ahora))->where(fn ($q) => $q->whereNull('contador_usos')->orWhereColumn('contador_usos', '<', 'usos_maximos')),
                'expirada' => $query->whereNull('usado_en')->where('esta_activo', true)->whereNotNull('expira_en')->where('expira_en', '<=', $ahora),
                'utilizada' => $query->whereNull('usado_en')->where('esta_activo', true)->whereNotNull('contador_usos')->whereColumn('contador_usos', '>=', 'usos_maximos'),
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
            'evento_id' => ['required_if:tipo,evento', 'nullable', 'exists:eventos,id'],
            'vigencia_dias' => ['required', 'integer', 'min:1', 'max:365'],
            'usos_maximos' => ['required', 'integer', 'min:1', 'max:100'],
            'mensaje' => ['nullable', 'string', 'max:250'],
            // El código lo genera el formulario (lo que el admin ve/copia/comparte
            // antes de enviar) — lo validamos único para no chocar con otro ya
            // generado, y si por lo que sea no llega, el modelo genera uno propio.
            'codigo' => ['nullable', 'string', 'max:20', Rule::unique(CodigoInvitacion::class, 'codigo')],
        ], [
            'evento_id.required_if' => 'Elige a qué evento estás invitando.',
            'evento_id.exists' => 'Ese evento ya no existe o no está disponible.',
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
                'evento_id' => $data['tipo'] === 'evento' ? $data['evento_id'] : null,
            ],
        ]);

        // El envío de correo no debe tronar la creación de la invitación si
        // el SMTP falla (credenciales mal puestas, servidor caído, etc.) —
        // la invitación ya quedó guardada y es válida aunque el correo no
        // salga; solo lo registramos en el log para poder revisarlo.
        $correoEnviado = false;
        try {
            // Las invitaciones a un evento específico llevan su propio
            // correo (menciona el evento y el botón manda directo a
            // registrarse + reservar ese evento). El resto (registro,
            // premium) usa el correo genérico de siempre.
            if ($data['tipo'] === 'evento' && $codigo->metadata['evento_id']) {
                $evento = Evento::find($codigo->metadata['evento_id']);
                if ($evento) {
                    Mail::to($codigo->email)->send(new InvitacionEventoMail($codigo, $evento));
                } else {
                    Mail::to($codigo->email)->send(new InvitacionRegistroMail($codigo));
                }
            } else {
                Mail::to($codigo->email)->send(new InvitacionRegistroMail($codigo));
            }
            $correoEnviado = true;
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de invitación', [
                'invitacion_id' => $codigo->id,
                'email' => $codigo->email,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.invitaciones.index')
            ->with('success', "Invitación creada. Código: {$codigo->codigo}")
            ->with('flash', [
                'toast' => $correoEnviado
                    ? ['type' => 'success', 'message' => "Invitación creada y correo enviado a {$codigo->email}."]
                    : ['type' => 'warning', 'message' => "Invitación creada, pero el correo no se pudo enviar. Revisa la configuración de correo."],
            ]);
    }

    public function destroy(CodigoInvitacion $invitacion)
    {
        $invitacion->update(['esta_activo' => false]);

        return back()->with('success', 'Invitación desactivada.');
    }

    private function estadoDisplay(CodigoInvitacion $c): string
    {
        if ($c->usado_en) return 'aceptada';
        if (!$c->esta_activo) return 'desactivada';
        if ($c->expira_en && now()->greaterThan($c->expira_en)) return 'expirada';
        if ($c->contador_usos >= $c->usos_maximos) return 'utilizada';
        return 'pendiente';
    }
}