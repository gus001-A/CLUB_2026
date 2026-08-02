<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    /**
     * Muestra el formulario de inicio de sesión del panel admin.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Procesa el inicio de sesión del administrador.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Las credenciales no coinciden con ningún administrador.',
            ])->onlyInput('email');
        }

        $admin = Auth::guard('admin')->user();

        if (! $admin->estaActivo()) {
            Auth::guard('admin')->logout();

            return back()->withErrors([
                'email' => 'Esta cuenta de administrador está inactiva o no ha sido verificada.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $admin->registrarAcceso($request->ip());

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Cierra la sesión del administrador.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}