<?php

use App\Http\Controllers\Admin\CobroController;
use App\Http\Controllers\Admin\ContenidoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\InvitacionController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\SeguridadController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    // Invitados (no autenticados como admin)
    Route::middleware('guest:admin')->group(function () {
        // Redirigir al login unificado con parámetro type=admin
        Route::get('/login', function () {
            return redirect()->route('login', ['type' => 'admin']);
        })->name('login');
    });

    // Protegidas (solo admin autenticado)
    Route::middleware('auth:admin')->group(function () {
        // Logout de admin usando el controlador unificado
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // --- Usuarios ---
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{usuario}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::patch('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{usuario}/bloquear', [UsuarioController::class, 'toggleBloqueo'])->name('usuarios.bloquear');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        // --- Cobros y Pagos ---
        Route::get('/cobros-y-pagos', [CobroController::class, 'index'])->name('cobros.index');
        Route::get('/cobros-y-pagos/exportar', [CobroController::class, 'exportar'])->name('cobros.exportar');
        Route::post('/cobros-y-pagos/{cobro}/aprobar', [CobroController::class, 'aprobar'])->name('cobros.aprobar');
        Route::post('/cobros-y-pagos/{cobro}/reembolsar', [CobroController::class, 'reembolsar'])->name('cobros.reembolsar');

        // --- Invitaciones ---
        Route::get('/invitaciones', [InvitacionController::class, 'index'])->name('invitaciones.index');
        Route::get('/invitaciones/crear', [InvitacionController::class, 'create'])->name('invitaciones.create');
        Route::get('/invitaciones/codigos', [InvitacionController::class, 'codigos'])->name('invitaciones.codigos');
        Route::post('/invitaciones', [InvitacionController::class, 'store'])->name('invitaciones.store');
        Route::delete('/invitaciones/{invitacion}', [InvitacionController::class, 'destroy'])->name('invitaciones.destroy');

        // --- Eventos ---
        Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
        Route::get('/eventos/crear', [EventoController::class, 'create'])->name('eventos.create');
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
        Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');
        Route::get('/eventos/{evento}/editar', [EventoController::class, 'edit'])->name('eventos.edit');
        Route::patch('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
        Route::delete('/eventos/{evento}', [EventoController::class, 'destroy'])->name('eventos.destroy');

        // --- Contenido ---
        Route::get('/contenido', [ContenidoController::class, 'index'])->name('contenido.index');
        Route::get('/contenido/crear', [ContenidoController::class, 'create'])->name('contenido.create');
        Route::post('/contenido', [ContenidoController::class, 'store'])->name('contenido.store');
        Route::get('/contenido/{contenido}', [ContenidoController::class, 'show'])->name('contenido.show');

        // --- Shop ---
        Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
        Route::get('/shop/exportar', [ShopController::class, 'exportar'])->name('shop.exportar');
        Route::get('/shop/{pedido}', [ShopController::class, 'show'])->name('shop.show');
        Route::post('/shop/{pedido}/estado', [ShopController::class, 'actualizarEstado'])->name('shop.actualizar-estado');

        // --- Reportes ---
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::post('/reportes/{reporte}/revisar', [ReporteController::class, 'marcarRevisado'])->name('reportes.revisar');
        Route::post('/reportes/{reporte}/resolver', [ReporteController::class, 'resolver'])->name('reportes.resolver');
        Route::post('/reportes/{reporte}/bloquear', [ReporteController::class, 'bloquearReportado'])->name('reportes.bloquear');
        Route::delete('/reportes/{reporte}', [ReporteController::class, 'destroy'])->name('reportes.destroy');

        // --- Modulares / Coming Soon ---
        Route::get('/mensajes', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Mensajes']))->name('mensajes.index');
        Route::get('/configuracion', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Configuración']))->name('configuracion.index');
        Route::get('/soporte', fn () => \Inertia\Inertia::render('Admin/ComingSoon', ['modulo' => 'Soporte']))->name('soporte.index');

        // --- Seguridad ---
        Route::get('/seguridad', [SeguridadController::class, 'index'])->name('seguridad.index');
        Route::patch('/seguridad/perfil', [SeguridadController::class, 'actualizarPerfil'])->name('seguridad.perfil');
        Route::patch('/seguridad/password', [SeguridadController::class, 'actualizarPassword'])->name('seguridad.password');
        Route::post('/seguridad/{administrador}/toggle-activo', [SeguridadController::class, 'toggleActivo'])->name('seguridad.toggle-activo');
    });
});