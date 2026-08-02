<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        Log::info('Intento de inicio de sesión', [
            'nickname' => $request->nickname,
            'ip' => $request->ip()
        ]);

        // Validación de los campos
        $request->validate([
            'nickname' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'remember' => 'boolean',
        ], [
            'nickname.required' => 'El nickname es obligatorio',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        // Buscar usuario por apodo (nickname)
        $user = User::where('apodo', $request->nickname)->first();

        // Verificar si el usuario existe y la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Intento de login fallido - credenciales incorrectas', [
                'nickname' => $request->nickname,
                'ip' => $request->ip()
            ]);
            
            // SOLO UN MENSAJE DE ERROR - SIN TOAST
            return back()->withErrors([
                'login' => 'Usuario y/o contraseña incorrecta'
            ])->onlyInput('nickname');
        }

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

        // Iniciar sesión
        Auth::login($user, $request->remember ?? false);
        
        Log::info('Inicio de sesión exitoso', [
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

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        Log::info('Cierre de sesión', [
            'user_id' => $user?->id,
            'nickname' => $user?->apodo,
            'ip' => $request->ip()
        ]);

        Auth::logout();
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