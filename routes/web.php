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
use App\Http\Controllers\Usuario\PedidoController;
use App\Http\Controllers\Usuario\MensajeController;
use App\Http\Controllers\Usuario\PerfilVerController;
use App\Http\Controllers\Usuario\UserController;
use App\Http\Controllers\Creador\CreatorController;
use App\Http\Controllers\Creador\ComunidadCreadorController;
use App\Http\Controllers\Creador\EditarPerfilCreadorController;
use App\Http\Controllers\Creador\NuevoContenidoController;
use App\Http\Controllers\Usuario\InteraccionContenidoController;
use App\Http\Controllers\Usuario\SuscripcionCreadorController;
use App\Http\Controllers\Usuario\SuscripcionController;
use App\Http\Controllers\Usuario\PerfilCreadorPublicoController;
use App\Http\Controllers\Usuario\ChatController;
use App\Http\Controllers\Usuario\LlamadaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Usuario\NotificacionController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Usuario\ReporteController;

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

// Verificación de correo (paso previo a crear la cuenta)
Route::post('/register/invite/enviar-codigo', [InviteRegisterController::class, 'enviarCodigoVerificacion'])
    ->name('register.invite.enviar-codigo');

Route::post('/register/invite', [InviteRegisterController::class, 'register'])
    ->name('register.invite.store');

// Login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Verificación de correo en el login (cuentas creadas por un admin, o
// registros interrumpidos a medio camino)
Route::post('/login/verificar', [LoginController::class, 'verificarCodigoLogin'])->name('login.verificar-codigo');
Route::post('/login/verificar/reenviar', [LoginController::class, 'reenviarCodigoLogin'])->name('login.reenviar-codigo');
Route::post('/login/verificar/cancelar', [LoginController::class, 'cancelarVerificacionLogin'])->name('login.cancelar-verificacion');

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
// RUTAS DE TIENDA - PÚBLICAS (fuera de autenticación)
// =======================================================================

Route::get('/tienda', [ShopController::class, 'index'])->name('tienda');
Route::get('/tienda/filtrar', [ShopController::class, 'filtrar'])->name('tienda.filtrar');

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
    // DESCUBRIR - TODAS LAS RUTAS JUNTAS
    // ============================================
    Route::get('/descubrir', [DescubrirController::class, 'index'])->name('descubrir');
    Route::post('/descubrir/pasar', [DescubrirController::class, 'pasar'])->name('descubrir.pasar');
    Route::post('/descubrir/conectar', [DescubrirController::class, 'conectar'])->name('descubrir.conectar');
    Route::post('/descubrir/destacar', [DescubrirController::class, 'destacar'])->name('descubrir.destacar');
    Route::post('/descubrir/mensaje-flash', [DescubrirController::class, 'enviarMensajeFlash'])
        ->name('descubrir.mensaje-flash');
    
    // ✅ ============================================
    // ✅ LIKES RECIBIDOS - NUEVAS RUTAS
    // ✅ ============================================
    Route::post('/descubrir/aceptar-like', [DescubrirController::class, 'aceptarLike'])->name('descubrir.aceptar-like');
    Route::post('/descubrir/rechazar-like', [DescubrirController::class, 'rechazarLike'])->name('descubrir.rechazar-like');
    Route::get('/descubrir/perfil', [DescubrirController::class, 'obtenerPerfil'])->name('descubrir.perfil');

// ✅ LIKES RECIBIDOS - NUEVAS RUTAS
Route::post('/descubrir/aceptar-like', [DescubrirController::class, 'aceptarLike'])->name('descubrir.aceptar-like');
Route::post('/descubrir/rechazar-like', [DescubrirController::class, 'rechazarLike'])->name('descubrir.rechazar-like');
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
    // TIENDA - CARRITO, CHECKOUT Y PEDIDOS
    // ============================================
    Route::post('/tienda/carrito/sincronizar', [ShopController::class, 'sincronizarCarrito'])
        ->name('tienda.carrito.sincronizar');
    Route::get('/tienda/carrito', [ShopController::class, 'carrito'])->name('tienda.carrito');
    Route::get('/tienda/checkout', [ShopController::class, 'checkout'])->name('tienda.checkout');
    Route::post('/tienda/pedido/confirmar', [ShopController::class, 'confirmarPedido'])
        ->name('tienda.pedido.confirmar');
    Route::get('/tienda/pedido/exito/{id}', [ShopController::class, 'pedidoExito'])
        ->name('tienda.pedido.exito');
    
    // ============================================
    // PEDIDOS - GESTIÓN DE PEDIDOS DEL USUARIO
    // ============================================
    Route::get('/tienda/mis-pedidos', [PedidoController::class, 'index'])->name('tienda.mis-pedidos');
    Route::get('/tienda/pedido/{id}', [PedidoController::class, 'show'])->name('tienda.pedido.show');
    Route::delete('/tienda/pedido/{id}/cancelar', [PedidoController::class, 'cancelar'])
        ->name('tienda.pedido.cancelar');
    
    // ============================================
    // MENSAJES - VERSIÓN ORIGINAL (MensajeController)
    // ============================================
    Route::get('/mensajes', [MensajeController::class, 'index'])->name('mensajes');
    Route::post('/mensajes/enviar', [MensajeController::class, 'enviar'])->name('mensajes.enviar');
    Route::post('/mensajes/marcar-leidos', [MensajeController::class, 'marcarLeidos'])->name('mensajes.marcar-leidos');
    Route::get('/mensajes/{chatId}', [MensajeController::class, 'getMensajes'])->name('mensajes.obtener');
    
    // ============================================
    // CHAT - NUEVA VERSIÓN (ChatController)
    // ============================================
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/{chat}/mensajes', [ChatController::class, 'show'])->name('chats.mensajes');
    Route::post('/chats/{chat}/mensajes', [ChatController::class, 'store'])->name('chats.mensajes.store');
    Route::delete('/mensajes/{mensaje}', [ChatController::class, 'destroy'])->name('mensajes.destroy');
    Route::post('/chats/{chat}/marcar-leido', [ChatController::class, 'marcarLeido'])->name('chats.marcar-leido');
    Route::post('/chats/{chat}/escribiendo', [ChatController::class, 'escribiendo'])->name('chats.escribiendo');

    // ============================================
    // LLAMADAS (audio y video)
    // ============================================
    Route::post('/chats/{chat}/llamadas', [LlamadaController::class, 'store'])->name('llamadas.store');
    Route::post('/llamadas/{llamada}/contestar', [LlamadaController::class, 'contestar'])->name('llamadas.contestar');
    Route::post('/llamadas/{llamada}/colgar', [LlamadaController::class, 'colgar'])->name('llamadas.colgar');
    
    // ============================================
    // COMUNIDAD - PUBLICACIONES DE USUARIOS
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
    Route::delete('/comunidad/comentario/{comentario}', [ComunidadController::class, 'eliminarComentario'])
        ->name('comunidad.eliminar-comentario');

    // ============================================
    // INTERACCIONES CON CONTENIDO DE CREADORES
    // ============================================
    Route::post('/contenidos/{contenido}/like', [InteraccionContenidoController::class, 'toggleLike'])
        ->name('contenidos.like');
    Route::get('/contenidos/{contenido}/comentarios', [InteraccionContenidoController::class, 'comentarios'])
        ->name('contenidos.comentarios');
    Route::post('/contenidos/{contenido}/comentarios', [InteraccionContenidoController::class, 'comentar'])
        ->name('contenidos.comentarios.store');
    Route::delete('/contenido-comentarios/{comentario}', [InteraccionContenidoController::class, 'eliminarComentario'])
        ->name('contenido-comentarios.destroy');

    // ============================================
    // SUSCRIPCIÓN A CREADORES
    // ============================================
    Route::get('/creador/{creador}/{slug}/suscripcion', [SuscripcionCreadorController::class, 'mostrar'])
        ->name('creador.suscripcion.mostrar');
    Route::post('/creador/{creador}/suscripcion/procesar', [SuscripcionCreadorController::class, 'procesar'])
        ->name('creador.suscripcion.procesar');

    // ============================================
    // PERFIL PÚBLICO DEL CREADOR
    // ============================================
    Route::get('/creador/{id}/{slug?}', [PerfilCreadorPublicoController::class, 'show'])
        ->name('creador.publico.show');
    Route::get('/creador/{id}/contenidos', [PerfilCreadorPublicoController::class, 'contenidos'])
        ->name('creador.publico.contenidos');

    // ============================================
    // SUSCRIPCIONES DEL USUARIO
    // ============================================
    Route::get('/suscripciones', [SuscripcionController::class, 'index'])->name('suscripciones.index');
    Route::get('/suscripciones/{id}', [SuscripcionController::class, 'show'])->name('suscripciones.show');
    Route::post('/suscripciones/{id}/cancelar', [SuscripcionController::class, 'cancelar'])->name('suscripciones.cancelar');
    Route::post('/suscripciones/{id}/reactivar', [SuscripcionController::class, 'reactivar'])->name('suscripciones.reactivar');
    Route::get('/suscripciones/creador/{creadorId}', [SuscripcionController::class, 'verificarSuscripcion'])->name('suscripciones.verificar');
    Route::get('/suscripciones/estadisticas', [SuscripcionController::class, 'estadisticas'])->name('suscripciones.estadisticas');

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
    Route::get('/creador/perfil', [CreatorController::class, 'perfil'])->name('creador.perfil');
    Route::get('/creador/ganancias', [CreatorController::class, 'ganancias'])->name('creador.ganancias');
    
    // ============================================
    // CREADOR - DASHBOARD Y COMUNIDAD
    // ============================================
    Route::get('/creador/comunidad', [ComunidadCreadorController::class, 'index'])
        ->name('creador.comunidad');
    Route::get('/creador/dashboard', [CreatorController::class, 'dashboard'])->name('creador.dashboard');
    
    // ============================================
    // CREADOR - EDITAR PERFIL
    // ============================================
    Route::get('/creador/editar-perfil', [EditarPerfilCreadorController::class, 'index'])
        ->name('creador.editar-perfil');
    Route::post('/creador/editar-perfil', [EditarPerfilCreadorController::class, 'update'])
        ->name('creador.editar-perfil.update');
    Route::post('/creador/portada', [EditarPerfilCreadorController::class, 'subirPortada'])
        ->name('creador.portada.subir');
    Route::delete('/creador/portada', [EditarPerfilCreadorController::class, 'eliminarPortada'])
        ->name('creador.portada.eliminar');
    Route::post('/creador/foto-perfil', [EditarPerfilCreadorController::class, 'subirFotoPerfil'])
        ->name('creador.foto-perfil.subir');
    Route::post('/creador/foto-principal', [EditarPerfilCreadorController::class, 'setFotoPrincipal'])
        ->name('creador.foto-principal.set');
    Route::delete('/creador/foto-perfil/{foto_id}', [EditarPerfilCreadorController::class, 'eliminarFotoPerfil'])
        ->name('creador.foto-perfil.eliminar');

    // ============================================
    // CREADOR - NUEVO CONTENIDO
    // ============================================
    Route::get('/creador/nuevo-contenido', [NuevoContenidoController::class, 'index'])
        ->name('creador.nuevo-contenido');
    Route::post('/creador/nuevo-contenido', [NuevoContenidoController::class, 'store'])
        ->name('creador.nuevo-contenido.store');
    Route::post('/creador/contenido/archivo', [NuevoContenidoController::class, 'subirArchivo'])
        ->name('creador.contenido.archivo.subir');
    Route::delete('/creador/contenido/archivo', [NuevoContenidoController::class, 'eliminarArchivo'])
        ->name('creador.contenido.archivo.eliminar');

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
    Route::post('/reportes', [ReporteController::class, 'store'])->name('reportes.store');
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

    Route::get('/notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/marcar-leidas', [NotificacionController::class, 'marcarLeidas'])->name('notificaciones.marcar-leidas');
    Route::post('/notificaciones/{id}/marcar-leida', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.marcar-leida');
    Route::get('/notificaciones/nuevas', [NotificacionController::class, 'nuevas'])->name('notificaciones.nuevas');

});

// =======================================================================
// RUTA DE DETALLE DE PRODUCTO - DEBE IR AL FINAL
// =======================================================================
Route::get('/tienda/{id}', [ShopController::class, 'show'])
    ->where('id', '[0-9]+')
    ->name('tienda.show');

// =======================================================================
// RUTA DE FALLBACK (404)
// =======================================================================
Route::fallback(function () {
    return Inertia::render('Errors/404');
});

Route::post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware('auth');