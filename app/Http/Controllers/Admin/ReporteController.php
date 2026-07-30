<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    public function index(Request $request): Response
    {
        $total = Reporte::count();
        $pendientes = Reporte::where('estado', 'pendiente')->count();
        $revisados = Reporte::where('estado', 'revisado')->count();
        $resueltos = Reporte::where('estado', 'resuelto')->count();

        $query = Reporte::with(['reporta:id,nombre,apodo', 'reportado:id,nombre,apodo,estado']);

        if ($search = $request->string('q')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('reportado', fn ($u) => $u->where('nombre', 'like', "%{$search}%")->orWhere('apodo', 'like', "%{$search}%"))
                    ->orWhereHas('reporta', fn ($u) => $u->where('nombre', 'like', "%{$search}%")->orWhere('apodo', 'like', "%{$search}%"));
            });
        }

        if ($tipo = $request->string('tipo')->value()) {
            $query->where('tipo', $tipo);
        }

        if ($estado = $request->string('estado')->value()) {
            $query->where('estado', $estado);
        }

        $reportes = $query->latest()->paginate(10)->withQueryString();
        $reportes->through(fn ($r) => [
            'id' => $r->id,
            'tipo' => $r->tipo,
            'tipo_nombre' => $r->tipo_nombre,
            'descripcion' => $r->descripcion,
            'estado' => $r->estado,
            'reportable_type' => class_basename($r->reportable_type),
            'reporta' => $r->reporta,
            'reportado' => $r->reportado,
            'created_at' => $r->created_at,
        ]);

        // --- Desglose por tipo (para el resumen) ---
        $porTipo = Reporte::selectRaw('tipo, COUNT(*) as cantidad')->groupBy('tipo')->pluck('cantidad', 'tipo');

        return Inertia::render('Admin/Reportes/Index', [
            'stats' => [
                'total' => $total,
                'pendientes' => $pendientes,
                'revisados' => $revisados,
                'resueltos' => $resueltos,
            ],
            'reportes' => $reportes,
            'filtros' => $request->only(['q', 'tipo', 'estado']),
            'porTipo' => [
                'spam' => $porTipo['spam'] ?? 0,
                'inapropiado' => $porTipo['inapropiado'] ?? 0,
                'falso' => $porTipo['falso'] ?? 0,
                'acoso' => $porTipo['acoso'] ?? 0,
                'otro' => $porTipo['otro'] ?? 0,
            ],
        ]);
    }

    public function marcarRevisado(Reporte $reporte)
    {
        $reporte->update(['estado' => 'revisado']);

        return back()->with('success', 'Reporte marcado como revisado.');
    }

    public function resolver(Reporte $reporte)
    {
        $reporte->update(['estado' => 'resuelto']);

        return back()->with('success', 'Reporte marcado como resuelto.');
    }

    public function bloquearReportado(Reporte $reporte)
    {
        $reporte->reportado?->update(['estado' => 'bloqueado']);
        $reporte->update(['estado' => 'resuelto']);

        return back()->with('success', "Usuario @{$reporte->reportado?->apodo} bloqueado y reporte resuelto.");
    }

    public function destroy(Reporte $reporte)
    {
        $reporte->delete();

        return back()->with('success', 'Reporte descartado.');
    }
}