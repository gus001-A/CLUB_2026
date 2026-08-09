<?php

use App\Http\Controllers\Auth\InviteRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Usuario\InicioController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Usuario\ProfileController;
use App\Http\Controllers\Usuario\ComunidadController;
use App\Http\Controllers\Usuario\EventoController;
use App\Http\Controllers\Usuario\ReservaController; // ← NUEVO
use App\Http\Controllers\Usuario\DescubrirController;
use App\Http\Controllers\Usuario\ShopController;
use App\Http\Controllers\Usuario\MensajeController;
use App\Http\Controllers\Usuario\PerfilVerController;
use App\Http\Controllers\Usuario\UserController;
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
// RUTAS ADMIN (importadas desde admin.php)
// =======================================================================

require __DIR__.'/admin.php';

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
    Route::post('/perfil/actualizar', [ProfileController::class, 'actualizar'])->name('perfil.actualizar');
    
    // ============================================
    // PERFIL - VER Y ACTUALIZAR (CON CONTROLADOR) ✅
    // ============================================
    Route::get('/perfil/ver', [PerfilVerController::class, 'index'])->name('perfil.ver');
    Route::post('/perfil/actualizar', [PerfilVerController::class, 'actualizar'])->name('perfil.actualizar');
    
    // ============================================
    // PERFIL - VISTA GENERAL
    // ============================================
    Route::get('/perfil', function () {
        return redirect()->route('perfil.ver');
    })->name('perfil');
    
    // ============================================
    // CONFIGURACIÓN ✅
    // ============================================
    Route::get('/configuracion', function () {
        return Inertia::render('Settings');
    })->name('configuracion');
    
    // ============================================
    // USUARIO - DATOS PERSONALES ✅
    // ============================================
    Route::get('/profile/usuario', [UserController::class, 'edit'])
        ->name('profile.usuario');
    
    Route::put('/usuario/actualizar', [UserController::class, 'actualizar'])
        ->name('usuario.actualizar');
    
    // ============================================
    // USUARIO - CAMBIAR CONTRASEÑA ✅ (NUEVO)
    // ============================================
    Route::put('/usuario/cambiar-password', [UserController::class, 'cambiarPassword'])
        ->name('usuario.cambiar-password');
    
    // ============================================
    // USUARIO - AVATAR Y VERIFICACIÓN ✅
    // ============================================
    Route::post('/usuario/avatar', [UserController::class, 'actualizarAvatar'])
        ->name('usuario.avatar');
    
    Route::get('/usuario/verificar-apodo', [UserController::class, 'verificarApodo'])
        ->name('usuario.verificar-apodo');
    
    Route::delete('/usuario/eliminar', [UserController::class, 'eliminarCuenta'])
        ->name('usuario.eliminar');
    
    // ============================================
    // FAVORITOS
    // ============================================
    Route::get('/favoritos', function () {
        return Inertia::render('Favorites');
    })->name('favoritos');
    
    // ============================================
    // DESCUBRIR (CON CONTROLADOR)
    // ============================================
    Route::get('/descubrir', [DescubrirController::class, 'index'])->name('descubrir');
    Route::post('/descubrir/pasar', [DescubrirController::class, 'pasar'])->name('descubrir.pasar');
    Route::post('/descubrir/conectar', [DescubrirController::class, 'conectar'])->name('descubrir.conectar');
    Route::post('/descubrir/destacar', [DescubrirController::class, 'destacar'])->name('descubrir.destacar');
    
    // ============================================
    // EVENTOS (CON CONTROLADOR)
    // ============================================
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');
    
    // ============================================
    // EVENTOS - API Y FILTROS
    // ============================================
    Route::get('/eventos/buscar', [EventoController::class, 'buscar'])->name('eventos.buscar');
    Route::get('/eventos/ciudades', [EventoController::class, 'ciudades'])->name('eventos.ciudades');
    
    // ============================================
    // RESERVAS - GESTIÓN DE RESERVAS 🆕
    // ============================================
    // Lista de reservas del usuario
    Route::get('/eventos/reservas', [ReservaController::class, 'misReservas'])
        ->name('eventos.reservas');
    
    // Paso 1: Crear reserva (formulario)
    Route::get('/eventos/{id}/reservar', [ReservaController::class, 'crear'])
        ->name('eventos.reservar.crear');
    
    // Paso 1: Guardar reserva
    Route::post('/eventos/{id}/reservar', [ReservaController::class, 'store'])
        ->name('eventos.reservar.store');
    
    // Paso 2: Pago (formulario)
    Route::get('/eventos/reserva/{reservaId}/pago', [ReservaController::class, 'pago'])
        ->name('eventos.reserva.pago');
    
    // Paso 2: Procesar pago
    Route::post('/eventos/reserva/{reservaId}/pago', [ReservaController::class, 'procesarPago'])
        ->name('eventos.reserva.procesar-pago');
    
    // Paso 3: Éxito / Confirmación
    Route::get('/eventos/reserva/{reservaId}/exito', [ReservaController::class, 'exito'])
        ->name('eventos.reserva.exito');
    
    // Cancelar reserva
    Route::post('/eventos/reserva/{reservaId}/cancelar', [ReservaController::class, 'cancelar'])
        ->name('eventos.reserva.cancelar');
    
    // Verificar disponibilidad (API)
    Route::get('/eventos/{id}/disponibilidad', [ReservaController::class, 'verificarDisponibilidad'])
        ->name('eventos.disponibilidad');
    
    // ============================================
    // TIENDA - SHOP (CON CONTROLADOR)
    // ============================================
    Route::get('/tienda', [ShopController::class, 'index'])->name('tienda');
    Route::get('/tienda/filtrar', [ShopController::class, 'filtrar'])->name('tienda.filtrar');
    Route::get('/tienda/{id}', [ShopController::class, 'show'])->name('tienda.show');
    
    // ============================================
    // MENSAJES (CON CONTROLADOR)
    // ============================================
    Route::get('/mensajes', [MensajeController::class, 'index'])->name('mensajes');
    Route::post('/mensajes/enviar', [MensajeController::class, 'enviar'])->name('mensajes.enviar');
    Route::post('/mensajes/marcar-leidos', [MensajeController::class, 'marcarLeidos'])->name('mensajes.marcar-leidos');
    Route::get('/mensajes/{chatId}', [MensajeController::class, 'getMensajes'])->name('mensajes.obtener');
    
    // ============================================
    // COMUNIDAD - CON CONTROLADOR
    // ============================================
    Route::get('/comunidad', [ComunidadController::class, 'index'])->name('comunidad.index');
    
    // ============================================
    // RUTAS DE INTERACCIÓN DE LA COMUNIDAD
    // ============================================
    Route::post('/comunidad/publicar', [ComunidadController::class, 'crearPublicacion'])->name('comunidad.publicar');
    Route::post('/comunidad/like/{publicacion}', [ComunidadController::class, 'likePublicacion'])->name('comunidad.like');
    Route::get('/comunidad/comentarios/{publicacion}', [ComunidadController::class, 'obtenerComentarios'])->name('comunidad.comentarios');
    Route::post('/comunidad/comentar/{publicacion}', [ComunidadController::class, 'crearComentario'])->name('comunidad.comentar');
    Route::delete('/comunidad/publicacion/{publicacion}', [ComunidadController::class, 'eliminarPublicacion'])->name('comunidad.eliminar');
});

// =======================================================================
// RUTA DE FALLBACK (404)
// =======================================================================

Route::fallback(function () {
    return Inertia::render('Errors/404');
});