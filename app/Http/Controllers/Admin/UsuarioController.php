<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    // 1. SI FILTRAN POR ROL 'ADMIN' -> Consultar únicamente la tabla 'administradores'
    if ($rol === 'admin') {
        $queryAdmin = Administrador::query();

        if ($search) {
            $queryAdmin->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($estado) {
            if ($estado === 'verificado') $queryAdmin->where('esta_activo', true);
            if ($estado === 'bloqueado') $queryAdmin->where('esta_activo', false);
            // Si el estado es 'pendiente' e 'incompleto', no habrá admins ya que ellos usan booleano
            if (in_array($estado, ['pendiente', 'incompleto'])) $queryAdmin->whereRaw('1 = 0');
        }

        $paginator = $queryAdmin->latest()
            ->paginate(10)
            ->withQueryString();

        // Mapeamos los campos para que Vue los reciba en el mismo formato
        $usuarios = $paginator->through(fn($a) => [
            'id' => $a->id,
            'nombre' => $a->nombre,
            'apodo' => 'admin', // Valor por defecto o identificador visual
            'email' => $a->email,
            'rol' => 'admin',
            'estado' => $a->esta_activo ? 'verificado' : 'bloqueado',
            'created_at' => $a->created_at,
            'es_admin' => true, // Bandera opcional
        ]);
    }
    // 2. SI NO FILTRAN POR ADMIN O PIDEN TODOS LOS ROLES
    else {
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

    public function create(): Response
    {
        return Inertia::render('Admin/Usuarios/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apodo' => ['required', 'string', 'max:255', 'unique:users,apodo'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'rol' => ['required', Rule::in(['usuario', 'creador', 'admin'])],
            'estado' => ['required', Rule::in(['pendiente', 'verificado', 'incompleto', 'bloqueado'])],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['email_verificado_en'] = $data['estado'] === 'verificado' ? now() : null;

        User::create($data);

        return redirect()->route('admin.usuarios.index')->with('success', "Usuario @{$data['apodo']} creado correctamente.");
    }

    /**
     * Alterna el estado de un usuario entre bloqueado / verificado.
     */
    public function toggleBloqueo(User $usuario)
    {
        $usuario->estado = $usuario->estado === 'bloqueado' ? 'verificado' : 'bloqueado';
        $usuario->save();

        return back()->with('success', $usuario->estado === 'bloqueado'
            ? "Usuario @{$usuario->apodo} bloqueado."
            : "Usuario @{$usuario->apodo} desbloqueado.");
    }

    public function destroy(User $usuario)
    {
        $usuario->delete(); // soft delete

        return back()->with('success', "Usuario @{$usuario->apodo} eliminado.");
    }
}