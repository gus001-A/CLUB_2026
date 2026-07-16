<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CobroController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvitacionController;
use App\Http\Controllers\Admin\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    // Invitados (no autenticados como admin)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    // Protegidas (solo admin autenticado)
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // --- Usuarios ---
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::post('/usuarios/{usuario}/toggle-bloqueo', [UsuarioController::class, 'toggleBloqueo'])->name('usuarios.toggle-bloqueo');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        // --- Cobros y Pagos ---
        Route::get('/cobros-y-pagos', [CobroController::class, 'index'])->name('cobros.index');
        Route::get('/cobros-y-pagos/exportar', [CobroController::class, 'exportar'])->name('cobros.exportar');
        Route::post('/cobros-y-pagos/{cobro}/aprobar', [CobroController::class, 'aprobar'])->name('cobros.aprobar');
        Route::post('/cobros-y-pagos/{cobro}/reembolsar', [CobroController::class, 'reembolsar'])->name('cobros.reembolsar');
        // --- Invitaciones ---
        Route::get('/invitaciones', [InvitacionController::class, 'index'])->name('invitaciones.index');
        Route::get('/invitaciones/crear', [InvitacionController::class, 'create'])->name('invitaciones.create');
        Route::post('/invitaciones', [InvitacionController::class, 'store'])->name('invitaciones.store');
        Route::delete('/invitaciones/{invitacion}', [InvitacionController::class, 'destroy'])->name('invitaciones.destroy');
        Route::get('/eventos', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Eventos']))->name('eventos.index');
        Route::get('/contenido', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Contenido']))->name('contenido.index');
        Route::get('/shop', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Shop']))->name('shop.index');
        Route::get('/reportes', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Reportes']))->name('reportes.index');
        Route::get('/mensajes', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Mensajes']))->name('mensajes.index');
        Route::get('/configuracion', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Configuración']))->name('configuracion.index');
        Route::get('/seguridad', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Seguridad']))->name('seguridad.index');
        Route::get('/soporte', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Soporte']))->name('soporte.index');
    });
});