<?php

use App\Http\Controllers\Admin\CobroController;
use App\Http\Controllers\Admin\ConfiguracionController;
use App\Http\Controllers\Admin\ContenidoController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventoController;
use App\Http\Controllers\Admin\InvitacionController;
use App\Http\Controllers\Admin\ModeracionController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\SeguridadController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\SoporteController;
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
        Route::post('/usuarios/{usuario}/bloquear', [UsuarioController::class, 'toggleBloqueo'])->name('usuarios.toggle-bloqueo');
        Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

        // --- Cobros y Pagos ---
        Route::get('/cobros-y-pagos', [CobroController::class, 'index'])->name('cobros.index');
        Route::get('/cobros-y-pagos/exportar', [CobroController::class, 'exportar'])->name('cobros.exportar');
        Route::get('/cobros-y-pagos/todas', [CobroController::class, 'transacciones'])->name('cobros.transacciones');
        Route::post('/cobros-y-pagos/{cobro}/aprobar', [CobroController::class, 'aprobar'])->name('cobros.aprobar');
        Route::post('/cobros-y-pagos/{cobro}/reembolsar', [CobroController::class, 'reembolsar'])->name('cobros.reembolsar');
        Route::get('/cobros-y-pagos/{cobro}', [CobroController::class, 'show'])->name('cobros.show');

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
        Route::get('/eventos/todos', [EventoController::class, 'todos'])->name('eventos.todos');
        Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');
        Route::get('/eventos/{evento}/editar', [EventoController::class, 'edit'])->name('eventos.edit');
        Route::patch('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
        Route::delete('/eventos/{evento}', [EventoController::class, 'destroy'])->name('eventos.destroy');

        // --- Contenido (solo monitoreo: index + show, sin crear/editar/eliminar) ---
        Route::get('/contenido', [ContenidoController::class, 'index'])->name('contenido.index');
        Route::get('/contenido/{contenido}', [ContenidoController::class, 'show'])->name('contenido.show');

        // --- Shop (Pedidos) ---
        Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
        Route::get('/shop/exportar', [ShopController::class, 'exportar'])->name('shop.exportar');
        Route::get('/shop/todos', [ShopController::class, 'todos'])->name('shop.todos');
        Route::get('/shop/{pedido}', [ShopController::class, 'show'])->name('shop.show');
        Route::post('/shop/{pedido}/estado', [ShopController::class, 'actualizarEstado'])->name('shop.actualizar-estado');

        // --- Productos ---
        Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
        Route::get('/productos/crear', [ProductoController::class, 'create'])->name('productos.create');
        Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
        Route::get('/productos/todos', [ProductoController::class, 'todos'])->name('productos.todos');
        Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');
        Route::get('/productos/{producto}/editar', [ProductoController::class, 'edit'])->name('productos.edit');
        Route::patch('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');

        // --- Reportes analíticos (hub + reportes individuales, PDF/Excel) ---
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/{tipo}/exportar-pdf', [ReporteController::class, 'exportarPdf'])->name('reportes.exportar-pdf');
        Route::get('/reportes/{tipo}/exportar-excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportar-excel');
        Route::get('/reportes/{tipo}', [ReporteController::class, 'detalle'])->name('reportes.detalle');

        // --- Soporte (moderación de reportes entre usuarios: acoso, perfil falso, spam...) ---
        Route::get('/soporte', [ModeracionController::class, 'index'])->name('soporte.index');
        Route::post('/soporte/{reporte}/revisar', [ModeracionController::class, 'marcarRevisado'])->name('soporte.revisar');
        Route::post('/soporte/{reporte}/resolver', [ModeracionController::class, 'resolver'])->name('soporte.resolver');
        Route::post('/soporte/{reporte}/bloquear', [ModeracionController::class, 'bloquearReportado'])->name('soporte.bloquear');
        Route::delete('/soporte/{reporte}', [ModeracionController::class, 'destroy'])->name('soporte.destroy');

        // --- Seguridad ---
        Route::get('/seguridad', [SeguridadController::class, 'index'])->name('seguridad.index');
        Route::patch('/seguridad/email', [SeguridadController::class, 'actualizarEmail'])->name('seguridad.email');
        Route::patch('/seguridad/password', [SeguridadController::class, 'actualizarPassword'])->name('seguridad.password');
        Route::post('/seguridad/administradores/{administrador}/toggle', [SeguridadController::class, 'toggleActivo'])->name('seguridad.toggle-activo');

        // --- Configuración ---
        Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::patch('/configuracion', [ConfiguracionController::class, 'actualizar'])->name('configuracion.actualizar');

        // --- Mensajes (bandeja de soporte admin ↔ usuario) ---
        Route::get('/mensajes', [SoporteController::class, 'index'])->name('mensajes.index');
        Route::post('/mensajes/iniciar', [SoporteController::class, 'iniciar'])->name('mensajes.iniciar');
        Route::post('/mensajes/{soporte}/enviar', [SoporteController::class, 'enviar'])->name('mensajes.enviar');
        Route::post('/mensajes/{soporte}/cerrar', [SoporteController::class, 'cerrar'])->name('mensajes.cerrar');
    });
});