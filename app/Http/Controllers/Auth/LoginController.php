<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de login unificado
     */
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Procesa el inicio de sesión (tanto para usuarios como administradores)
     */
    public function login(Request $request)
    {
        Log::info('Intento de inicio de sesión', [
            'nickname' => $request->nickname,
            'ip' => $request->ip()
        ]);

        // Validación de los campos
        $request->validate([
            'nickname' => 'required|string|max:50',
            'password' => 'required|string|min:6',
            'remember' => 'boolean',
        ], [
            'nickname.required' => 'El nickname es obligatorio',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        // PRIMERO: Buscar en usuarios normales con su perfil
        $user = User::where('apodo', $request->nickname)
            ->with('perfil') // Cargar la relación perfil
            ->first();
        
        // SEGUNDO: Si no existe en usuarios, buscar en administradores
        if (!$user) {
            $admin = Administrador::where('nickname', $request->nickname)->first();
            
            if ($admin && Hash::check($request->password, $admin->password)) {
                // Es un administrador
                return $this->loginAdmin($admin, $request);
            }
        }

        // Si existe usuario normal y la contraseña es correcta
        if ($user && Hash::check($request->password, $user->password)) {
            return $this->loginUser($user, $request);
        }

        // Si llegamos aquí, las credenciales son incorrectas
        Log::warning('Intento de login fallido - credenciales incorrectas', [
            'nickname' => $request->nickname,
            'ip' => $request->ip()
        ]);
        
        return back()->withErrors([
            'login' => 'Nickname y/o contraseña incorrecta'
       ])->onlyInput('nickname');
    }

    /**
     * Login para usuarios normales
     */
    private function loginUser($user, Request $request)
    {
        // Verificar si el usuario está activo
        if ($user->estado === 'inactivo' || $user->estado === 'suspendido') {
            Log::warning('Intento de login - cuenta inactiva', [
                'user_id' => $user->id,
                'estado' => $user->estado,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'login' => 'Tu cuenta está inactiva o suspendida. Contacta al soporte.'
            ])->onlyInput('nickname');
        }

        // Iniciar sesión como usuario
        Auth::guard('web')->login($user, $request->remember ?? false);
        
        // Guardar el perfil en la sesión para acceso rápido
        if ($user->perfil) {
            session()->put('user_perfil', $user->perfil);
        }
        
        Log::info('Inicio de sesión exitoso - Usuario', [
            'user_id' => $user->id,
            'nickname' => $user->apodo,
            'email' => $user->email,
            'rol' => $user->rol,
            'ip' => $request->ip()
        ]);

        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('inicio'))
            ->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => '¡Bienvenido de nuevo!',
                    'message' => 'Has iniciado sesión correctamente, ' . ($user->nombre ?? $user->apodo) . '.',
                    'duration' => 5000,
                ]
            ]);
    }

    /**
     * Login para administradores
     */
    private function loginAdmin($admin, Request $request)
    {
        // Verificar si el admin está activo
        if (!$admin->esta_activo) {
            Log::warning('Intento de login admin - cuenta inactiva', [
                'admin_id' => $admin->id,
                'nickname' => $admin->nickname,
                'email' => $admin->email,
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'login' => 'Esta cuenta de administrador está inactiva.'
            ])->onlyInput('nickname');
        }

        // Iniciar sesión como administrador
        Auth::guard('admin')->login($admin, $request->remember ?? false);
        
        Log::info('Inicio de sesión exitoso - Administrador', [
            'admin_id' => $admin->id,
            'nickname' => $admin->nickname,
            'email' => $admin->email,
            'rol' => $admin->rol,
            'ip' => $request->ip()
        ]);

        $request->session()->regenerate();

        // Actualizar último acceso
        $admin->ultimo_acceso_en = now();
        $admin->save();

        return redirect()->intended(route('admin.dashboard'))
            ->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => '¡Bienvenido Administrador!',
                    'message' => 'Has iniciado sesión correctamente, ' . ($admin->nombre ?? $admin->nickname) . '.',
                    'duration' => 5000,
                ]
            ]);
    }

    /**
     * Cierra la sesión (tanto para usuarios como administradores)
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $admin = Auth::guard('admin')->user();
        
        $userType = $user ? 'Usuario' : ($admin ? 'Administrador' : 'Desconocido');
        $userId = $user?->id ?? $admin?->id ?? null;
        $userIdentifier = $user?->apodo ?? $admin?->nickname ?? null;
        
        Log::info('Cierre de sesión', [
            'tipo' => $userType,
            'id' => $userId,
            'nickname' => $userIdentifier,
            'ip' => $request->ip()
        ]);

        // Limpiar sesión de perfil
        session()->forget('user_perfil');

        // Cerrar sesión del guard que esté activo
        if (Auth::check()) {
            Auth::logout();
        }
        
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userType === 'Administrador') {
            return redirect()->route('admin.login')
                ->with('flash', [
                    'toast' => [
                        'type' => 'info',
                        'title' => 'Sesión cerrada',
                        'message' => 'Has cerrado sesión del panel de administración correctamente.',
                        'duration' => 3000,
                    ]
                ]);
        }

        return redirect()->route('login')
            ->with('flash', [
                'toast' => [
                    'type' => 'info',
                    'title' => 'Sesión cerrada',
                    'message' => 'Has cerrado sesión correctamente.',
                    'duration' => 3000,
                ]
            ]);
    }
}