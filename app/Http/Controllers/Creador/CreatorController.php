<?php

namespace App\Http\Controllers\Creador;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Creador;
use App\Models\Perfil;
use App\Models\Fotos;
use App\Models\Publicacion;
use App\Models\Contenido;
use App\Models\ConfiguracionMonetizacion;
use App\Models\Suscripcion;
use App\Models\Transaccion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CreatorController extends Controller
{
    /**
     * Muestra el wizard para convertirse en creador
     */
    public function index()
    {
        Log::info('=== INICIO INDEX CREATOR ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre, 'rol' => $user->rol]);
        
        // Verificar si el usuario ya es creador
        if ($user->rol === 'creador' && $user->creador) {
            Log::info('Usuario ya es creador, redirigiendo a dashboard');
            return redirect()->route('creador.dashboard')
                ->with('info', 'Ya eres un creador verificado.');
        }

        // Cargar el perfil y las fotos del usuario
        $user->load(['perfil.fotos', 'creador.configuracionMonetizacion']);
        Log::info('Datos cargados:', [
            'tiene_perfil' => !is_null($user->perfil),
            'tiene_creador' => !is_null($user->creador)
        ]);

        // Datos del usuario para la vista
        $usuarioData = [
            'nombre' => $user->nombre,
            'avatar' => $this->getAvatarUrl($user),
            'verificado' => $user->estado === 'verificado',
        ];

        // Obtener datos existentes del creador si los tiene
        $creadorData = $user->creador;
        $formData = $this->getFormDataFromCreador($creadorData);
        $verificacionData = $this->getVerificacionDataFromCreador($creadorData);
        $monetizacionData = $this->getMonetizacionDataFromCreador($creadorData);
        $privacidadData = $this->getPrivacidadDataFromCreador($creadorData);

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
            Log::info('Fotos de perfil obtenidas:', ['count' => count($fotosPerfil)]);
        }

        $pasoActivo = $this->getPasoActivo($creadorData);
        Log::info('Paso activo:', ['paso' => $pasoActivo]);

        return Inertia::render('Creador/CrearCreador', [
            'usuario' => $usuarioData,
            'form' => $formData,
            'verificacion' => $verificacionData,
            'monetizacionSeleccionada' => $monetizacionData['seleccionada'] ?? 'suscripcion',
            'privacidad' => $privacidadData,
            'pasoActivo' => $pasoActivo,
            'footerColumnas' => $this->getFooterColumnas(),
            'fotosPerfil' => $fotosPerfil,
            'configuracionMonetizacion' => $creadorData?->configuracionMonetizacion ?? null,
        ]);
    }


    /**
     * Muestra las ganancias del creador
     */
    public function ganancias()
    {
        Log::info('=== GANANCIAS CREADOR ===');
        $user = Auth::user();
        
        // Verificar que el usuario sea creador
        if ($user->rol !== 'creador' || !$user->creador) {
            Log::warning('Usuario no es creador');
            return redirect()->route('creador.index')
                ->with('info', 'Completa el proceso para convertirte en creador.');
        }

        // 🔥 CARGAR LA RELACIÓN PERFIL PARA TENER EL AVATAR
        $user->load(['perfil.fotos', 'creador.configuracionMonetizacion']);
        
        $creador = $user->creador;
        Log::info('Creador encontrado:', ['id' => $creador->id]);

        // ============================================================
        // 1. OBTENER ESTADÍSTICAS DE SUSCRIPCIONES
        // ============================================================
        $suscripcionesActivas = Suscripcion::where('creador_id', $creador->id)
            ->where('estado', 'activa')
            ->count();

        $suscripcionesUltimoMes = Suscripcion::where('creador_id', $creador->id)
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        // ============================================================
        // 2. OBTENER ESTADÍSTICAS DE TRANSACCIONES
        // ============================================================
        $ingresosMes = Transaccion::where('creador_id', $creador->id)
            ->where('estado', 'aprobada')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('monto');

        $gananciasTotales = Transaccion::where('creador_id', $creador->id)
            ->where('estado', 'aprobada')
            ->sum('monto');

        $comisionesTotales = Transaccion::where('creador_id', $creador->id)
            ->where('estado', 'aprobada')
            ->sum('comision');

        $gananciasNetas = $gananciasTotales - $comisionesTotales;

        // ============================================================
        // 3. INGRESOS MENSUALES PARA LA GRÁFICA (6 meses) - EN MXN
        // ============================================================
        $ingresosMensuales = [];
        $mesesLabels = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $mesesLabels[] = $fecha->locale('es')->shortMonthName;
            
            $total = Transaccion::where('creador_id', $creador->id)
                ->where('estado', 'aprobada')
                ->whereYear('created_at', $fecha->year)
                ->whereMonth('created_at', $fecha->month)
                ->sum('monto');
            
            $ingresosMensuales[] = (float) $total;
        }

        // ============================================================
        // 4. TRANSACCIONES RECIENTES - CON AVATAR
        // ============================================================
        $transaccionesRecientes = Transaccion::where('creador_id', $creador->id)
            ->with('usuario.perfil.fotos')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($transaccion) {
                $usuario = $transaccion->usuario;
                $nombreUsuario = $usuario ? $usuario->nombre : 'Anónimo';
                
                // 🔥 OBTENER AVATAR USANDO EL ACCESOR DEL MODELO
                $avatar = null;
                if ($usuario) {
                    $avatar = $usuario->avatar; // Usa el accesor del modelo User
                }
                
                $tipos = [
                    'suscripcion' => 'Suscripción renovada',
                    'compra_contenido' => 'Compra de contenido',
                    'propina' => 'Propina',
                    'retiro' => 'Retiro',
                ];
                
                $tipo = $tipos[$transaccion->tipo] ?? $transaccion->tipo;
                
                $tipoColor = match($transaccion->tipo) {
                    'suscripcion' => 'green',
                    'compra_contenido' => 'blue',
                    'propina' => 'orange',
                    'retiro' => 'red',
                    default => 'gray',
                };
                
                return [
                    'usuario' => $nombreUsuario,
                    'avatar' => $avatar,
                    'tipo' => $tipo,
                    'tipoColor' => $tipoColor,
                    'descripcion' => $this->getDescripcionTransaccion($transaccion),
                    'monto' => '$' . number_format($transaccion->monto, 2) . ' MXN',
                    'fecha' => $transaccion->created_at->format('d/m/Y, H:i'),
                ];
            });

        // ============================================================
        // 5. RENDIMIENTO DE CONTENIDO
        // ============================================================
        $contenido = Contenido::where('creador_id', $creador->id)
            ->where('estado', 'publicado')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                $ingresos = Transaccion::where('creador_id', $item->creador_id)
                    ->where('estado', 'aprobada')
                    ->where('metadatos->contenido_id', $item->id)
                    ->sum('monto');
                
                return [
                    'id' => $item->id,
                    'titulo' => $item->titulo,
                    'tipo' => $item->tipo,
                    'imagen' => $this->getImagenContenido($item),
                    'fecha' => $item->created_at->format('d M Y'),
                    'vistas' => $item->total_vistas ?? 0,
                    'compras' => $item->total_compras ?? 0,
                    'ingresos' => '$' . number_format($ingresos, 2) . ' MXN',
                    'conversion' => $this->calcularConversion($item),
                ];
            });

        // ============================================================
        // 6. DATOS PARA EL SIDEBAR
        // ============================================================
        $saldoDisponible = Transaccion::where('creador_id', $creador->id)
            ->where('estado', 'aprobada')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('monto');

        $proximoPago = Transaccion::where('creador_id', $creador->id)
            ->where('estado', 'pendiente')
            ->where('tipo', 'retiro')
            ->orderBy('created_at', 'asc')
            ->first();

        // 🔥 MÉTODO DE COBRO - PENDIENTE
        $metodoCobro = [
            'nombre' => 'Pendiente de configurar',
            'email' => 'Configura tu método de cobro',
        ];

        // ============================================================
        // 7. METRICAS PARA EL DASHBOARD - EN MXN
        // ============================================================
        $metricas = [
            [
                'icon' => 'pi-dollar',
                'titulo' => 'Ingresos del mes',
                'valor' => '$' . number_format($ingresosMes, 2) . ' MXN',
                'variacion' => $this->calcularVariacion($creador->id, 'mensual'),
                'comparativa' => 'vs. mes anterior',
            ],
            [
                'icon' => 'pi-wallet',
                'titulo' => 'Ganancias totales',
                'valor' => '$' . number_format($gananciasNetas, 2) . ' MXN',
                'variacion' => $this->calcularVariacion($creador->id, 'total'),
                'comparativa' => 'vs. total anterior',
            ],
            [
                'icon' => 'pi-users',
                'titulo' => 'Suscriptores activos',
                'valor' => $this->formatearNumero($suscripcionesActivas),
                'variacion' => $this->calcularVariacionSuscriptores($creador->id),
                'comparativa' => 'vs. mes anterior',
            ],
            [
                'icon' => 'pi-user-plus',
                'titulo' => 'Nuevas suscripciones',
                'valor' => '+' . $suscripcionesUltimoMes,
                'variacion' => '+12%',
                'comparativa' => 'vs. mes anterior',
            ],
            [
                'icon' => 'pi-tag',
                'titulo' => 'Ticket promedio',
                'valor' => $suscripcionesActivas > 0 
                    ? '$' . number_format($ingresosMes / max($suscripcionesActivas, 1), 2) . ' MXN'
                    : '$0.00 MXN',
                'variacion' => '+9%',
                'comparativa' => 'vs. mes anterior',
            ],
            [
                'icon' => 'pi-refresh',
                'titulo' => 'Tasa de renovación',
                'valor' => $this->calcularTasaRenovacion($creador->id) . '%',
                'variacion' => '+6%',
                'comparativa' => 'vs. mes anterior',
            ],
        ];

        // ============================================================
        // 8. CONSEJOS PARA EL CREADOR
        // ============================================================
        $consejos = [
            [
                'icon' => 'pi-file-edit',
                'titulo' => 'Publica con frecuencia',
                'desc' => 'La constancia mantiene a tu audiencia interesada y aumenta tus ingresos.',
            ],
            [
                'icon' => 'pi-shield',
                'titulo' => 'Crea contenido exclusivo',
                'desc' => 'Ofrece contenido único que tus suscriptores no puedan encontrar en otro lugar.',
            ],
            [
                'icon' => 'pi-comments',
                'titulo' => 'Mantén la interacción',
                'desc' => 'Responde mensajes y comentarios para fidelizar y aumentar tus propinas.',
            ],
        ];

        // ============================================================
        // 9. RENDERIZAR VUE - CON AVATAR DEL USUARIO
        // ============================================================
        return Inertia::render('Creador/GananciasCreador', [
            // 🔥 USUARIO CON AVATAR USANDO EL ACCESOR DEL MODELO
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'avatar' => $user->avatar, // 🔥 USA EL ACCESOR DEL MODELO
                'verificado' => $user->estado === 'verificado',
                'rol' => $user->rol,
            ],
            'metricas' => $metricas,
            'meses' => $mesesLabels,
            'ingresosMensuales' => $ingresosMensuales,
            'transacciones' => $transaccionesRecientes,
            'contenido' => $contenido,
            'consejos' => $consejos,
            'saldoDisponible' => '$' . number_format($saldoDisponible, 2) . ' MXN',
            'proximoPago' => $proximoPago ? [
                'fecha' => $proximoPago->created_at->format('d/m/Y'),
                'monto' => '$' . number_format($proximoPago->monto, 2) . ' MXN',
            ] : null,
            'metodoCobro' => $metodoCobro,
            'footerColumnas' => $this->getFooterColumnas(),
        ]);
    }

    /**
     * Obtiene la descripción de una transacción
     */
    private function getDescripcionTransaccion($transaccion)
    {
        $metadatos = $transaccion->metadatos ?? [];
        
        return match($transaccion->tipo) {
            'suscripcion' => 'Renovación ' . ($metadatos['plan'] ?? 'mensual'),
            'compra_contenido' => $metadatos['contenido_titulo'] ?? 'Contenido exclusivo',
            'propina' => $metadatos['mensaje'] ?? 'Propina por mensaje',
            'retiro' => 'Retiro de fondos',
            default => 'Transacción',
        };
    }

    /**
     * Obtiene la imagen de portada de un contenido
     */
    private function getImagenContenido($contenido)
    {
        if ($contenido->archivos && is_array($contenido->archivos) && count($contenido->archivos) > 0) {
            $primerArchivo = $contenido->archivos[0];
            if (isset($primerArchivo['url'])) {
                return $primerArchivo['url'];
            }
            if (isset($primerArchivo['ruta'])) {
                return Storage::url($primerArchivo['ruta']);
            }
        }
        return '/images/ganancias/contenido-default.jpg';
    }

    /**
     * Calcula la tasa de conversión de un contenido
     */
    private function calcularConversion($contenido)
    {
        $vistas = $contenido->total_vistas ?? 0;
        $compras = $contenido->total_compras ?? 0;
        
        if ($vistas === 0) return '0%';
        return number_format(($compras / $vistas) * 100, 1) . '%';
    }

    /**
     * Calcula la variación de ingresos
     */
    private function calcularVariacion($creadorId, $tipo)
    {
        if ($tipo === 'mensual') {
            $mesActual = Transaccion::where('creador_id', $creadorId)
                ->where('estado', 'aprobada')
                ->whereMonth('created_at', now()->month)
                ->sum('monto');
                
            $mesAnterior = Transaccion::where('creador_id', $creadorId)
                ->where('estado', 'aprobada')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->sum('monto');
                
            if ($mesAnterior == 0) return '+0%';
            $variacion = (($mesActual - $mesAnterior) / $mesAnterior) * 100;
            return ($variacion >= 0 ? '+' : '') . number_format($variacion, 1) . '%';
        }
        
        return '+18%';
    }

    /**
     * Calcula la variación de suscriptores
     */
    private function calcularVariacionSuscriptores($creadorId)
    {
        $mesActual = Suscripcion::where('creador_id', $creadorId)
            ->where('estado', 'activa')
            ->whereMonth('created_at', now()->month)
            ->count();
            
        $mesAnterior = Suscripcion::where('creador_id', $creadorId)
            ->where('estado', 'activa')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();
            
        if ($mesAnterior == 0) return '+0%';
        $variacion = (($mesActual - $mesAnterior) / $mesAnterior) * 100;
        return ($variacion >= 0 ? '+' : '') . number_format($variacion, 1) . '%';
    }

    /**
     * Calcula la tasa de renovación
     */
    private function calcularTasaRenovacion($creadorId)
    {
        $totalSuscripciones = Suscripcion::where('creador_id', $creadorId)->count();
        $renovadas = Suscripcion::where('creador_id', $creadorId)
            ->where('estado', 'activa')
            ->where('created_at', '>=', now()->subMonths(6))
            ->count();
            
        if ($totalSuscripciones == 0) return 0;
        return round(($renovadas / $totalSuscripciones) * 100);
    }

    /**
     * Obtiene las columnas del footer
     */
    private function getFooterColumnas()
    {
        return [
            'navegacion' => ['Inicio', 'Descubrir', 'Eventos'],
            'comunidad' => ['Mensajes', 'Creadores'],
            'soporte' => ['Sobre nosotros', 'Términos y condiciones', 'Política de privacidad'],
            'legal' => ['Centro de ayuda', 'Contacto', 'Reportar un problema'],
        ];
    }

    /**
     * Obtiene la URL del avatar del usuario
     */
    private function getAvatarUrl($user)
    {
        Log::info('Obteniendo avatar para usuario:', ['user_id' => $user->id]);
        
        // 1. Si tiene foto_principal en el usuario
        if ($user->foto_principal) {
            if (filter_var($user->foto_principal, FILTER_VALIDATE_URL)) {
                Log::info('Avatar: usando foto_principal URL');
                return $user->foto_principal;
            }
            $url = Storage::url($user->foto_principal);
            Log::info('Avatar: usando foto_principal storage:', ['url' => $url]);
            return $url;
        }

        // 2. Buscar en el perfil las fotos
        if ($user->perfil) {
            $perfil = $user->perfil;
            
            // Buscar foto principal en el perfil
            $fotoPrincipal = $perfil->fotos()->where('es_principal', true)->first();
            if ($fotoPrincipal) {
                Log::info('Avatar: usando foto principal del perfil');
                return $fotoPrincipal->url;
            }
            
            // Si no hay principal, usar la primera foto
            $primeraFoto = $perfil->fotos()->first();
            if ($primeraFoto) {
                Log::info('Avatar: usando primera foto del perfil');
                return $primeraFoto->url;
            }
        }

        Log::info('Avatar: usando default');
        return '/images/shared/avatar-default.jpg';
    }

    /**
     * Guarda el perfil del creador (Paso 1)
     */
    public function guardarPerfil(Request $request)
    {
        Log::info('=== GUARDAR PERFIL CREADOR ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre]);
        
        $validated = $request->validate([
            'nombreMostrar' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:500',
            'categorias' => 'nullable|array',
            'categorias.*' => 'string|max:50',
            'tipoContenido' => 'required|in:fotos,videos,exclusivo',
            'perfilPremium' => 'boolean',
        ]);

        Log::info('Datos validados:', $validated);

        // Obtener o crear el perfil de creador
        $creador = Creador::firstOrNew(['usuario_id' => $user->id]);
        Log::info('Creador:', ['existe' => $creador->exists, 'id' => $creador->id ?? 'nuevo']);
        
        // Actualizar datos
        $creador->biografia = $validated['descripcion'] ?? null;
        $creador->categorias = $validated['categorias'] ?? [];
        $creador->es_premium = $validated['perfilPremium'] ?? false;
        
        // Si es nuevo, establecer estado inicial
        if (!$creador->exists) {
            $creador->estado_verificacion = 'pendiente';
            $creador->documentos_verificacion = [];
            $creador->estadisticas = [];
            $creador->detalles_pago = [];
            $creador->precios = [];
            Log::info('Creando nuevo creador');
        }
        
        // Guardar tipo de contenido en estadisticas
        $estadisticas = $creador->estadisticas ?? [];
        $estadisticas['tipo_contenido'] = $validated['tipoContenido'];
        $creador->estadisticas = $estadisticas;
        
        $creador->save();
        Log::info('Creador guardado:', ['id' => $creador->id]);

        // Actualizar el nombre del usuario
        if ($user->nombre !== $validated['nombreMostrar']) {
            $user->update(['nombre' => $validated['nombreMostrar']]);
            Log::info('Nombre de usuario actualizado');
        }

        return redirect()->back()->with('success', 'Perfil de creador guardado correctamente.');
    }

    /**
     * Guarda la verificación (Paso 2)
     */
    public function guardarVerificacion(Request $request)
    {
        Log::info('=== GUARDAR VERIFICACION ===');
        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            Log::warning('Creador no encontrado');
            return redirect()->back()->with('error', 'Primero completa tu perfil de creador.');
        }

        Log::info('Creador encontrado:', ['id' => $creador->id]);

        // OBTENER LOS DOCUMENTOS ACTUALES DE LA BD
        $documentos = $creador->documentos_verificacion ?? [];
        if (!is_array($documentos)) {
            $documentos = [];
        }

        // Validar que las URLs no sean blob
        $validated = $request->validate([
            'selfieUrl' => 'nullable|string',
            'fotosVerificacionUrls' => 'nullable|array',
            'fotosVerificacionUrls.*' => 'nullable|string',
            'documentoIdentidadUrl' => 'nullable|string',
        ]);

        Log::info('Datos validados:', [
            'selfieUrl' => $validated['selfieUrl'] ?? 'no',
            'count_fotosVerificacionUrls' => count($validated['fotosVerificacionUrls'] ?? []),
            'fotosVerificacionUrls' => $validated['fotosVerificacionUrls'] ?? []
        ]);

        // 1. GUARDAR SELFIE - Solo si es una URL de storage real
        if (isset($validated['selfieUrl']) && !empty($validated['selfieUrl'])) {
            $selfiePath = $this->extractPathFromUrl($validated['selfieUrl']);
            if ($selfiePath && !str_starts_with($selfiePath, 'blob:')) {
                $documentos['selfie'] = $selfiePath;
                Log::info('Selfie guardada:', ['path' => $selfiePath]);
            }
        }

        // 2. GUARDAR FOTOS DEL INE
        if (isset($validated['fotosVerificacionUrls']) && count($validated['fotosVerificacionUrls']) > 0) {
            $fotosINE = [];
            $todasSonBlob = true;
            
            foreach ($validated['fotosVerificacionUrls'] as $url) {
                if (str_starts_with($url, 'blob:')) {
                    continue;
                }
                $todasSonBlob = false;
                $path = $this->extractPathFromUrl($url);
                if ($path && !str_starts_with($path, 'blob:')) {
                    $fotosINE[] = $path;
                    Log::info('INE path extraído:', ['path' => $path]);
                }
            }
            
            if ($todasSonBlob && isset($documentos['fotos_ine']) && count($documentos['fotos_ine']) >= 2) {
                $fotosINE = $documentos['fotos_ine'];
                Log::info('Usando fotos_ine existentes de documentos (todas blob)');
            }
            
            if (count($fotosINE) >= 2) {
                $documentos['fotos_ine'] = $fotosINE;
                Log::info('INE guardadas:', ['count' => count($fotosINE)]);
            } else {
                Log::warning('No se pudieron extraer todas las rutas del INE', ['count' => count($fotosINE)]);
            }
        }

        // 3. GUARDAR DOCUMENTO DE IDENTIDAD (opcional)
        if (isset($validated['documentoIdentidadUrl']) && !empty($validated['documentoIdentidadUrl'])) {
            $path = $this->extractPathFromUrl($validated['documentoIdentidadUrl']);
            if ($path && !str_starts_with($path, 'blob:')) {
                $documentos['documento_identidad'] = $path;
                Log::info('Documento identidad guardado:', ['path' => $path]);
            }
        }

        // 4. VERIFICAR QUE TENGA TODOS LOS DOCUMENTOS OBLIGATORIOS
        $tieneSelfie = isset($documentos['selfie']) && !str_starts_with($documentos['selfie'], 'blob:');
        $tieneINE = isset($documentos['fotos_ine']) && count($documentos['fotos_ine']) >= 2;

        Log::info('Verificación de documentos:', [
            'tiene_selfie' => $tieneSelfie,
            'tiene_ine' => $tieneINE,
            'cantidad_ine' => count($documentos['fotos_ine'] ?? [])
        ]);

        if ($tieneSelfie && $tieneINE) {
            $creador->estado_verificacion = 'pendiente';
            Log::info('Estado de verificación actualizado a: pendiente');
        } else {
            Log::warning('Faltan documentos obligatorios para la verificación');
            return redirect()->back()->with('warning', 'Debes subir tu selfie y ambas fotos de tu INE (frente y reverso).');
        }

        // 5. GUARDAR TODO
        $creador->update([
            'documentos_verificacion' => $documentos,
            'estado_verificacion' => $creador->estado_verificacion ?? 'pendiente',
        ]);

        Log::info('Verificación guardada exitosamente:', [
            'documentos' => array_keys($documentos),
            'estado' => $creador->estado_verificacion
        ]);
        
        return redirect()->back()->with('success', 'Documentos de verificación guardados correctamente. Tu solicitud está en revisión.');
    }

    /**
     * Extrae la ruta del storage desde una URL
     */
    private function extractPathFromUrl($url)
    {
        // Ignorar URLs blob
        if (str_starts_with($url, 'blob:')) {
            return null;
        }

        // Si es una URL completa de storage
        if (str_contains($url, '/storage/')) {
            $parts = explode('/storage/', $url);
            return end($parts);
        }
        
        // Si ya es una ruta relativa
        if (str_starts_with($url, '/')) {
            return ltrim($url, '/');
        }
        
        // Si es una ruta de storage sin el prefijo
        if (str_starts_with($url, 'storage/')) {
            return str_replace('storage/', '', $url);
        }
        
        return $url;
    }

    /**
     * Guarda la configuración de monetización (Paso 3)
     */
    public function guardarMonetizacion(Request $request)
    {
        Log::info('=== GUARDAR MONETIZACION ===');
        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            Log::warning('Creador no encontrado');
            return redirect()->back()->with('error', 'Primero completa tu perfil de creador.');
        }

        $validated = $request->validate([
            'modeloSeleccionado' => 'required|in:gratis,suscripcion,exclusivo',
            'precioPersonalizado' => 'nullable|numeric|min:0',
            'pruebaGratuita' => 'boolean',
            'descuentoLanzamiento' => 'boolean',
            'paqueteVip' => 'boolean',
            'frecuenciaPago' => 'in:Mensual,Trimestral,Semestral,Anual',
            'soloSuscriptores' => 'boolean',
            'aprobarManualmente' => 'boolean',
            'permitirMensajesPremium' => 'boolean',
            'mostrarVistaPrevia' => 'boolean',
            'permitirCompraIndividual' => 'boolean',
        ]);

        Log::info('Datos validados:', $validated);

        // Construir el array con todos los campos
        $data = [
            'creador_id' => $creador->id,
            'modelo_ingresos' => $validated['modeloSeleccionado'] ?? 'suscripcion',
            'precio_personalizado' => $validated['precioPersonalizado'] ?? null,
            'prueba_gratuita' => $validated['pruebaGratuita'] ? 1 : 0,
            'descuento_lanzamiento' => $validated['descuentoLanzamiento'] ? 1 : 0,
            'paquete_vip' => $validated['paqueteVip'] ? 1 : 0,
            'frecuencia_pago' => $validated['frecuenciaPago'] ?? 'Mensual',
            'solo_suscriptores' => $validated['soloSuscriptores'] ? 1 : 0,
            'aprobar_manualmente' => $validated['aprobarManualmente'] ? 1 : 0,
            'permitir_mensajes_premium' => $validated['permitirMensajesPremium'] ? 1 : 0,
            'mostrar_vista_previa' => $validated['mostrarVistaPrevia'] ? 1 : 0,
            'permitir_compra_individual' => $validated['permitirCompraIndividual'] ? 1 : 0,
            'estado' => 'activo',
            'comision_plataforma' => 20.00,
            'tarjeta_ultimos4' => '8123',
            'tarjeta_marca' => 'Visa',
        ];

        Log::info('Datos a guardar:', $data);

        $configuracion = ConfiguracionMonetizacion::updateOrCreate(
            ['creador_id' => $creador->id],
            $data
        );

        Log::info('Configuración guardada:', [
            'config_id' => $configuracion->id,
            'creador_id' => $configuracion->creador_id,
            'modelo_ingresos' => $configuracion->modelo_ingresos
        ]);

        // Actualizar estadísticas del creador
        $estadisticas = $creador->estadisticas ?? [];
        $estadisticas['tipo_monetizacion'] = $validated['modeloSeleccionado'];
        if ($validated['precioPersonalizado']) {
            $estadisticas['precio_seleccionado'] = $validated['precioPersonalizado'];
        }
        $creador->update(['estadisticas' => $estadisticas]);

        return redirect()->back()->with('success', 'Configuración de monetización guardada correctamente.');
    }

    /**
     * Guarda las preferencias de privacidad
     */
    public function guardarPrivacidad(Request $request)
    {
        Log::info('=== GUARDAR PRIVACIDAD ===');
        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            Log::warning('Creador no encontrado');
            return redirect()->back()->with('error', 'Primero completa tu perfil de creador.');
        }

        $validated = $request->validate([
            'aprobarSeguidores' => 'boolean',
            'mostrarContenidoBloqueado' => 'boolean',
            'permitirMensajesPremium' => 'boolean',
            'ocultarActividad' => 'boolean',
        ]);

        Log::info('Datos validados:', $validated);

        // Guardar preferencias en estadisticas
        $estadisticas = $creador->estadisticas ?? [];
        $estadisticas['privacidad'] = [
            'aprobar_seguidores' => $validated['aprobarSeguidores'] ?? true,
            'mostrar_contenido_bloqueado' => $validated['mostrarContenidoBloqueado'] ?? true,
            'permitir_mensajes_premium' => $validated['permitirMensajesPremium'] ?? true,
            'ocultar_actividad' => $validated['ocultarActividad'] ?? false,
        ];

        $creador->update(['estadisticas' => $estadisticas]);
        Log::info('Privacidad guardada');

        return redirect()->back()->with('success', 'Preferencias de privacidad guardadas correctamente.');
    }

    /**
     * Publica el contenido del creador (Paso 4)
     */
    public function publicar(Request $request)
    {
        Log::info('=== INICIO PUBLICAR CONTENIDO ===');
        Log::info('Datos recibidos:', $request->all());
        Log::info('Archivos recibidos:', [
            'has_archivos' => $request->hasFile('archivos'),
            'count' => $request->hasFile('archivos') ? count($request->file('archivos')) : 0,
        ]);

        $user = Auth::user();
        Log::info('Usuario autenticado:', ['id' => $user->id, 'nombre' => $user->nombre, 'rol' => $user->rol]);

        $creador = $user->creador;
        Log::info('Creador encontrado:', ['existe' => !is_null($creador), 'id' => $creador?->id]);

        if (!$creador) {
            Log::warning('Creador no encontrado para usuario:', ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'Primero completa tu perfil de creador.');
        }

        try {
            $validated = $request->validate([
                'tipoContenido' => 'required|in:foto,video,exclusivo',
                'titulo' => 'required|string|max:255',
                'descripcion' => 'nullable|string|max:5000',
                'etiquetas' => 'nullable|string',
                'visibilidad' => 'required|array',
                'visibilidad.soloSuscriptores' => 'boolean',
                'visibilidad.mostrarVistaPreviaBloqueada' => 'boolean',
                'visibilidad.permitirComentarios' => 'boolean',
                'archivos' => 'required|array',
                'archivos.*' => 'file|max:512000',
            ]);

            Log::info('Validación exitosa:', $validated);

            // Decodificar etiquetas si vienen como string JSON
            if (isset($validated['etiquetas']) && is_string($validated['etiquetas'])) {
                $validated['etiquetas'] = json_decode($validated['etiquetas'], true) ?? [];
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación:', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        // VERIFICAR DOCUMENTOS DE VERIFICACIÓN
        $documentos = $creador->documentos_verificacion ?? [];

        // Si no tiene documentos, intentar obtenerlos de la base de datos nuevamente
        if (empty($documentos['selfie']) || empty($documentos['fotos_ine'])) {
            $creador->refresh();
            $documentos = $creador->documentos_verificacion ?? [];
        }

        Log::info('Documentos de verificación encontrados:', [
            'tiene_selfie' => isset($documentos['selfie']),
            'tiene_fotos_ine' => isset($documentos['fotos_ine']),
            'count_fotos_ine' => count($documentos['fotos_ine'] ?? [])
        ]);

        // Validar que tenga selfie y ambas fotos del INE
        $tieneSelfie = isset($documentos['selfie']);
        $tieneINE = isset($documentos['fotos_ine']) && count($documentos['fotos_ine']) >= 2;

        if (!$tieneSelfie || !$tieneINE) {
            Log::warning('Verificación incompleta:', [
                'tiene_selfie' => $tieneSelfie,
                'tiene_ine' => $tieneINE,
                'fotos_ine_count' => count($documentos['fotos_ine'] ?? [])
            ]);
            return redirect()->route('creador.index')
                ->with('error', 'Debes completar la verificación (selfie y ambas fotos del INE) antes de publicar.');
        }

        Log::info('Verificación completa: Selfie y INE OK');

        // Configuración de monetización
        Log::info('Verificando configuración de monetización...');
        $configuracion = $creador->configuracionMonetizacion;
        Log::info('Configuración existente:', ['existe' => !is_null($configuracion)]);

        if (!$configuracion || $configuracion->estado !== 'activo') {
            Log::info('Creando configuración de monetización por defecto...');
            try {
                $configExistente = ConfiguracionMonetizacion::where('creador_id', $creador->id)->first();
                
                if ($configExistente) {
                    $configuracion = $configExistente;
                    $configuracion->update([
                        'estado' => 'activo',
                        'modelo_ingresos' => 'suscripcion',
                    ]);
                } else {
                    $configuracion = ConfiguracionMonetizacion::create([
                        'creador_id' => $creador->id,
                        'modelo_ingresos' => 'suscripcion',
                        'precio_personalizado' => null,
                        'prueba_gratuita' => 1,
                        'descuento_lanzamiento' => 1,
                        'paquete_vip' => 1,
                        'frecuencia_pago' => 'Mensual',
                        'solo_suscriptores' => 1,
                        'aprobar_manualmente' => 1,
                        'permitir_mensajes_premium' => 1,
                        'mostrar_vista_previa' => 1,
                        'permitir_compra_individual' => 0,
                        'estado' => 'activo',
                        'comision_plataforma' => 20.00,
                        'tarjeta_ultimos4' => '8123',
                        'tarjeta_marca' => 'Visa',
                    ]);
                }
                Log::info('Configuración creada/actualizada:', ['config_id' => $configuracion->id]);
            } catch (\Exception $e) {
                Log::error('Error al crear configuración:', ['error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'Error al crear la configuración de monetización.');
            }
            
            // Actualizar estadísticas del creador
            try {
                $estadisticas = $creador->estadisticas ?? [];
                $estadisticas['tipo_monetizacion'] = 'suscripcion';
                $estadisticas['precio_seleccionado'] = 199.99;
                $creador->update(['estadisticas' => $estadisticas]);
                Log::info('Estadísticas actualizadas');
            } catch (\Exception $e) {
                Log::error('Error al actualizar estadísticas:', ['error' => $e->getMessage()]);
            }
        }

        // Procesar archivos subidos
        Log::info('Procesando archivos...');
        $archivosGuardados = [];
        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $index => $file) {
                try {
                    $folder = match ($validated['tipoContenido']) {
                        'video' => 'contenidos/videos',
                        'exclusivo' => 'contenidos/exclusivo',
                        default => 'contenidos/fotos',
                    };
                    
                    $path = $file->store($folder, 'public');
                    $archivoData = [
                        'ruta' => $path,
                        'url' => Storage::url($path),
                        'nombre_original' => $file->getClientOriginalName(),
                        'tipo' => $file->getMimeType(),
                        'tamano' => $file->getSize(),
                    ];
                    $archivosGuardados[] = $archivoData;
                    Log::info('Archivo guardado:', ['index' => $index, 'path' => $path]);
                } catch (\Exception $e) {
                    Log::error('Error al guardar archivo:', ['index' => $index, 'error' => $e->getMessage()]);
                }
            }
            Log::info('Total archivos guardados:', ['count' => count($archivosGuardados)]);
        } else {
            Log::warning('No se recibieron archivos en la petición');
            return redirect()->back()->with('error', 'Debes subir al menos un archivo.');
        }

        // Determinar visibilidad
        $visibilidad = ($validated['visibilidad']['soloSuscriptores'] ?? true) ? 'suscriptores' : 'publico';
        Log::info('Visibilidad determinada:', ['visibilidad' => $visibilidad]);
        
        // Determinar si es premium
        $esPremium = $validated['tipoContenido'] === 'exclusivo' || 
                     ($configuracion->modelo_ingresos !== 'suscripcion' && ($configuracion->precio_personalizado ?? 0) > 0);
        Log::info('Es premium:', ['esPremium' => $esPremium]);

        // Obtener precio
        $precio = $configuracion->precio_personalizado ?? 0;
        if ($configuracion->modelo_ingresos === 'exclusivo' && $configuracion->precio_personalizado) {
            $precio = $configuracion->precio_personalizado;
        }
        Log::info('Precio determinado:', ['precio' => $precio]);

        // Crear el contenido
        try {
            Log::info('Creando contenido en la tabla contenidos...');
            $contenido = Contenido::create([
                'creador_id' => $creador->id,
                'tipo' => $validated['tipoContenido'],
                'titulo' => $validated['titulo'],
                'categoria' => $creador->categorias[0] ?? 'General',
                'descripcion' => $validated['descripcion'] ?? null,
                'archivos' => $archivosGuardados,
                'precio' => $precio,
                'visibilidad' => $visibilidad,
                'estado' => 'publicado',
                'etiquetas' => $validated['etiquetas'] ?? [],
                'es_premium' => $esPremium,
                'metadatos' => [
                    'vista_previa_bloqueada' => $validated['visibilidad']['mostrarVistaPreviaBloqueada'] ?? true,
                    'permitir_comentarios' => $validated['visibilidad']['permitirComentarios'] ?? true,
                    'fecha_publicacion' => now()->toISOString(),
                    'modelo_ingresos' => $configuracion->modelo_ingresos,
                ],
            ]);
            
            Log::info('CONTENIDO CREADO EXITOSAMENTE:', [
                'contenido_id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'tipo' => $contenido->tipo,
                'es_premium' => $contenido->es_premium,
                'visibilidad' => $contenido->visibilidad,
                'precio' => $contenido->precio
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear contenido:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al guardar el contenido: ' . $e->getMessage());
        }

        // Actualizar estadísticas del creador
        try {
            Log::info('Actualizando estadísticas del creador...');
            $estadisticas = $creador->estadisticas ?? [];
            $estadisticas['total_publicaciones'] = ($estadisticas['total_publicaciones'] ?? 0) + 1;
            $estadisticas['ultima_publicacion'] = now()->toISOString();
            
            $publicaciones = $estadisticas['contenido_publicado'] ?? [];
            $publicaciones[] = [
                'id' => $contenido->id,
                'titulo' => $contenido->titulo,
                'tipo' => $contenido->tipo,
                'fecha' => now()->toISOString(),
            ];
            if (count($publicaciones) > 10) {
                $publicaciones = array_slice($publicaciones, -10);
            }
            $estadisticas['contenido_publicado'] = $publicaciones;

            $creador->update(['estadisticas' => $estadisticas]);
            Log::info('Estadísticas actualizadas:', ['total_publicaciones' => $estadisticas['total_publicaciones']]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar estadísticas:', ['error' => $e->getMessage()]);
        }

        // Si el usuario aún no es creador oficial, actualizar rol
        if ($user->rol !== 'creador') {
            try {
                $user->update(['rol' => 'creador']);
                Log::info('Rol de usuario actualizado a creador');
            } catch (\Exception $e) {
                Log::error('Error al actualizar rol:', ['error' => $e->getMessage()]);
            }
        }

        // Actualizar estado de verificación
        try {
            $creador->update(['estado_verificacion' => 'aprobado']);
            Log::info('Estado de verificación actualizado a aprobado');
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de verificación:', ['error' => $e->getMessage()]);
        }

        // Crear publicación de bienvenida
        try {
            $totalPublicaciones = Publicacion::where('usuario_id', $user->id)->count();
            
            if ($totalPublicaciones === 0) {
                $publicacion = Publicacion::create([
                    'usuario_id' => $user->id,
                    'texto' => '¡Hola! Soy ' . $user->nombre . ' y acabo de unirme como creador. Estoy emocionado/a de compartir contenido exclusivo con ustedes. Suscríbanse para no perderse nada.',
                    'estado' => 'publicado',
                    'es_premium' => false,
                ]);
                Log::info('Publicación de bienvenida creada:', ['publicacion_id' => $publicacion->id]);
            }
        } catch (\Exception $e) {
            Log::error('Error al crear publicación de bienvenida:', ['error' => $e->getMessage()]);
        }

        Log::info('PUBLICACIÓN EXITOSA');
        Log::info('Contenido guardado en tabla "contenidos" con ID:', ['id' => $contenido->id]);
        
        // REDIRIGIR A COMUNIDAD CREADOR
        return redirect()->route('creador.comunidad')
            ->with('success', '¡Felicidades! Tu contenido ha sido publicado exitosamente.');
    }

    /**
     * Muestra la comunidad del creador (dashboard del creador)
     */
    public function comunidad()
    {
        Log::info('=== COMUNIDAD CREADOR ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre, 'rol' => $user->rol]);
        
        // Verificar que el usuario sea creador
        if ($user->rol !== 'creador' || !$user->creador) {
            Log::warning('Usuario no es creador o no tiene perfil de creador');
            return redirect()->route('creador.index')
                ->with('info', 'Completa el proceso para convertirte en creador.');
        }

        $creador = $user->creador;
        $configuracion = $creador->configuracionMonetizacion;
        Log::info('Creador encontrado:', ['id' => $creador->id]);

        // Contar contenidos del creador
        $totalContenidos = $creador->contenidos()->where('estado', 'publicado')->count();
        Log::info('Total contenidos del creador:', ['total' => $totalContenidos]);

        // Obtener contenidos recientes del creador
        $contenidosRecientes = $creador->contenidos()
            ->where('estado', 'publicado')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($contenido) {
                return [
                    'id' => $contenido->id,
                    'titulo' => $contenido->titulo,
                    'tipo' => $contenido->tipo,
                    'descripcion' => $contenido->descripcion,
                    'precio' => $contenido->precio,
                    'es_premium' => $contenido->es_premium,
                    'visibilidad' => $contenido->visibilidad,
                    'archivos' => $contenido->archivos,
                    'created_at' => $contenido->created_at->diffForHumans(),
                    'total_likes' => $contenido->total_likes,
                    'total_comentarios' => $contenido->total_comentarios,
                ];
            });

        // Estadísticas del creador
        $estadisticas = [
            'total_publicaciones' => $totalContenidos,
            'total_suscriptores' => Suscripcion::where('creador_id', $creador->id)
                ->where('estado', 'activa')
                ->count(),
            'total_ganancias' => Transaccion::where('creador_id', $creador->id)
                ->where('estado', 'aprobada')
                ->sum('monto') ?? 0,
            'visitas' => $creador->estadisticas['visitas'] ?? 0,
            'interacciones' => $creador->estadisticas['interacciones'] ?? 0,
        ];

        $data = [
            'usuario' => [
                'nombre' => $user->nombre,
                'avatar' => $this->getAvatarUrl($user),
                'verificado' => $user->estado === 'verificado',
            ],
            'creador' => [
                'biografia' => $creador->biografia,
                'categorias' => $creador->categorias,
                'es_premium' => $creador->es_premium,
                'esta_verificado' => $creador->estado_verificacion === 'aprobado',
                'estado_verificacion' => $creador->estado_verificacion,
            ],
            'estadisticas' => $estadisticas,
            'contenidos_recientes' => $contenidosRecientes,
            'configuracion_monetizacion' => $configuracion ? [
                'modelo_ingresos' => $configuracion->modelo_ingresos,
                'precio_personalizado' => $configuracion->precio_personalizado,
                'tiene_tarjeta' => !is_null($configuracion->tarjeta_ultimos4),
                'tarjeta_display' => $configuracion->tarjeta_ultimos4 ? '**** ' . $configuracion->tarjeta_ultimos4 : null,
                'frecuencia_pago' => $configuracion->frecuencia_pago,
                'comision_plataforma' => $configuracion->comision_plataforma,
                'comision_creador' => 100 - ($configuracion->comision_plataforma ?? 20),
            ] : null,
            'footerColumnas' => $this->getFooterColumnas(),
        ];

        Log::info('Datos de la comunidad preparados');
        return Inertia::render('Creador/ComunidadCreador', $data);
    }

    /**
     * Muestra el dashboard del creador (redirige a comunidad)
     */
    public function dashboard()
    {
        Log::info('=== DASHBOARD CREADOR (REDIRIGIENDO) ===');
        return redirect()->route('creador.comunidad');
    }

    /**
     * Muestra el perfil del creador
     */
    public function perfil()
    {
        Log::info('=== PERFIL CREADOR ===');
        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id, 'nombre' => $user->nombre, 'rol' => $user->rol]);
        
        // Verificar que el usuario sea creador
        if ($user->rol !== 'creador' || !$user->creador) {
            Log::warning('Usuario no es creador o no tiene perfil de creador');
            return redirect()->route('creador.index')
                ->with('info', 'Completa el proceso para convertirte en creador.');
        }

        $creador = $user->creador;
        Log::info('Creador encontrado:', ['id' => $creador->id]);
        
        // Cargar relaciones
        $user->load(['perfil.fotos', 'creador.configuracionMonetizacion', 'creador.contenidos']);
        
        // ============================================================
        // 1. DATOS DEL PERFIL
        // ============================================================
        
        // Obtener foto de portada
        $estadisticas = $creador->estadisticas ?? [];
        $fotoPortada = isset($estadisticas['foto_portada']) 
            ? Storage::url($estadisticas['foto_portada']) 
            : '/images/perfil-creador/portada-default.jpg';
        
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
            Log::info('Fotos de perfil obtenidas:', ['count' => count($fotosPerfil)]);
        }
        
        // ============================================================
        // 2. ESTADÍSTICAS DEL CREADOR
        // ============================================================
        
        $totalPublicaciones = $creador->contenidos()->where('estado', 'publicado')->count();
        $totalSuscriptores = Suscripcion::where('creador_id', $creador->id)
            ->where('estado', 'activa')
            ->count();
        $totalGanancias = Transaccion::where('creador_id', $creador->id)
            ->where('estado', 'aprobada')
            ->sum('monto') ?? 0;
        
        // Calcular likes de todas las publicaciones
        $totalLikes = 0;
        foreach ($creador->contenidos()->where('estado', 'publicado')->get() as $contenido) {
            $totalLikes += $contenido->total_likes ?? 0;
        }
        
        // ============================================================
        // 3. CONTENIDOS RECIENTES
        // ============================================================
        
        $contenidosRecientes = $creador->contenidos()
            ->where('estado', 'publicado')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($contenido) {
                // Procesar archivos para asegurar URLs correctas
                $archivos = [];
                if ($contenido->archivos && is_array($contenido->archivos)) {
                    foreach ($contenido->archivos as $archivo) {
                        if (isset($archivo['url'])) {
                            $archivos[] = $archivo;
                        } elseif (isset($archivo['ruta'])) {
                            $archivos[] = [
                                'ruta' => $archivo['ruta'],
                                'url' => Storage::url($archivo['ruta']),
                                'nombre_original' => $archivo['nombre_original'] ?? 'archivo',
                                'tipo' => $archivo['tipo'] ?? 'image/jpeg',
                                'tamano' => $archivo['tamano'] ?? 0,
                            ];
                        }
                    }
                }
                
                return [
                    'id' => $contenido->id,
                    'titulo' => $contenido->titulo,
                    'tipo' => $contenido->tipo,
                    'descripcion' => $contenido->descripcion,
                    'precio' => $contenido->precio,
                    'es_premium' => $contenido->es_premium,
                    'visibilidad' => $contenido->visibilidad,
                    'archivos' => $archivos,
                    'created_at' => $contenido->created_at->diffForHumans(),
                    'total_likes' => $contenido->total_likes ?? 0,
                    'total_comentarios' => $contenido->total_comentarios ?? 0,
                ];
            });
        
        // ============================================================
        // 4. SUSCRIPCIONES ACTIVAS
        // ============================================================
        
        $suscripcionesActivas = Suscripcion::where('creador_id', $creador->id)
            ->with('usuario')
            ->where('estado', 'activa')
            ->limit(5)
            ->get()
            ->map(function ($suscripcion) {
                $usuarioSuscriptor = $suscripcion->usuario;
                return [
                    'nombre' => $usuarioSuscriptor->nombre ?? 'Usuario',
                    'avatar' => $usuarioSuscriptor ? $this->getAvatarUrl($usuarioSuscriptor) : null,
                    'renovacion' => $suscripcion->fecha_renovacion 
                        ? $suscripcion->fecha_renovacion->format('d/m/Y') 
                        : null,
                    'plan' => $suscripcion->plan ?? 'Premium',
                ];
            });
        
        // ============================================================
        // 5. CONFIGURACIÓN DE MONETIZACIÓN
        // ============================================================
        
        $configuracion = $creador->configuracionMonetizacion;
        
        // ============================================================
        // 6. DATOS PARA LA VUE
        // ============================================================
        
        $perfilData = [
            'portada' => $fotoPortada,
            'avatar' => $avatar,
            'nombre' => $user->nombre,
            'bio' => $creador->biografia ?? 'Comparto contenido exclusivo, experiencias auténticas y momentos premium para mi comunidad.',
            'seguidores' => $this->formatearNumero($totalSuscriptores),
            'suscriptores' => $this->formatearNumero($totalSuscriptores),
            'publicaciones' => $totalPublicaciones,
            'meGusta' => $this->formatearNumero($totalLikes),
            'categorias' => $creador->categorias ?? [],
        ];
        
        $estadisticasData = [
            'total_publicaciones' => $totalPublicaciones,
            'total_suscriptores' => $totalSuscriptores,
            'total_ganancias' => $totalGanancias,
            'suscriptores_nuevos' => Suscripcion::where('creador_id', $creador->id)
                ->where('estado', 'activa')
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'visitas' => $estadisticas['visitas'] ?? 0,
            'interacciones' => $estadisticas['interacciones'] ?? 0,
        ];
        
        $configuracionData = $configuracion ? [
            'modelo_ingresos' => $configuracion->modelo_ingresos,
            'precio_personalizado' => $configuracion->precio_personalizado,
            'prueba_gratuita' => (bool)$configuracion->prueba_gratuita,
            'descuento_lanzamiento' => (bool)$configuracion->descuento_lanzamiento,
            'paquete_vip' => (bool)$configuracion->paquete_vip,
            'frecuencia_pago' => $configuracion->frecuencia_pago,
            'solo_suscriptores' => (bool)$configuracion->solo_suscriptores,
            'aprobar_manualmente' => (bool)$configuracion->aprobar_manualmente,
            'permitir_mensajes_premium' => (bool)$configuracion->permitir_mensajes_premium,
            'mostrar_vista_previa' => (bool)$configuracion->mostrar_vista_previa,
            'permitir_compra_individual' => (bool)$configuracion->permitir_compra_individual,
            'comision_plataforma' => $configuracion->comision_plataforma,
        ] : null;
        
        // ============================================================
        // 7. RENDERIZAR VUE
        // ============================================================
        
        return Inertia::render('Creador/PerfilCreador', [
            'usuario' => [
                'nombre' => $user->nombre,
                'avatar' => $avatar,
                'verificado' => $user->estado === 'verificado',
            ],
            'perfil' => $perfilData,
            'publicaciones' => $contenidosRecientes,
            'configuracionMonetizacion' => $configuracionData,
            'fotosPerfil' => $fotosPerfil,
            'footerColumnas' => $this->getFooterColumnas(),
            'estadisticas' => $estadisticasData,
            'suscripcionesActivas' => $suscripcionesActivas,
            'categorias' => $creador->categorias ?? [],
        ]);
    }

    /**
     * Formatea un número a formato abreviado (ej: 12.4K)
     */
    private function formatearNumero($numero)
    {
        if ($numero >= 1000000) {
            return number_format($numero / 1000000, 1) . 'M';
        }
        if ($numero >= 1000) {
            return number_format($numero / 1000, 1) . 'K';
        }
        return (string)$numero;
    }

    /**
     * Sube una selfie (foto de verificación) - API
     */
    public function subirSelfie(Request $request)
    {
        Log::info('=== SUBIR SELFIE ===');
        $request->validate([
            'foto' => 'required|image|max:5120',
        ]);

        $user = Auth::user();
        Log::info('Usuario:', ['id' => $user->id]);

        $path = $request->file('foto')->store('CREADOR_REVISAR/selfies', 'public');
        $url = Storage::url($path);
        Log::info('Selfie guardada:', ['path' => $path, 'url' => $url]);

        // Guardar inmediatamente en la base de datos
        if ($user->creador) {
            $documentos = $user->creador->documentos_verificacion ?? [];
            if (!is_array($documentos)) {
                $documentos = [];
            }
            $documentos['selfie'] = $path;
            
            // Verificar si ya tiene INE para actualizar estado
            $tieneINE = isset($documentos['fotos_ine']) && count($documentos['fotos_ine']) >= 2;
            if ($tieneINE) {
                $user->creador->estado_verificacion = 'pendiente';
            }
            
            $user->creador->update([
                'documentos_verificacion' => $documentos,
                'estado_verificacion' => $user->creador->estado_verificacion ?? 'pendiente',
            ]);
            Log::info('Selfie guardada en BD');
        }

        return redirect()->back()->with([
            'flash' => [
                'selfieUrl' => $url,
                'selfiePath' => $path,
            ],
            'toast' => [
                'type' => 'success',
                'title' => 'Selfie subida',
                'message' => 'Tu selfie se ha subido correctamente',
            ]
        ]);
    }

    /**
     * Sube fotos de verificación (INE) - API
     */
    public function subirFotosVerificacion(Request $request)
    {
        Log::info('=== SUBIR FOTOS VERIFICACION (INE) ===');
        $request->validate([
            'fotos' => 'required|array|min:1',
            'fotos.*' => 'image|max:5120',
        ]);

        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            Log::error('Creador no encontrado');
            return redirect()->back()->with('error', 'Creador no encontrado');
        }

        // Obtener documentos actuales
        $documentos = $creador->documentos_verificacion ?? [];
        if (!is_array($documentos)) {
            $documentos = [];
        }

        // Obtener fotos INE existentes
        $fotosINE = $documentos['fotos_ine'] ?? [];
        $urls = [];

        // Procesar cada foto subida
        foreach ($request->file('fotos') as $index => $file) {
            $path = $file->store('CREADOR_REVISAR/ine', 'public');
            $fotosINE[] = $path;
            $urls[] = Storage::url($path);
            Log::info('INE guardada:', ['index' => $index + 1, 'path' => $path, 'url' => Storage::url($path)]);
        }

        // Guardar las fotos del INE en documentos
        $documentos['fotos_ine'] = $fotosINE;
        
        // Verificar si ya tiene selfie
        $tieneSelfie = isset($documentos['selfie']) && !empty($documentos['selfie']);
        $tieneINECompleto = count($fotosINE) >= 2;
        
        // Actualizar estado de verificación si tiene ambos documentos
        if ($tieneSelfie && $tieneINECompleto) {
            $creador->estado_verificacion = 'pendiente';
            Log::info('Estado de verificación actualizado a: pendiente (tiene selfie y INE)');
        }
        
        $creador->update([
            'documentos_verificacion' => $documentos,
            'estado_verificacion' => $creador->estado_verificacion ?? 'pendiente',
        ]);

        Log::info('INE guardadas en BD:', [
            'count' => count($fotosINE),
            'documentos' => $documentos
        ]);

        // MENSAJE DE CONFIRMACIÓN CON TOAST
        $mensaje = count($fotosINE) >= 2 
            ? '¡Excelente! Ambas fotos de tu INE (frente y reverso) se han subido correctamente. ¡Verificación completada!'
            : 'Foto de INE subida correctamente. Sube la otra foto (frente o reverso) para completar la verificación.';

        $titulo = count($fotosINE) >= 2 
            ? 'Verificación completada'
            : 'Foto de INE subida';

        return redirect()->back()->with([
            'flash' => [
                'fotosVerificacionUrls' => $urls,
                'fotosVerificacionPaths' => $fotosINE,
            ],
            'toast' => [
                'type' => 'success',
                'title' => $titulo,
                'message' => $mensaje,
            ]
        ]);
    }

    /**
     * Sube documento de identidad (PDF) - API
     */
    public function subirDocumento(Request $request)
    {
        Log::info('=== SUBIR DOCUMENTO ===');
        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            Log::error('Creador no encontrado');
            return redirect()->back()->with('error', 'Creador no encontrado');
        }

        $path = $request->file('documento')->store('CREADOR_REVISAR/documentos', 'public');
        $url = Storage::url($path);
        Log::info('Documento guardado:', ['path' => $path]);

        // Guardar inmediatamente en la base de datos
        if ($creador) {
            $documentos = $creador->documentos_verificacion ?? [];
            if (!is_array($documentos)) {
                $documentos = [];
            }
            $documentos['documento_identidad'] = $path;
            $creador->update([
                'documentos_verificacion' => $documentos,
            ]);
            Log::info('Documento guardado en BD');
        }

        return redirect()->back()->with([
            'flash' => [
                'documentoIdentidadUrl' => $url,
                'documentoIdentidadPath' => $path,
            ],
            'toast' => [
                'type' => 'success',
                'title' => 'Documento subido',
                'message' => 'Tu documento de identidad se ha subido correctamente',
            ]
        ]);
    }

    /**
     * Elimina un documento de verificación (API)
     */
    public function eliminarDocumento(Request $request)
    {
        Log::info('=== ELIMINAR DOCUMENTO ===');
        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            Log::error('Creador no encontrado');
            return response()->json(['error' => 'Creador no encontrado'], 404);
        }

        $validated = $request->validate([
            'tipo' => 'required|in:selfie,fotos_ine,documento_identidad',
            'index' => 'nullable|integer',
        ]);

        $documentos = $creador->documentos_verificacion ?? [];
        if (!is_array($documentos)) {
            $documentos = [];
        }

        if ($validated['tipo'] === 'selfie') {
            if (isset($documentos['selfie'])) {
                Storage::disk('public')->delete($documentos['selfie']);
                unset($documentos['selfie']);
                Log::info('Selfie eliminada');
            }
        } elseif ($validated['tipo'] === 'fotos_ine') {
            $index = $validated['index'] ?? 0;
            if (isset($documentos['fotos_ine'][$index])) {
                Storage::disk('public')->delete($documentos['fotos_ine'][$index]);
                array_splice($documentos['fotos_ine'], $index, 1);
                Log::info('INE eliminada:', ['index' => $index]);
            }
        } elseif ($validated['tipo'] === 'documento_identidad') {
            if (isset($documentos['documento_identidad'])) {
                Storage::disk('public')->delete($documentos['documento_identidad']);
                unset($documentos['documento_identidad']);
                Log::info('Documento identidad eliminado');
            }
        }

        $creador->update(['documentos_verificacion' => $documentos]);

        return response()->json(['success' => true]);
    }

    /**
     * Sube foto de portada (API)
     */
    public function subirFotoPortada(Request $request)
    {
        Log::info('=== SUBIR FOTO PORTADA ===');
        $request->validate([
            'foto' => 'required|image|max:5120',
        ]);

        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            Log::error('Creador no encontrado');
            return response()->json(['error' => 'Creador no encontrado'], 404);
        }

        $path = $request->file('foto')->store('CREADOR_REVISAR/portadas', 'public');
        Log::info('Portada guardada:', ['path' => $path]);
        
        $estadisticas = $creador->estadisticas ?? [];
        $estadisticas['foto_portada'] = $path;
        $creador->update(['estadisticas' => $estadisticas]);

        return response()->json([
            'success' => true,
            'url' => Storage::url($path),
        ]);
    }

    /**
     * Obtiene la URL de la foto de portada
     */
    public function getFotoPortada()
    {
        $user = Auth::user();
        $creador = $user->creador;

        if (!$creador) {
            return response()->json(['url' => null]);
        }

        $estadisticas = $creador->estadisticas ?? [];
        $url = isset($estadisticas['foto_portada']) ? Storage::url($estadisticas['foto_portada']) : null;

        return response()->json(['url' => $url]);
    }

    /**
     * Obtiene las fotos del perfil del usuario (API)
     */
    public function getFotosPerfil()
    {
        $user = Auth::user();
        
        if (!$user->perfil) {
            return response()->json(['fotos' => []]);
        }

        $fotos = $user->perfil->fotos()->orderBy('es_principal', 'desc')->get();
        
        $fotosData = $fotos->map(function ($foto) {
            return [
                'id' => $foto->id,
                'url' => $foto->url,
                'es_principal' => $foto->es_principal,
                'ruta_foto' => $foto->ruta_foto,
            ];
        });

        return response()->json(['fotos' => $fotosData]);
    }

    /**
     * Sube fotos al perfil (API)
     */
    public function subirFotosPerfil(Request $request)
    {
        Log::info('=== SUBIR FOTOS PERFIL ===');
        $request->validate([
            'fotos' => 'required|array',
            'fotos.*' => 'image|max:5120',
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

        $fotosGuardadas = [];
        $urls = [];

        foreach ($request->file('fotos') as $index => $file) {
            $path = $file->store('perfiles/fotos', 'public');
            
            $foto = Fotos::create([
                'perfil_id' => $perfil->id,
                'ruta_foto' => $path,
                'es_principal' => false,
                'permisos' => ['publica'],
                'fecha_subida' => now(),
            ]);

            $fotosGuardadas[] = $foto;
            $urls[] = Storage::url($path);
        }

        $fotoPrincipal = $perfil->fotos()->where('es_principal', true)->first();
        if (!$fotoPrincipal && count($fotosGuardadas) > 0) {
            $fotosGuardadas[0]->update(['es_principal' => true]);
            $user->update(['foto_principal' => $fotosGuardadas[0]->ruta_foto]);
        }

        return response()->json([
            'success' => true,
            'fotos' => $urls,
            'fotosData' => $fotosGuardadas->map(function ($foto) {
                return [
                    'id' => $foto->id,
                    'url' => $foto->url,
                    'es_principal' => $foto->es_principal,
                ];
            })
        ]);
    }

    /**
     * Establece una foto como principal (API)
     */
    public function establecerPrincipal(Request $request)
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
            'message' => 'Foto principal actualizada correctamente'
        ]);
    }

    /**
     * Elimina una foto del perfil (API)
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

    /**
     * Obtiene datos del formulario desde el modelo Creador
     */
    private function getFormDataFromCreador($creador)
    {
        if (!$creador) {
            return [
                'nombreMostrar' => Auth::user()->nombre ?? '',
                'descripcion' => '',
                'categorias' => [],
                'tipoContenido' => 'fotos',
                'perfilPremium' => false,
            ];
        }

        $estadisticas = $creador->estadisticas ?? [];

        return [
            'nombreMostrar' => Auth::user()->nombre ?? '',
            'descripcion' => $creador->biografia ?? '',
            'categorias' => $creador->categorias ?? [],
            'tipoContenido' => $estadisticas['tipo_contenido'] ?? 'fotos',
            'perfilPremium' => $creador->es_premium ?? false,
        ];
    }

    /**
     * Obtiene datos de verificación desde el modelo Creador
     */
    private function getVerificacionDataFromCreador($creador)
    {
        if (!$creador) {
            return [
                'selfie' => ['subida' => false, 'url' => null],
                'fotosIdentificacion' => [],
                'documentoIdentidad' => ['estado' => 'pendiente'],
                'verificacionCompleta' => false,
            ];
        }

        $documentos = $creador->documentos_verificacion ?? [];
        if (!is_array($documentos)) {
            $documentos = [];
        }

        // Verificar selfie
        $tieneSelfie = isset($documentos['selfie']);
        $urlSelfie = $tieneSelfie ? Storage::url($documentos['selfie']) : null;

        // Verificar INE
        $fotosINE = $documentos['fotos_ine'] ?? [];
        $urlsINE = array_map(function ($path) {
            return Storage::url($path);
        }, $fotosINE);

        $verificacionCompleta = $tieneSelfie && count($fotosINE) >= 2;

        return [
            'selfie' => [
                'subida' => $tieneSelfie,
                'url' => $urlSelfie,
            ],
            'fotosIdentificacion' => $urlsINE,
            'documentoIdentidad' => [
                'estado' => isset($documentos['documento_identidad']) ? 'subido' : 'pendiente',
            ],
            'verificacionCompleta' => $verificacionCompleta,
            'estado_verificacion' => $creador->estado_verificacion,
        ];
    }

    /**
     * Obtiene datos de monetización desde el modelo Creador
     */
    private function getMonetizacionDataFromCreador($creador)
    {
        if (!$creador) {
            return [
                'seleccionada' => 'suscripcion',
                'precios' => [],
            ];
        }

        $estadisticas = $creador->estadisticas ?? [];
        
        return [
            'seleccionada' => $estadisticas['tipo_monetizacion'] ?? 'suscripcion',
            'precios' => $creador->precios ?? [],
        ];
    }

    /**
     * Obtiene datos de privacidad desde el modelo Creador
     */
    private function getPrivacidadDataFromCreador($creador)
    {
        if (!$creador) {
            return [
                'aprobarSeguidores' => true,
                'mostrarContenidoBloqueado' => true,
                'permitirMensajesPremium' => true,
                'ocultarActividad' => false,
            ];
        }

        $estadisticas = $creador->estadisticas ?? [];
        $privacidad = $estadisticas['privacidad'] ?? [];

        return [
            'aprobarSeguidores' => $privacidad['aprobar_seguidores'] ?? true,
            'mostrarContenidoBloqueado' => $privacidad['mostrar_contenido_bloqueado'] ?? true,
            'permitirMensajesPremium' => $privacidad['permitir_mensajes_premium'] ?? true,
            'ocultarActividad' => $privacidad['ocultar_actividad'] ?? false,
        ];
    }

    /**
     * Determina el paso activo del wizard
     */
    private function getPasoActivo($creador)
    {
        if (!$creador) {
            return 1;
        }

        $documentos = $creador->documentos_verificacion ?? [];
        if (!is_array($documentos)) {
            $documentos = [];
        }

        $estadisticas = $creador->estadisticas ?? [];

        $tieneSelfie = isset($documentos['selfie']);
        $tieneINE = isset($documentos['fotos_ine']) && count($documentos['fotos_ine']) >= 2;

        if ($creador->estado_verificacion === 'aprobado') {
            return 4;
        }

        if (isset($estadisticas['tipo_monetizacion'])) {
            return 4;
        }

        if ($tieneSelfie && $tieneINE) {
            return 3;
        }

        if ($tieneSelfie || $creador->biografia) {
            return 2;
        }

        return 1;
    }
}