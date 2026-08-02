<?php

use App\Http\Controllers\Auth\InviteRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Usuarios\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [LandingController::class, 'index'])->name('home');

// =======================================================================
// RUTAS DE AUTENTICACIÓN
// =======================================================================

// Registro con invitación
Route::get('/register/invite', [InviteRegisterController::class, 'showRegistrationForm'])
    ->name('register.invite');

Route::post('/register/invite', [InviteRegisterController::class, 'register'])
    ->name('register.invite.store');

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registro estándar (si existe)
Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->name('register');

// =======================================================================
// RUTAS DE RECUPERACIÓN DE CONTRASEÑA
// =======================================================================

Route::get('/forgot-password', function () {
    return Inertia::render('Auth/ForgotPassword');
})->name('password.request');

Route::post('/forgot-password', function () {
    // Aquí va tu lógica de envío de correo
})->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return Inertia::render('Auth/ResetPassword', ['token' => $token]);
})->name('password.reset');

Route::post('/reset-password', function () {
    // Aquí va tu lógica de restablecimiento
})->name('password.update');

// =======================================================================
// RUTAS DE PÁGINAS LEGALES
// =======================================================================

Route::get('/terminos', function () {
    return Inertia::render('Terms');
})->name('terms');

Route::get('/privacidad', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacy');

Route::get('/cookies', function () {
    return Inertia::render('Cookies');
})->name('cookies');

// =======================================================================
// RUTAS PROTEGIDAS (requieren autenticación)
// =======================================================================
Route::middleware(['auth'])->group(function () {
    // ============================================
    // RUTA PRINCIPAL - INICIO (CON CONTROLADOR)
    // ============================================
    Route::get('/inicio', [InicioController::class, 'index'])->name('inicio');
    
    // ============================================
    // DASHBOARD (redirección después de registro/login)
    // ============================================
    Route::get('/dashboard', function () {
        return redirect()->route('inicio');
    })->name('dashboard');
    
    // ============================================
    // PERFIL - COMPLETAR (CON CONTROLADOR)
    // ============================================
    Route::get('/perfil/completar', [ProfileController::class, 'completar'])->name('perfil.completar');
    Route::post('/perfil/guardar', [ProfileController::class, 'guardar'])->name('perfil.guardar');
    Route::post('/perfil/borrador', [ProfileController::class, 'borrador'])->name('perfil.borrador');
    
    // ============================================
    // PERFIL - VISTA GENERAL
    // ============================================
    Route::get('/perfil', function () {
        return Inertia::render('Profile/Index');
    })->name('perfil');
    
    // ============================================
    // CONFIGURACIÓN
    // ============================================
    Route::get('/configuracion', function () {
        return Inertia::render('Settings');
    })->name('configuracion');
    
    // ============================================
    // FAVORITOS
    // ============================================
    Route::get('/favoritos', function () {
        return Inertia::render('Favorites');
    })->name('favoritos');
    
    // ============================================
    // DESCUBRIR
    // ============================================
    Route::get('/descubrir', function () {
        return Inertia::render('Descubrir');
    })->name('descubrir');
    
    // ============================================
    // EVENTOS
    // ============================================
    Route::get('/eventos', function () {
        return Inertia::render('Eventos');
    })->name('eventos');
    
    // ============================================
    // MENSAJES
    // ============================================
    Route::get('/mensajes', function () {
        return Inertia::render('Mensajes');
    })->name('mensajes');
    
    // ============================================
    // COMUNIDAD
    // ============================================
    Route::get('/comunidad', function () {
        return Inertia::render('Comunidad');
    })->name('comunidad');
});

// =======================================================================
// RUTAS DE FALLBACK (404)
// =======================================================================

Route::fallback(function () {
    return Inertia::render('Errors/404');
});