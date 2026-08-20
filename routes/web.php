<?php

use App\Http\Controllers\Auth\InviteRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Usuario\InicioController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Usuario\ProfileController;
use App\Http\Controllers\Usuario\ComunidadController;
use App\Http\Controllers\Usuario\EventoController;
use App\Http\Controllers\Usuario\ReservaController;
use App\Http\Controllers\Usuario\DescubrirController;
use App\Http\Controllers\Usuario\ShopController;
use App\Http\Controllers\Usuario\MensajeController;
use App\Http\Controllers\Usuario\PerfilVerController;
use App\Http\Controllers\Usuario\UserController;
use App\Http\Controllers\Creador\CreatorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// =======================================================================
// RUTAS PÚBLICAS
// =======================================================================

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
    // RUTA PRINCIPAL - INICIO
    // ============================================
    Route::get('/inicio', [InicioController::class, 'index'])->name('inicio');
    
    // ============================================
    // DASHBOARD (redirección)
    // ============================================
    Route::get('/dashboard', function () {
        return redirect()->route('inicio');
    })->name('dashboard');
    
    // ============================================
    // PERFIL - COMPLETAR
    // ============================================
    Route::get('/perfil/completar', [ProfileController::class, 'completar'])->name('perfil.completar');
    Route::post('/perfil/guardar', [ProfileController::class, 'guardar'])->name('perfil.guardar');
    Route::post('/perfil/borrador', [ProfileController::class, 'borrador'])->name('perfil.borrador');
    
    // ============================================
    // PERFIL - VER Y ACTUALIZAR
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
    // CONFIGURACIÓN
    // ============================================
    Route::get('/configuracion', function () {
        return Inertia::render('Settings');
    })->name('configuracion');
    
    // ============================================
    // USUARIO - DATOS PERSONALES
    // ============================================
    Route::get('/profile/usuario', [UserController::class, 'edit'])->name('profile.usuario');
    Route::put('/usuario/actualizar', [UserController::class, 'actualizar'])->name('usuario.actualizar');
    
    // ============================================
    // USUARIO - CAMBIAR CONTRASEÑA
    // ============================================
    Route::put('/usuario/cambiar-password', [UserController::class, 'cambiarPassword'])
        ->name('usuario.cambiar-password');
    
    // ============================================
    // USUARIO - AVATAR Y VERIFICACIÓN
    // ============================================
    Route::post('/usuario/avatar', [UserController::class, 'actualizarAvatar'])->name('usuario.avatar');
    Route::get('/usuario/verificar-apodo', [UserController::class, 'verificarApodo'])->name('usuario.verificar-apodo');
    Route::delete('/usuario/eliminar', [UserController::class, 'eliminarCuenta'])->name('usuario.eliminar');
    
    // ============================================
    // FAVORITOS
    // ============================================
    Route::get('/favoritos', function () {
        return Inertia::render('Favorites');
    })->name('favoritos');
    
    // ============================================
    // DESCUBRIR
    // ============================================
    Route::get('/descubrir', [DescubrirController::class, 'index'])->name('descubrir');
    Route::post('/descubrir/pasar', [DescubrirController::class, 'pasar'])->name('descubrir.pasar');
    Route::post('/descubrir/conectar', [DescubrirController::class, 'conectar'])->name('descubrir.conectar');
    Route::post('/descubrir/destacar', [DescubrirController::class, 'destacar'])->name('descubrir.destacar');
    
    // ============================================
    // EVENTOS
    // ============================================
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');
    Route::get('/eventos/buscar', [EventoController::class, 'buscar'])->name('eventos.buscar');
    Route::get('/eventos/ciudades', [EventoController::class, 'ciudades'])->name('eventos.ciudades');
    
    // ============================================
    // RESERVAS
    // ============================================
    Route::get('/eventos/{evento}/reservar', [ReservaController::class, 'crear'])->name('eventos.reservar');
    Route::post('/eventos/reserva/procesar-pago', [ReservaController::class, 'procesarPago'])
        ->name('eventos.reserva.procesar-pago');
    Route::get('/eventos/reserva/comprobante/{reserva}', [ReservaController::class, 'comprobante'])
        ->name('eventos.reserva.comprobante');
    Route::get('/eventos/reserva/pdf/{reserva}', [ReservaController::class, 'exportarPdf'])
        ->name('eventos.reserva.pdf');
    Route::delete('/eventos/reserva/{reserva}/cancelar', [ReservaController::class, 'cancelar'])
        ->name('eventos.reserva.cancelar');
    
    // ============================================
    // TIENDA - SHOP
    // ============================================
    Route::get('/tienda', [ShopController::class, 'index'])->name('tienda');
    Route::get('/tienda/filtrar', [ShopController::class, 'filtrar'])->name('tienda.filtrar');
    Route::get('/tienda/{id}', [ShopController::class, 'show'])->name('tienda.show');
    
    // ============================================
    // MENSAJES
    // ============================================
    Route::get('/mensajes', [MensajeController::class, 'index'])->name('mensajes');
    Route::post('/mensajes/enviar', [MensajeController::class, 'enviar'])->name('mensajes.enviar');
    Route::post('/mensajes/marcar-leidos', [MensajeController::class, 'marcarLeidos'])->name('mensajes.marcar-leidos');
    Route::get('/mensajes/{chatId}', [MensajeController::class, 'getMensajes'])->name('mensajes.obtener');
    
    // ============================================
    // COMUNIDAD
    // ============================================
    Route::get('/comunidad', [ComunidadController::class, 'index'])->name('comunidad.index');
    Route::post('/comunidad/publicar', [ComunidadController::class, 'crearPublicacion'])->name('comunidad.publicar');
    Route::post('/comunidad/like/{publicacion}', [ComunidadController::class, 'likePublicacion'])->name('comunidad.like');
    Route::get('/comunidad/comentarios/{publicacion}', [ComunidadController::class, 'obtenerComentarios'])
        ->name('comunidad.comentarios');
    Route::post('/comunidad/comentar/{publicacion}', [ComunidadController::class, 'crearComentario'])
        ->name('comunidad.comentar');
    Route::delete('/comunidad/publicacion/{publicacion}', [ComunidadController::class, 'eliminarPublicacion'])
        ->name('comunidad.eliminar');

    // ============================================
    // CREADOR - WIZARD
    // ============================================
    Route::get('/creador', [CreatorController::class, 'index'])->name('creador.index');
    Route::post('/creador/perfil', [CreatorController::class, 'guardarPerfil'])->name('creador.perfil.guardar');
    Route::post('/creador/verificacion', [CreatorController::class, 'guardarVerificacion'])
        ->name('creador.verificacion.guardar');
    Route::post('/creador/monetizacion', [CreatorController::class, 'guardarMonetizacion'])
        ->name('creador.monetizacion.guardar');
    Route::post('/creador/privacidad', [CreatorController::class, 'guardarPrivacidad'])
        ->name('creador.privacidad.guardar');
    Route::post('/creador/publicar', [CreatorController::class, 'publicar'])->name('creador.publicar');
    
    // ============================================
    // CREADOR - DASHBOARD
    // ============================================
    Route::get('/creador/comunidad', [CreatorController::class, 'comunidad'])->name('creador.comunidad');
    Route::get('/creador/dashboard', [CreatorController::class, 'dashboard'])->name('creador.dashboard');
    
    // ============================================
    // CREADOR - CONFIGURACIÓN DE MONETIZACIÓN
    // ============================================
    Route::post('/creador/configuracion-monetizacion', [CreatorController::class, 'guardarConfiguracionMonetizacion'])
        ->name('creador.configuracion.monetizacion.guardar');
    Route::get('/creador/configuracion-monetizacion', [CreatorController::class, 'obtenerConfiguracionMonetizacion'])
        ->name('creador.configuracion.monetizacion.obtener');
    Route::post('/creador/tarjeta/registrar', [CreatorController::class, 'registrarTarjeta'])
        ->name('creador.tarjeta.registrar');
    Route::delete('/creador/tarjeta/eliminar', [CreatorController::class, 'eliminarTarjeta'])
        ->name('creador.tarjeta.eliminar');
    
    // ============================================
    // CREADOR - SUBIR ARCHIVOS
    // ============================================
    Route::post('/creador/subir-selfie', [CreatorController::class, 'subirSelfie'])->name('creador.subir.selfie');
    Route::post('/creador/subir-fotos-verificacion', [CreatorController::class, 'subirFotosVerificacion'])
        ->name('creador.subir.fotos-verificacion');
    Route::post('/creador/subir-identificacion', [CreatorController::class, 'subirFotosVerificacion'])
        ->name('creador.subir.identificacion');
    Route::post('/creador/subir-documento', [CreatorController::class, 'subirDocumento'])
        ->name('creador.subir.documento');
    Route::delete('/creador/eliminar-documento', [CreatorController::class, 'eliminarDocumento'])
        ->name('creador.eliminar.documento');
    Route::post('/creador/subir-portada', [CreatorController::class, 'subirFotoPortada'])
        ->name('creador.subir.portada');
    Route::get('/creador/foto-portada', [CreatorController::class, 'getFotoPortada'])
        ->name('creador.foto.portada');
    
    // ============================================
    // CREADOR - FOTOS DE PERFIL
    // ============================================
    Route::get('/creador/fotos-perfil', [CreatorController::class, 'getFotosPerfil'])
        ->name('creador.getFotosPerfil');
    Route::post('/creador/subir-fotos-perfil', [CreatorController::class, 'subirFotosPerfil'])
        ->name('creador.subirFotosPerfil');
    Route::post('/creador/establecer-principal', [CreatorController::class, 'establecerPrincipal'])
        ->name('creador.establecerPrincipal');
    Route::delete('/creador/eliminar-foto-perfil', [CreatorController::class, 'eliminarFotoPerfil'])
        ->name('creador.eliminarFotoPerfil');
});

// =======================================================================
// RUTA DE FALLBACK (404)
// =======================================================================

Route::fallback(function () {
    return Inertia::render('Errors/404');
});