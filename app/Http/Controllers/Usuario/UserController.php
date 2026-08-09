<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Mostrar el formulario de edición del usuario
     */
    public function edit()
    {
        $user = Auth::user()->load('perfil');
        
        return Inertia::render('Profile/Ingresar', [
            'user' => $user,
        ]);
    }

    /**
     * Actualizar los datos del usuario
     */
    public function actualizar(Request $request)
    {
        $user = Auth::user();
        
        // Validar los datos
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|min:2|max:100',
            'apodo' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'apodo')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'telefono' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'ciudad' => 'nullable|string|max:100',
            'fecha_nacimiento' => [
                'nullable',
                'date',
                'before:18 years ago',
                'after:1900-01-01',
            ],
        ], [
            // Mensajes personalizados en español
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre no puede tener más de 100 caracteres.',
            
            'apodo.required' => 'El apodo es obligatorio.',
            'apodo.min' => 'El apodo debe tener al menos 3 caracteres.',
            'apodo.max' => 'El apodo no puede tener más de 20 caracteres.',
            'apodo.regex' => 'El apodo solo puede contener letras, números y guión bajo.',
            'apodo.unique' => 'Este apodo ya está en uso por otro usuario.',
            
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Ingresa un email válido.',
            'email.max' => 'El email no puede tener más de 255 caracteres.',
            'email.unique' => 'Este email ya está registrado por otro usuario.',
            
            'telefono.regex' => 'Ingresa un número de teléfono válido (solo números, +, -, espacios y paréntesis).',
            'telefono.max' => 'El teléfono no puede tener más de 20 caracteres.',
            
            'ciudad.max' => 'La ciudad no puede tener más de 100 caracteres.',
            
            'fecha_nacimiento.date' => 'Ingresa una fecha de nacimiento válida.',
            'fecha_nacimiento.before' => 'Debes ser mayor de 18 años para registrarte.',
            'fecha_nacimiento.after' => 'Ingresa una fecha de nacimiento válida (después de 1900).',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Datos validados
        $validated = $validator->validated();

        // Actualizar el usuario
        $user->update([
            'nombre' => $validated['nombre'],
            'apodo' => $validated['apodo'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? null,
            'ciudad' => $validated['ciudad'] ?? null,
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
        ]);

        // Actualizar estado del perfil si está completo
        $this->actualizarEstadoPerfil($user);

        return redirect()->back()->with('flash', [
            'toast' => [
                'type' => 'success',
                'title' => '¡Datos actualizados!',
                'message' => 'Tu información personal se ha guardado correctamente.',
                'duration' => 3000,
            ]
        ]);
    }

    /**
     * Cambiar la contraseña del usuario
     */
    public function cambiarPassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar la contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'La contraseña actual es incorrecta.'])
                ->withInput();
        }

        // Actualizar la contraseña
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('flash', [
            'toast' => [
                'type' => 'success',
                'title' => 'Contraseña actualizada',
                'message' => 'Tu contraseña se ha cambiado correctamente.',
                'duration' => 3000,
            ]
        ]);
    }

    /**
     * Actualizar solo el avatar/foto principal
     */
    public function actualizarAvatar(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
        ], [
            'avatar.required' => 'Debes seleccionar una imagen.',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.mimes' => 'La imagen debe ser JPG, PNG, GIF o WEBP.',
            'avatar.max' => 'La imagen no debe superar los 5MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            // Generar nombre único
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            
            // Guardar en el usuario
            $user->update([
                'foto_principal' => '/storage/' . $path,
            ]);

            return redirect()->back()->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => 'Avatar actualizado',
                    'message' => 'Tu foto de perfil se ha actualizado correctamente.',
                    'duration' => 3000,
                ]
            ]);
        }

        return redirect()->back()->with('flash', [
            'toast' => [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No se pudo actualizar el avatar.',
                'duration' => 3000,
            ]
        ]);
    }

    /**
     * Verificar si un apodo está disponible (AJAX)
     */
    public function verificarApodo(Request $request)
    {
        $request->validate([
            'apodo' => 'required|string|min:3|max:20|regex:/^[a-zA-Z0-9_]+$/',
        ]);

        $exists = User::where('apodo', $request->apodo)
            ->where('id', '!=', Auth::id())
            ->exists();

        return response()->json([
            'disponible' => !$exists,
            'apodo' => $request->apodo,
            'mensaje' => $exists ? 'Este apodo no está disponible.' : '¡Apodo disponible!',
        ]);
    }

    /**
     * Mostrar perfil público de un usuario
     */
    public function show($id)
    {
        $user = User::with(['perfil', 'perfil.fotos'])
            ->findOrFail($id);

        // Verificar si el usuario tiene perfil visible
        if ($user->perfil && $user->perfil->privacidad === 'privado' && Auth::id() !== $user->id) {
            abort(403, 'Este perfil es privado.');
        }

        return Inertia::render('Profile/Publico', [
            'usuario' => $user,
        ]);
    }

    /**
     * Eliminar cuenta (soft delete)
     */
    public function eliminarCuenta(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'password' => 'required|current_password',
            'confirmacion' => 'required|in:Eliminar mi cuenta',
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.current_password' => 'La contraseña es incorrecta.',
            'confirmacion.required' => 'Debes escribir la confirmación.',
            'confirmacion.in' => 'Debes escribir exactamente "Eliminar mi cuenta" para confirmar.',
        ]);

        // Cerrar sesión y eliminar
        Auth::logout();
        $user->delete();

        return redirect('/')->with('flash', [
            'toast' => [
                'type' => 'info',
                'title' => 'Cuenta eliminada',
                'message' => 'Tu cuenta ha sido eliminada. Lamentamos verte ir.',
                'duration' => 5000,
            ]
        ]);
    }

    /**
     * Actualizar el estado del perfil según completitud
     */
    private function actualizarEstadoPerfil(User $user)
    {
        $camposRequeridos = [
            'nombre',
            'apodo',
            'email',
            'fecha_nacimiento',
            'telefono',
            'ciudad',
        ];

        $completo = true;
        foreach ($camposRequeridos as $campo) {
            if (empty($user->$campo)) {
                $completo = false;
                break;
            }
        }

        // Si está completo y el estado actual es 'incompleto', actualizar
        if ($completo && $user->estado === 'incompleto') {
            $user->update(['estado' => 'completo']);
        } elseif (!$completo && $user->estado === 'completo') {
            $user->update(['estado' => 'incompleto']);
        }
    }
}