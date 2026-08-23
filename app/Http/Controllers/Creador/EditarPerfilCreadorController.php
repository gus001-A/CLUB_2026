<?php

namespace App\Http\Controllers\Creador;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Creador;
use App\Models\Perfil;
use App\Models\Fotos;
use App\Models\ConfiguracionMonetizacion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EditarPerfilCreadorController extends Controller
{
    /**
     * Muestra el formulario para editar el perfil del creador
     */
    public function index()
    {
        Log::info('=== EDITAR PERFIL CREADOR ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre, 'rol' => $user->rol]);

        // Verificar que el usuario sea creador
        if ($user->rol !== 'creador' || !$user->creador) {
            Log::warning('Usuario no es creador');
            return redirect()->route('creador.index')
                ->with('info', 'Completa el proceso para convertirte en creador.');
        }

        $creador = $user->creador;
        $user->load(['perfil.fotos']);

        // Obtener avatar
        $avatar = $this->getAvatarUrl($user);

        // Obtener fotos del perfil
        $fotosPerfil = [];
        if ($user->perfil) {
            $fotosPerfil = $user->perfil->fotos()
                ->orderBy('es_principal', 'desc')
                ->get()
                ->map(function ($foto) {
                    return [
                        'id' => $foto->id,
                        'url' => $foto->url,
                        'es_principal' => $foto->es_principal,
                        'ruta_foto' => $foto->ruta_foto,
                    ];
                });
        }

        // ✅ Obtener configuración de monetización desde el campo 'precios'
        $precios = $creador->precios ?? [];
        $configuracion = $creador->configuracionMonetizacion;

        // Datos del perfil
        $perfilData = [
            'nombre' => $user->nombre,
            'avatar' => $avatar,
            'biografia' => $creador->biografia ?? '',
            'categorias' => $creador->categorias ?? [],
            'es_premium' => $creador->es_premium ?? false,
            'tipo_contenido' => $creador->estadisticas['tipo_contenido'] ?? 'fotos',
            'estado_verificacion' => $creador->estado_verificacion ?? 'pendiente',
            // ✅ Datos de monetización desde 'precios'
            'monetizacion' => [
                'modelo' => $precios['modelo'] ?? 'suscripcion',
                'precio_personalizado' => $precios['precio_personalizado'] ?? null,
                'frecuencia' => $precios['frecuencia'] ?? 'Mensual',
                'precio_suscripcion' => $precios['suscripcion'] ?? 199.99,
                'precio_foto' => $precios['foto'] ?? 299.99,
                'precio_video' => $precios['video'] ?? 499.99,
            ]
        ];

        Log::info('Datos del perfil:', $perfilData);

        return Inertia::render('Creador/EditarPerfilCreador', [
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'avatar' => $avatar,
                'verificado' => $user->estado === 'verificado',
                'rol' => $user->rol,
            ],
            'perfil' => $perfilData,
            'fotosPerfil' => $fotosPerfil,
            'categoriasDisponibles' => $this->getCategoriasDisponibles(),
            'configuracionMonetizacion' => $configuracion,
            'footerColumnas' => $this->getFooterColumnas(),
        ]);
    }

    /**
     * Actualiza el perfil del creador
     */
    public function update(Request $request)
    {
        Log::info('=== ACTUALIZAR PERFIL CREADOR ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre]);

        $creador = $user->creador;
        if (!$creador) {
            Log::warning('Creador no encontrado');
            return redirect()->back()->with('error', 'No se encontró tu perfil de creador.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'biografia' => 'nullable|string|max:500',
            'categorias' => 'nullable|array',
            'categorias.*' => 'string|max:50',
            'es_premium' => 'boolean',
            'tipo_contenido' => 'nullable|in:fotos,videos,exclusivo',
            // ✅ Datos de monetización
            'monetizacion' => 'nullable|array',
            'monetizacion.modelo' => 'nullable|in:suscripcion,fotos,videos,exclusivo',
            'monetizacion.precio_personalizado' => 'nullable|numeric|min:0',
            'monetizacion.frecuencia' => 'nullable|in:Mensual,Trimestral,Semestral,Anual',
        ]);

        Log::info('Datos validados:', $validated);

        // Actualizar nombre del usuario
        if ($user->nombre !== $validated['nombre']) {
            $user->update(['nombre' => $validated['nombre']]);
            Log::info('Nombre actualizado');
        }

        // Actualizar datos del creador
        $creador->biografia = $validated['biografia'] ?? null;
        $creador->categorias = $validated['categorias'] ?? [];
        $creador->es_premium = $validated['es_premium'] ?? false;

        // Actualizar estadísticas
        $estadisticas = $creador->estadisticas ?? [];
        if ($validated['tipo_contenido']) {
            $estadisticas['tipo_contenido'] = $validated['tipo_contenido'];
        }

        $creador->estadisticas = $estadisticas;

        // ✅ Guardar configuración de monetización en el campo 'precios'
        if (isset($validated['monetizacion'])) {
            $precios = $creador->precios ?? [];
            
            // Datos base de la monetización
            $precios['modelo'] = $validated['monetizacion']['modelo'] ?? 'suscripcion';
            $precios['frecuencia'] = $validated['monetizacion']['frecuencia'] ?? 'Mensual';
            
            // Precios según el modelo seleccionado
            switch ($precios['modelo']) {
                case 'suscripcion':
                    $precios['suscripcion'] = 199.99;
                    $precios['foto'] = null;
                    $precios['video'] = null;
                    $precios['precio_personalizado'] = null;
                    break;
                case 'fotos':
                    $precios['suscripcion'] = null;
                    $precios['foto'] = 299.99;
                    $precios['video'] = null;
                    $precios['precio_personalizado'] = null;
                    break;
                case 'videos':
                    $precios['suscripcion'] = null;
                    $precios['foto'] = null;
                    $precios['video'] = 499.99;
                    $precios['precio_personalizado'] = null;
                    break;
                case 'exclusivo':
                    $precios['suscripcion'] = null;
                    $precios['foto'] = null;
                    $precios['video'] = null;
                    $precios['precio_personalizado'] = $validated['monetizacion']['precio_personalizado'] ?? null;
                    break;
                default:
                    $precios['suscripcion'] = 199.99;
                    $precios['foto'] = null;
                    $precios['video'] = null;
                    $precios['precio_personalizado'] = null;
                    break;
            }

            $creador->precios = $precios;
            Log::info('Precios actualizados:', $precios);

            // ✅ También actualizar o crear ConfiguracionMonetizacion
            $this->guardarConfiguracionMonetizacion($creador, $precios);
        }

        $creador->save();

        Log::info('Perfil actualizado:', ['creador_id' => $creador->id]);

        return redirect()->route('creador.perfil')
            ->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Guarda o actualiza la configuración de monetización en la tabla configuracion_monetizacion
     */
    private function guardarConfiguracionMonetizacion($creador, $precios)
    {
        try {
            // Determinar el precio según el modelo
            $precio = null;
            $modelo = $precios['modelo'] ?? 'suscripcion';
            
            switch ($modelo) {
                case 'suscripcion':
                    $precio = $precios['suscripcion'] ?? 199.99;
                    break;
                case 'fotos':
                    $precio = $precios['foto'] ?? 299.99;
                    break;
                case 'videos':
                    $precio = $precios['video'] ?? 499.99;
                    break;
                case 'exclusivo':
                    $precio = $precios['precio_personalizado'] ?? null;
                    break;
                default:
                    $precio = 199.99;
                    break;
            }

            $configuracion = ConfiguracionMonetizacion::updateOrCreate(
                ['creador_id' => $creador->id],
                [
                    'modelo_ingresos' => $modelo,
                    'precio_personalizado' => $precio,
                    'frecuencia_pago' => $precios['frecuencia'] ?? 'Mensual',
                    'estado' => 'activo',
                    'comision_plataforma' => 20.00,
                ]
            );

            Log::info('Configuración de monetización guardada:', [
                'config_id' => $configuracion->id,
                'modelo' => $modelo,
                'precio' => $precio
            ]);

        } catch (\Exception $e) {
            Log::error('Error al guardar configuración de monetización:', [
                'error' => $e->getMessage(),
                'creador_id' => $creador->id
            ]);
        }
    }

    /**
     * Sube una foto al perfil
     */
    public function subirFotoPerfil(Request $request)
    {
        Log::info('=== SUBIR FOTO PERFIL ===');
        $request->validate([
            'foto' => 'required|image|max:5120',
        ]);

        $user = Auth::user();

        if (!$user->perfil) {
            $perfil = Perfil::create([
                'usuario_id' => $user->id,
                'biografia' => '',
                'vistas' => 0,
            ]);
        } else {
            $perfil = $user->perfil;
        }

        $path = $request->file('foto')->store('perfiles/fotos', 'public');
        
        $foto = Fotos::create([
            'perfil_id' => $perfil->id,
            'ruta_foto' => $path,
            'es_principal' => false,
            'permisos' => ['publica'],
            'fecha_subida' => now(),
        ]);

        // Si no hay foto principal, establecer esta como principal
        $fotoPrincipal = $perfil->fotos()->where('es_principal', true)->first();
        if (!$fotoPrincipal) {
            $foto->update(['es_principal' => true]);
            $user->update(['foto_principal' => $path]);
        }

        return response()->json([
            'success' => true,
            'foto' => [
                'id' => $foto->id,
                'url' => $foto->url,
                'es_principal' => $foto->es_principal,
                'ruta_foto' => $foto->ruta_foto,
            ],
        ]);
    }

    /**
     * Establece una foto como principal
     */
    public function setFotoPrincipal(Request $request)
    {
        $request->validate([
            'foto_id' => 'required|exists:fotos,id'
        ]);

        $user = Auth::user();
        if (!$user->perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        $foto = Fotos::where('id', $request->foto_id)
            ->where('perfil_id', $user->perfil->id)
            ->first();

        if (!$foto) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        }

        $user->perfil->fotos()->update(['es_principal' => false]);
        $foto->update(['es_principal' => true]);
        $user->update(['foto_principal' => $foto->ruta_foto]);

        return response()->json([
            'success' => true,
            'message' => 'Foto principal actualizada correctamente',
        ]);
    }

    /**
     * Elimina una foto del perfil
     */
    public function eliminarFotoPerfil(Request $request)
    {
        $request->validate([
            'foto_id' => 'required|exists:fotos,id'
        ]);

        $user = Auth::user();
        if (!$user->perfil) {
            return response()->json(['error' => 'Perfil no encontrado'], 404);
        }

        $foto = Fotos::where('id', $request->foto_id)
            ->where('perfil_id', $user->perfil->id)
            ->first();

        if (!$foto) {
            return response()->json(['error' => 'Foto no encontrada'], 404);
        }

        $eraPrincipal = $foto->es_principal;

        if ($foto->ruta_foto) {
            Storage::disk('public')->delete($foto->ruta_foto);
        }

        $foto->delete();

        if ($eraPrincipal) {
            $nuevaPrincipal = $user->perfil->fotos()->first();
            if ($nuevaPrincipal) {
                $nuevaPrincipal->update(['es_principal' => true]);
                $user->update(['foto_principal' => $nuevaPrincipal->ruta_foto]);
            } else {
                $user->update(['foto_principal' => null]);
            }
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // MÉTODOS PRIVADOS
    // ============================================================

    private function getAvatarUrl($user)
    {
        if ($user->foto_principal) {
            if (filter_var($user->foto_principal, FILTER_VALIDATE_URL)) {
                return $user->foto_principal;
            }
            return Storage::url($user->foto_principal);
        }

        if ($user->perfil) {
            $fotoPrincipal = $user->perfil->fotos()->where('es_principal', true)->first();
            if ($fotoPrincipal) {
                return $fotoPrincipal->url;
            }
            
            $primeraFoto = $user->perfil->fotos()->first();
            if ($primeraFoto) {
                return $primeraFoto->url;
            }
        }

        return '/images/shared/avatar-default.jpg';
    }

    private function getCategoriasDisponibles()
    {
        return [
            'Lifestyle', 'Viajes', 'Bienestar', 'Noches exclusivas',
            'Arte', 'Música', 'Gastronomía', 'Fitness', 'Moda',
            'Tecnología', 'Cine', 'Literatura', 'Deportes', 'Cocina'
        ];
    }

    private function getFooterColumnas()
    {
        return [
            'navegacion' => ['Inicio', 'Descubrir', 'Eventos'],
            'comunidad' => ['Mensajes', 'Creadores'],
            'soporte' => ['Sobre nosotros', 'Términos y condiciones', 'Política de privacidad'],
            'legal' => ['Centro de ayuda', 'Contacto', 'Reportar un problema'],
        ];
    }
}