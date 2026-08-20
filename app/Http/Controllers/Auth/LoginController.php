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

        // PRIMERO: Buscar en administradores (por nickname o email)
        $admin = Administrador::where('nickname', $request->nickname)
            ->orWhere('email', $request->nickname)
            ->first();
        
        // Si existe administrador y la contraseña es correcta
        if ($admin && Hash::check($request->password, $admin->password)) {
            return $this->loginAdmin($admin, $request);
        }

        // SEGUNDO: Buscar en usuarios normales (por apodo o email)
        $user = User::where('apodo', $request->nickname)
            ->orWhere('email', $request->nickname)
            ->first();

        // Si existe usuario normal y la contraseña es correcta
        if ($user && Hash::check($request->password, $user->password)) {
            return $this->loginUser($user, $request);
        }

        // Si llegamos aquí, las credenciales son incorrectas
        return back()->withErrors([
            'login' => 'Nickname y/o contraseña incorrecta'
        ])->onlyInput('nickname');
    }

    /**
     * Login para administradores
     */
    private function loginAdmin($admin, Request $request)
    {
        // Verificar si el admin está activo
        if (!$admin->esta_activo) {
            return back()->withErrors([
                'login' => 'Esta cuenta de administrador está inactiva.'
            ])->onlyInput('nickname');
        }

        // Iniciar sesión como administrador
        Auth::guard('admin')->login($admin, $request->remember ?? false);

        $request->session()->regenerate();

        // Actualizar último acceso
        $admin->ultimo_acceso_en = now();
        $admin->save();

        // Redirigir al dashboard de admin
        return redirect()->route('admin.dashboard')
            ->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => '¡Bienvenido Administrador!',
                    'message' => 'Has iniciado sesión correctamente, ' . ($admin->nombre ?? $admin->nickname ?? $admin->email) . '.',
                    'duration' => 5000,
                ]
            ]);
    }

    /**
     * Login para usuarios normales
     */
    private function loginUser($user, Request $request)
    {
        // Verificar si el usuario está activo
        if ($user->estado === 'inactivo' || $user->estado === 'suspendido') {
            return back()->withErrors([
                'login' => 'Tu cuenta está inactiva o suspendida. Contacta al soporte.'
            ])->onlyInput('nickname');
        }

        try {
            // Intentar login con guard web
            Auth::guard('web')->login($user, $request->remember ?? false);
            
            // Verificar que el login fue exitoso
            if (!Auth::guard('web')->check()) {
                return back()->withErrors([
                    'login' => 'Error al iniciar sesión. Por favor intenta de nuevo.'
                ])->onlyInput('nickname');
            }

            // Guardar el perfil en la sesión para acceso rápido
            if ($user->perfil) {
                session()->put('user_perfil', $user->perfil);
            }

            $request->session()->regenerate();
            $request->session()->put('auth.password_confirmed_at', time());

            // Redirigir a inicio
            return redirect()->intended(route('inicio'))
                ->with('flash', [
                    'toast' => [
                        'type' => 'success',
                        'title' => '¡Bienvenido de nuevo!',
                        'message' => 'Has iniciado sesión correctamente, ' . ($user->nombre ?? $user->apodo) . '.',
                        'duration' => 5000,
                    ]
                ]);

        } catch (\Exception $e) {
            return back()->withErrors([
                'login' => 'Error al iniciar sesión: ' . $e->getMessage()
            ])->onlyInput('nickname');
        }
    }

    /**
     * Cierra la sesión (tanto para usuarios como administradores)
     */
    public function logout(Request $request)
    {
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