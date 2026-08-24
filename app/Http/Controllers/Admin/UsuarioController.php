<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BienvenidaUsuarioMail;
use App\Models\User;
use App\Models\Administrador;
use App\Models\Creador;
use App\Models\Fotos;
use App\Support\CodigoVerificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

            $paginator = $queryUser->with('perfil:id,usuario_id')
                ->latest()
                ->paginate(10)
                ->withQueryString();

            // Foto principal de cada usuario, en una sola consulta extra
            // (no una por fila) — así el listado no se vuelve lento con
            // más usuarios. Mismo criterio que usa show(): primero busca
            // en la tabla fotos (es_principal=true), y si no hay, cae al
            // campo users.foto_principal como respaldo.
            $perfilIds = $paginator->getCollection()->pluck('perfil.id')->filter()->values();
            $fotosPrincipales = $perfilIds->isNotEmpty()
                ? Fotos::whereIn('perfil_id', $perfilIds)->where('es_principal', true)->get()->keyBy('perfil_id')
                : collect();

            $usuarios = $paginator->through(function ($u) use ($fotosPrincipales) {
                $fotoPerfil = $u->perfil ? ($fotosPrincipales->get($u->perfil->id)?->ruta_foto) : null;
                $foto = $this->getFotoUrl($fotoPerfil ?? $u->foto_principal);

                return [
                    'id' => $u->id,
                    'nombre' => $u->nombre,
                    'apodo' => $u->apodo,
                    'email' => $u->email,
                    'rol' => $u->rol,
                    'estado' => $u->estado,
                    'created_at' => $u->created_at,
                    'es_admin' => false,
                    'foto_principal' => $foto,
                ];
            });
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

        // Guardamos la contraseña en texto plano ANTES de hashearla — la
        // necesitamos así para el correo de bienvenida (una vez hasheada
        // ya no hay forma de recuperarla).
        $passwordPlano = $data['password'];
        $data['password'] = Hash::make($data['password']);

        // OJO: antes esto dependía de $data['estado'] === 'verificado'.
        // Ahora 'estado' es un campo aparte (moderación) y la verificación
        // de correo es independiente — siempre nace null, y se confirma
        // con el código de 6 dígitos en su primer intento de login, sin
        // importar qué 'estado' le hayas puesto aquí.
        $data['email_verificado_en'] = null;

        $usuario = User::create($data);

        if ($usuario->rol === 'creador') {
            Creador::firstOrCreate(
                ['usuario_id' => $usuario->id],
                ['estado_verificacion' => 'pendiente']
            );
        }

        // Correo de bienvenida con sus credenciales + código de
        // verificación. Igual que con las invitaciones: si el correo
        // falla, no tumbamos la creación del usuario — solo lo dejamos
        // en el log para revisarlo.
        $codigo = CodigoVerificacion::generar($usuario->email);
        CodigoVerificacion::marcarEnviado($usuario->email);

        try {
            Mail::to($usuario->email)->send(new BienvenidaUsuarioMail($usuario, $passwordPlano, $codigo));
        } catch (\Throwable $e) {
            Log::error('No se pudo enviar el correo de bienvenida', [
                'usuario_id' => $usuario->id,
                'email' => $usuario->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admin.usuarios.index')->with('flash', [
                'toast' => [
                    'type' => 'warning',
                    'message' => "Usuario @{$data['apodo']} creado, pero el correo de bienvenida no se pudo enviar.",
                ],
            ]);
        }

        return redirect()->route('admin.usuarios.index')->with('success', "Usuario @{$data['apodo']} creado correctamente. Se le envió un correo con sus credenciales.");
    }

    public function show(User $usuario): Response
    {
        // Cargar relaciones
        $usuario->load('perfil', 'creador');
        
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

        $perfil = $usuario->perfil;

        $perfilData = $perfil ? [
            'id' => $perfil->id,
            'tipo' => $perfil->tipo ?? 'personal',
            'biografia' => $perfil->biografia ?? null,
            'genero' => $perfil->genero ?? null,
            'ubicacion_ciudad' => $perfil->ubicacion_ciudad ?? null,
            'fotos' => $fotos,
            'esta_verificado' => $perfil->esta_verificado ?? false,
        ] : null;

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
                'creador' => $usuario->creador,
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
     * Combina la funcionalidad para usuarios web y administradores.
     */
    public function toggleBloqueo(Request $request, $id)
    {
        // Primero intentar encontrar en Users
        $usuario = User::find($id);

        if ($usuario) {
            $usuario->estado = $usuario->estado === 'bloqueado' ? 'verificado' : 'bloqueado';
            $usuario->save();
            $apodo = $usuario->apodo;
            $estado = $usuario->estado;
        } else {
            // Si no existe en Users, buscar en Administradores
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
        // Primero intentar encontrar en Users
        $usuario = User::find($id);

        if ($usuario) {
            $apodo = $usuario->apodo;
            $usuario->delete();
        } else {
            // Si no existe en Users, buscar en Administradores
            $admin = Administrador::findOrFail($id);
            $apodo = $admin->nombre;
            $admin->delete();
        }

        return back()->with('success', "Usuario @{$apodo} eliminado.");
    }
}