<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SeguridadController extends Controller
{
    public function index(): Response
    {
        $admin = Auth::guard('admin')->user();
        $esSuperAdmin = $admin->rol === 'super_admin';

        return Inertia::render('Admin/Seguridad/Index', [
            'perfil' => [
                'nombre' => $admin->nombre,
                'email' => $admin->email,
                'telefono' => $admin->telefono,
                'rol' => $admin->rol,
                'ultimo_acceso_en' => $admin->ultimo_acceso_en,
                'ultimo_acceso_ip' => $admin->ultimo_acceso_ip,
                'created_at' => $admin->created_at,
            ],
            'esSuperAdmin' => $esSuperAdmin,
            'administradores' => $esSuperAdmin
                ? Administrador::orderByDesc('ultimo_acceso_en')->get(['id', 'nombre', 'email', 'rol', 'esta_activo', 'ultimo_acceso_en', 'ultimo_acceso_ip'])
                : [],
        ]);
    }

    public function actualizarPerfil(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('administradores', 'email')->ignore($admin->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
        ]);

        $admin->update($data);

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function actualizarPassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'password_actual' => ['required'],
            'password_nueva' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($request->password_actual, $admin->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.']);
        }

        $admin->update(['password' => $request->password_nueva]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }

    public function toggleActivo(Administrador $administrador)
    {
        if ($administrador->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $administrador->update(['esta_activo' => ! $administrador->esta_activo]);

        return back()->with('success', $administrador->esta_activo
            ? "Administrador {$administrador->nombre} reactivado."
            : "Administrador {$administrador->nombre} desactivado.");
    }
}