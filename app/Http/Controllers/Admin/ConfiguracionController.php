<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ConfiguracionController extends Controller
{
    public function index(): Response
    {
        $admin = Auth::guard('admin')->user();

        return Inertia::render('Admin/Configuracion/Index', [
            'perfil' => [
                'nombre' => $admin->nombre,
                'nickname' => $admin->nickname,
                'telefono' => $admin->telefono,
                'foto_perfil_url' => $this->resolverUrl($admin->foto_perfil_url),
                'rol' => $admin->rol,
            ],
        ]);
    }

    public function actualizar(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255', Rule::unique('administradores', 'nickname')->ignore($admin->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'foto_perfil' => ['nullable', 'file', 'image', 'max:4096'],
            'eliminar_foto' => ['boolean'],
        ]);

        if ($request->hasFile('foto_perfil')) {
            $this->borrarFotoSiEsPropia($admin->foto_perfil_url);
            $data['foto_perfil_url'] = $request->file('foto_perfil')->store('administradores', 'public');
        } elseif ($request->boolean('eliminar_foto')) {
            $this->borrarFotoSiEsPropia($admin->foto_perfil_url);
            $data['foto_perfil_url'] = null;
        }
        unset($data['foto_perfil'], $data['eliminar_foto']);

        $admin->update($data);

        return back()->with('success', 'Configuración actualizada correctamente.');
    }

    /** Misma lógica que en Contenido/Eventos: URL externa se deja igual, ruta interna se resuelve al disco público. */
    private function resolverUrl(?string $ruta): ?string
    {
        if (! $ruta) {
            return null;
        }

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            return $ruta;
        }

        return Storage::disk('public')->url($ruta);
    }

    private function borrarFotoSiEsPropia(?string $ruta): void
    {
        if ($ruta && ! str_starts_with($ruta, 'http://') && ! str_starts_with($ruta, 'https://')) {
            Storage::disk('public')->delete($ruta);
        }
    }
}