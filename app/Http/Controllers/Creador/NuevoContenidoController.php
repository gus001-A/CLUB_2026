<?php

namespace App\Http\Controllers\Creador;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Creador;
use App\Models\Contenido;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NuevoContenidoController extends Controller
{
    /**
     * Muestra el formulario para crear nuevo contenido
     */
    public function index()
    {
        Log::info('=== NUEVO CONTENIDO - FORMULARIO ===');
        $user = Auth::user();
        
        // Verificar que el usuario sea creador
        if ($user->rol !== 'creador' || !$user->creador) {
            Log::warning('Usuario no es creador');
            return redirect()->route('creador.index')
                ->with('info', 'Completa el proceso para convertirte en creador.');
        }

        $creador = $user->creador;
        $configuracion = $creador->configuracionMonetizacion;

        return Inertia::render('Creador/NuevoContenido', [
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'avatar' => $this->getAvatarUrl($user),
                'verificado' => $user->estado === 'verificado',
                'rol' => $user->rol,
            ],
            'configuracion' => $configuracion ? [
                'modelo_ingresos' => $configuracion->modelo_ingresos,
                'precio_personalizado' => $configuracion->precio_personalizado,
                'frecuencia_pago' => $configuracion->frecuencia_pago,
                'moneda' => $configuracion->moneda ?? 'MXN',
            ] : null,
            'categoriasDisponibles' => $this->getCategoriasDisponibles(),
            'footerColumnas' => $this->getFooterColumnas(),
        ]);
    }

    /**
     * Guarda el nuevo contenido
     */
    public function store(Request $request)
    {
        Log::info('=== GUARDAR NUEVO CONTENIDO ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre]);

        $creador = $user->creador;
        if (!$creador) {
            Log::warning('Creador no encontrado');
            return redirect()->back()->with('error', 'No se encontró tu perfil de creador.');
        }

        // Validar datos
        $validated = $request->validate([
            'tipo' => 'required|in:foto,video',
            'titulo' => 'required|string|max:255',
            'categoria' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:5000',
            'visibilidad' => 'required|in:publico,suscriptores',
            'es_premium' => 'boolean',
            'etiquetas' => 'nullable|string',
            'archivos' => 'nullable|array',
            'archivos.*' => 'file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv', // ✅ Añadir mimes
        ]);

        Log::info('Datos validados:', $validated);

        // Procesar etiquetas
        $etiquetas = [];
        if (!empty($validated['etiquetas'])) {
            try {
                $etiquetas = json_decode($validated['etiquetas'], true);
                if (!is_array($etiquetas)) {
                    $etiquetas = [];
                }
            } catch (\Exception $e) {
                Log::warning('Error al decodificar etiquetas:', ['error' => $e->getMessage()]);
                $etiquetas = [];
            }
        }
        Log::info('Etiquetas procesadas:', ['etiquetas' => $etiquetas]);

        // Procesar archivos
        $archivosGuardados = [];
        
        // ✅ Obtener archivos de diferentes formas posibles
        $archivos = $request->file('archivos');
        
        // Si es null, intentar obtener de otra manera
        if (empty($archivos) && $request->has('archivos')) {
            Log::info('Archivos enviados como array, procesando...');
            $archivos = $request->input('archivos');
        }
        
        // Si es un solo archivo, convertirlo a array
        if ($archivos instanceof \Illuminate\Http\UploadedFile) {
            $archivos = [$archivos];
        }
        
        // Si es un array, asegurarse de que sea indexado numéricamente
        if (is_array($archivos)) {
            $archivos = array_values($archivos);
        }

        if (!empty($archivos) && is_array($archivos)) {
            foreach ($archivos as $index => $file) {
                if (!$file instanceof \Illuminate\Http\UploadedFile) {
                    Log::warning('Elemento no es un archivo válido:', ['index' => $index]);
                    continue;
                }

                if (!$file->isValid()) {
                    Log::warning('Archivo inválido:', ['index' => $index, 'error' => $file->getError()]);
                    continue;
                }

                try {
                    $folder = match ($validated['tipo']) {
                        'video' => 'contenidos/videos',
                        'foto' => 'contenidos/fotos',
                        default => 'contenidos/otros',
                    };
                    
                    $path = $file->store($folder, 'public');
                    $archivosGuardados[] = [
                        'ruta' => $path,
                        'url' => Storage::url($path),
                        'nombre_original' => $file->getClientOriginalName(),
                        'tipo' => $file->getMimeType(),
                        'tamano' => $file->getSize(),
                    ];
                    Log::info('Archivo guardado:', ['index' => $index, 'path' => $path]);
                } catch (\Exception $e) {
                    Log::error('Error al guardar archivo:', ['index' => $index, 'error' => $e->getMessage()]);
                }
            }
        }

        // ✅ Verificar que se guardaron archivos
        if (empty($archivosGuardados)) {
            Log::warning('No se guardó ningún archivo');
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se pudo guardar ningún archivo. Verifica el formato y tamaño máximo (50MB).');
        }

        // ✅ El precio se obtiene de la configuración del creador, no del formulario
        $configuracion = $creador->configuracionMonetizacion;
        $precio = $validated['es_premium'] ? ($configuracion?->precio_personalizado ?? 199.99) : 0;

        // Crear contenido
        try {
            $contenido = Contenido::create([
                'creador_id' => $creador->id,
                'tipo' => $validated['tipo'],
                'titulo' => $validated['titulo'],
                'categoria' => $validated['categoria'] ?? 'General',
                'descripcion' => $validated['descripcion'] ?? null,
                'archivos' => $archivosGuardados,
                'precio' => $precio,
                'visibilidad' => $validated['visibilidad'],
                'estado' => 'publicado',
                'etiquetas' => $etiquetas,
                'es_premium' => $validated['es_premium'] ?? false,
                'metadatos' => [
                    'fecha_publicacion' => now()->toISOString(),
                    'tiene_archivos' => count($archivosGuardados) > 0,
                    'total_archivos' => count($archivosGuardados),
                ],
            ]);

            Log::info('CONTENIDO CREADO EXITOSAMENTE:', [
                'contenido_id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'tipo' => $contenido->tipo,
                'es_premium' => $contenido->es_premium,
                'precio' => $contenido->precio,
                'etiquetas' => $contenido->etiquetas,
                'archivos_subidos' => count($archivosGuardados),
            ]);

            // Actualizar estadísticas del creador
            $estadisticas = $creador->estadisticas ?? [];
            $estadisticas['total_publicaciones'] = ($estadisticas['total_publicaciones'] ?? 0) + 1;
            $estadisticas['ultima_publicacion'] = now()->toISOString();
            
            $publicaciones = $estadisticas['contenido_publicado'] ?? [];
            $publicaciones[] = [
                'id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'tipo' => $contenido->tipo,
                'fecha' => now()->toISOString(),
                'es_premium' => $contenido->es_premium,
            ];
            if (count($publicaciones) > 10) {
                $publicaciones = array_slice($publicaciones, -10);
            }
            $estadisticas['contenido_publicado'] = $publicaciones;
            $creador->update(['estadisticas' => $estadisticas]);

            return redirect()->route('creador.comunidad')
                ->with('success', '¡Contenido publicado exitosamente!');

        } catch (\Exception $e) {
            Log::error('Error al crear contenido:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Limpiar archivos subidos si hubo error
            foreach ($archivosGuardados as $archivo) {
                if (Storage::disk('public')->exists($archivo['ruta'])) {
                    Storage::disk('public')->delete($archivo['ruta']);
                }
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar el contenido: ' . $e->getMessage());
        }
    }

    /**
     * Sube archivos temporalmente (para el dropzone)
     */
    public function subirArchivo(Request $request)
    {
        Log::info('=== SUBIR ARCHIVO TEMPORAL ===');
        $request->validate([
            'archivo' => 'required|file|max:51200|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv',
        ]);

        $user = Auth::user();
        if ($user->rol !== 'creador' || !$user->creador) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $file = $request->file('archivo');
        $path = $file->store('contenidos/temp', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'ruta' => $path,
            'url' => $url,
            'nombre_original' => $file->getClientOriginalName(),
            'tipo' => $file->getMimeType(),
            'tamano' => $file->getSize(),
        ]);
    }

    /**
     * Elimina un archivo temporal
     */
    public function eliminarArchivo(Request $request)
    {
        Log::info('=== ELIMINAR ARCHIVO TEMPORAL ===');
        $request->validate([
            'ruta' => 'required|string',
        ]);

        $user = Auth::user();
        if ($user->rol !== 'creador' || !$user->creador) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if (Storage::disk('public')->exists($request->ruta)) {
            Storage::disk('public')->delete($request->ruta);
            Log::info('Archivo eliminado:', ['ruta' => $request->ruta]);
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
            'General', 'Lifestyle', 'Viajes', 'Bienestar', 'Noches exclusivas',
            'Arte', 'Música', 'Gastronomía', 'Fitness', 'Moda',
            'Tecnología', 'Cine', 'Literatura', 'Deportes', 'Cocina',
            'Educación', 'Entretenimiento', 'Inspiración', 'Consejos'
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