<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Administrador;
use App\Models\Fotos;
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

    public function show(User $usuario): Response
    {
        // Cargar relaciones
        $usuario->load('perfil');
        
        // ============================================================
        // OBTENER FOTOS DESDE LA TABLA FOTOS
        // ============================================================
        $fotos = [];
        if ($usuario->perfil) {
            // Buscar fotos en la tabla Fotos
            $fotosModel = Fotos::where('perfil_id', $usuario->perfil->id)
                ->orderBy('es_principal', 'desc')
                ->orderBy('fecha_subida', 'desc')
                ->get();
            
            if ($fotosModel->isNotEmpty()) {
                foreach ($fotosModel as $foto) {
                    // Obtener la URL completa de la foto
                    $url = $this->getFotoUrl($foto->ruta_foto);
                    
                    // Si hay URL, agregar la foto
                    if ($url) {
                        $fotos[] = [
                            'id' => $foto->id,
                            'url' => $url,
                            'ruta_foto' => $foto->ruta_foto,
                            'es_principal' => (bool) $foto->es_principal,
                            'fecha_subida' => $foto->fecha_subida,
                            'permisos' => $foto->permisos ?? ['todos'],
                        ];
                    }
                }
            }
        }
        
        // ============================================================
        // SI NO HAY FOTOS EN LA TABLA FOTOS, INTENTAR DEL CAMPO 'fotos'
        // ============================================================
        if (empty($fotos) && $usuario->perfil && !empty($usuario->perfil->fotos)) {
            $perfilFotos = $usuario->perfil->fotos;
            
            if (is_string($perfilFotos)) {
                $perfilFotos = json_decode($perfilFotos, true);
            }
            
            if (is_array($perfilFotos) && !empty($perfilFotos)) {
                foreach ($perfilFotos as $index => $foto) {
                    $ruta = is_string($foto) ? $foto : ($foto['url'] ?? $foto['ruta_foto'] ?? null);
                    $url = $this->getFotoUrl($ruta);
                    
                    if ($url) {
                        $fotos[] = [
                            'id' => $index + 1,
                            'url' => $url,
                            'ruta_foto' => $ruta,
                            'es_principal' => $index === 0,
                            'fecha_subida' => now(),
                            'permisos' => ['todos'],
                        ];
                    }
                }
            }
        }

        // ============================================================
        // PREPARAR DATOS DEL PERFIL
        // ============================================================
        $perfilData = null;
        if ($usuario->perfil) {
            $perfilData = [
                'id' => $usuario->perfil->id,
                'tipo' => $usuario->perfil->tipo ?? 'personal',
                'descripcion' => $usuario->perfil->descripcion ?? '',
                'biografia' => $usuario->perfil->biografia ?? '',
                'intereses' => $usuario->perfil->intereses ?? [],
                'pasatiempos' => $usuario->perfil->pasatiempos ?? [],
                'fotos' => $fotos,
                'privacidad_fotos' => $usuario->perfil->privacidad_fotos ?? 'todos',
                'esta_verificado' => $usuario->perfil->esta_verificado ?? false,
                'estado_verificacion' => $usuario->perfil->esta_verificado ? 'verificado' : 'pendiente',
                'puntuacion_compatibilidad' => $usuario->perfil->puntuacion_compatibilidad ?? 0,
                'ubicacion_ciudad' => $usuario->perfil->ubicacion_ciudad ?? $usuario->ciudad ?? '',
                'metadatos' => $usuario->perfil->metadatos ?? [],
                'created_at' => $usuario->perfil->created_at,
                'updated_at' => $usuario->perfil->updated_at,
            ];
        }

        // Log para debug
        \Log::info('Fotos en show:', [
            'user_id' => $usuario->id,
            'perfil_id' => $usuario->perfil?->id,
            'total_fotos' => count($fotos),
            'fotos' => $fotos
        ]);

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

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')->with('success', "Usuario @{$usuario->apodo} actualizado correctamente.");
    }

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
        $usuario->delete();

        return back()->with('success', "Usuario @{$usuario->apodo} eliminado.");
    }
}