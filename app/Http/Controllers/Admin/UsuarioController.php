<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Administrador;
use App\Models\Creador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('q')->trim()->value();
        $rol = $request->string('rol')->value();
        $estado = $request->string('estado')->value();

        if ($rol === 'admin') {
            $queryAdmin = Administrador::query();

            if ($search) {
                $queryAdmin->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('nickname', 'like', "%{$search}%");
                });
            }

            if ($estado) {
                if ($estado === 'verificado') $queryAdmin->where('esta_activo', true);
                if ($estado === 'bloqueado') $queryAdmin->where('esta_activo', false);
                if (in_array($estado, ['pendiente', 'incompleto'])) $queryAdmin->whereRaw('1 = 0');
            }

            $paginator = $queryAdmin->latest()
                ->paginate(10)
                ->withQueryString();

            $usuarios = $paginator->through(fn($a) => [
                'id' => $a->id,
                'nombre' => $a->nombre,
                'apodo' => $a->nickname ?? 'admin',
                'email' => $a->email,
                'rol' => 'admin',
                'estado' => $a->esta_activo ? 'verificado' : 'bloqueado',
                'created_at' => $a->created_at,
                'es_admin' => true,
            ]);
        } else {
            $queryUser = User::query();

            if ($search) {
                $queryUser->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apodo', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if ($rol) {
                $queryUser->where('rol', $rol);
            }

            if ($estado) {
                $queryUser->where('estado', $estado);
            }

            $paginator = $queryUser->latest()
                ->paginate(10)
                ->withQueryString();

            $usuarios = $paginator->through(fn($u) => [
                'id' => $u->id,
                'nombre' => $u->nombre,
                'apodo' => $u->apodo,
                'email' => $u->email,
                'rol' => $u->rol,
                'estado' => $u->estado,
                'created_at' => $u->created_at,
                'es_admin' => false,
            ]);
        }

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'filtros' => $request->only(['q', 'rol', 'estado']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/Usuarios/Create', [
            'origen' => $request->query('from'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apodo' => ['required_unless:rol,admin', 'nullable', 'string', 'max:255', 'unique:users,apodo'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'unique:administradores,email'],
            'password' => ['required', 'string', 'min:8'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'fecha_nacimiento' => ['required_unless:rol,admin', 'nullable', 'date', 'before:today'],
            'rol' => ['required', Rule::in(['usuario', 'creador', 'admin'])],
            'estado' => ['required', Rule::in(['pendiente', 'verificado', 'incompleto', 'bloqueado'])],
        ]);

        if ($data['rol'] === 'admin') {
            Administrador::create([
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'esta_activo' => $data['estado'] === 'verificado',
            ]);

            return redirect()->route('admin.usuarios.index')->with('success', "Administrador {$data['nombre']} creado correctamente.");
        }

        $data['password'] = Hash::make($data['password']);
        $data['email_verificado_en'] = $data['estado'] === 'verificado' ? now() : null;

        $usuario = User::create($data);

        if ($usuario->rol === 'creador') {
            Creador::firstOrCreate(
                ['usuario_id' => $usuario->id],
                ['estado_verificacion' => 'pendiente']
            );
        }

        return redirect()->route('admin.usuarios.index')->with('success', "Usuario @{$data['apodo']} creado correctamente.");
    }

    public function show(User $usuario): Response
    {
        $usuario->load('perfil', 'creador');

        return Inertia::render('Admin/Usuarios/Show', [
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apodo' => $usuario->apodo,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'ciudad' => $usuario->ciudad,
                'fecha_nacimiento' => $usuario->fecha_nacimiento,
                'rol' => $usuario->rol,
                'estado' => $usuario->estado,
                'email_verificado_en' => $usuario->email_verificado_en,
                'created_at' => $usuario->created_at,
                'updated_at' => $usuario->updated_at,
                'perfil' => $perfilData,
            ],
        ]);
    }

    /**
     * Obtiene la URL completa de una foto
     */
    private function getFotoUrl($ruta)
    {
        if (empty($ruta)) {
            return null;
        }

        // Si ya es una URL completa, devolverla
        if (filter_var($ruta, FILTER_VALIDATE_URL)) {
            return $ruta;
        }

        // Si es una ruta de storage
        if (strpos($ruta, 'perfil/fotos/') === 0) {
            return asset('storage/' . $ruta);
        }

        if (strpos($ruta, 'public/') === 0) {
            return asset('storage/' . str_replace('public/', '', $ruta));
        }

        // Si es una ruta relativa
        if (strpos($ruta, 'storage/') === 0) {
            return asset($ruta);
        }

        // Intentar con storage por defecto
        try {
            if (Storage::disk('public')->exists($ruta)) {
                return asset('storage/' . $ruta);
            }
        } catch (\Exception $e) {
            // Si hay error, intentar con el path directo
        }

        // Si nada funciona, intentar con el path directo
        return asset('storage/' . $ruta);
    }

    public function edit(User $usuario): Response
    {
        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => $usuario,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apodo' => ['required', 'string', 'max:255', Rule::unique('users', 'apodo')->ignore($usuario->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'rol' => ['required', Rule::in(['usuario', 'creador', 'admin'])],
            'estado' => ['required', Rule::in(['pendiente', 'verificado', 'incompleto', 'bloqueado'])],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $eraCreador = $usuario->rol === 'creador';

        $usuario->update($data);

        if ($usuario->rol === 'creador' && !$eraCreador) {
            Creador::firstOrCreate(
                ['usuario_id' => $usuario->id],
                ['estado_verificacion' => 'pendiente']
            );
        }
        // Si deja de ser creador, su fila en `creadores` NO se toca — se
        // conserva por si vuelve a serlo más adelante (decisión del cliente).

        return redirect()->route('admin.usuarios.index')->with('success', "Usuario @{$usuario->apodo} actualizado correctamente.");
    }

    /**
     * Alterna el estado de un usuario entre bloqueado / verificado.
     */
    public function toggleBloqueo(Request $request, $id)
    {
        // Si viene la bandera es_admin desde la petición o si no existe en Users
        $usuario = User::find($id);

        if ($usuario) {
            $usuario->estado = $usuario->estado === 'bloqueado' ? 'verificado' : 'bloqueado';
            $usuario->save();
            $apodo = $usuario->apodo;
            $estado = $usuario->estado;
        } else {
            $admin = Administrador::findOrFail($id);
            $admin->esta_activo = !$admin->esta_activo;
            $admin->save();
            $apodo = $admin->nombre;
            $estado = $admin->esta_activo ? 'verificado' : 'bloqueado';
        }

        return back()->with('success', $estado === 'bloqueado'
            ? "Usuario @{$apodo} bloqueado."
            : "Usuario @{$apodo} desbloqueado.");
    }

    public function destroy(Request $request, $id)
    {
        $usuario = User::find($id);

        if ($usuario) {
            $apodo = $usuario->apodo;
            $usuario->delete();
        } else {
            $admin = Administrador::findOrFail($id);
            $apodo = $admin->nombre;
            $admin->delete();
        }

        return back()->with('success', "Usuario @{$apodo} eliminado.");
    }
}