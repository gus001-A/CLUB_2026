<?php

namespace App\Http\Controllers\Usuarios;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Fotos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Muestra la página para completar el perfil
     */
    public function completar()
    {
        Log::info('=== INICIO completar perfil ===');
        
        $user = Auth::user();
        Log::info('Usuario autenticado', [
            'user_id' => $user->id,
            'email' => $user->email,
            'estado' => $user->estado
        ]);

        $perfil = Perfil::where('usuario_id', $user->id)->first();
        Log::info('Perfil encontrado', [
            'user_id' => $user->id,
            'tiene_perfil' => $perfil ? true : false,
            'perfil_id' => $perfil ? $perfil->id : null
        ]);

        // ============================================================
        // PREPARAR DATOS DEL USUARIO (DE LOS QUE YA TENEMOS)
        // ============================================================
        
        // Calcular edad desde fecha_nacimiento si existe
        $edad = null;
        if ($user->fecha_nacimiento) {
            $edad = Carbon::parse($user->fecha_nacimiento)->age;
            Log::debug('Edad calculada desde fecha_nacimiento', [
                'user_id' => $user->id,
                'edad' => $edad,
                'fecha_nacimiento' => $user->fecha_nacimiento
            ]);
        }

        $userData = [
            'id' => $user->id,
            'nickname' => $user->apodo ?? $user->nombre ?? '',
            'nombre' => $user->nombre ?? '',
            'apodo' => $user->apodo ?? '',
            'email' => $user->email ?? '',
            'telefono' => $user->telefono ?? '',
            'ciudad' => $user->ciudad ?? '',
            'edad' => $edad,
            'fecha_nacimiento' => $user->fecha_nacimiento ? $user->fecha_nacimiento->format('Y-m-d') : null,
            'estado' => $user->estado ?? 'incompleto',
            'email_verificado_en' => $user->email_verificado_en,
        ];

        Log::info('Datos de usuario preparados', [
            'user_id' => $user->id,
            'nickname' => $userData['nickname'],
            'ciudad' => $userData['ciudad'],
            'edad' => $userData['edad']
        ]);

        // ============================================================
        // PREPARAR DATOS DEL PERFIL (SI EXISTE)
        // ============================================================
        
        $perfilData = null;
        if ($perfil) {
            Log::info('Procesando perfil existente', [
                'perfil_id' => $perfil->id,
                'tipo' => $perfil->tipo
            ]);

            // Obtener fotos de la tabla Fotos
            $fotosList = Fotos::where('perfil_id', $perfil->id)
                ->orderBy('es_principal', 'desc')
                ->get()
                ->map(function($foto) {
                    return [
                        'id' => $foto->id,
                        'url' => $foto->url,
                        'principal' => $foto->es_principal,
                        'ruta_foto' => $foto->ruta_foto,
                    ];
                })
                ->toArray();

            Log::info('Fotos obtenidas de tabla Fotos', [
                'perfil_id' => $perfil->id,
                'cantidad' => count($fotosList),
                'fotos_ids' => array_column($fotosList, 'id')
            ]);

            // Si no hay fotos en la tabla Fotos, usar las del campo 'fotos' del perfil
            if (empty($fotosList) && !empty($perfil->fotos)) {
                $fotosList = $perfil->fotos;
                Log::info('Usando fotos del campo antiguo', [
                    'perfil_id' => $perfil->id,
                    'cantidad' => count($fotosList)
                ]);
            }

            $perfilData = [
                'id' => $perfil->id,
                'tipo' => $perfil->tipo ?? 'personal',
                'descripcion' => $perfil->descripcion ?? '',
                'biografia' => $perfil->biografia ?? '',
                'intereses' => $perfil->intereses ?? [],
                'pasatiempos' => $perfil->pasatiempos ?? [],
                'fotos' => $fotosList,
                'privacidad_fotos' => $perfil->privacidad_fotos ?? 'todos',
                'esta_verificado' => $perfil->esta_verificado ?? false,
                'puntuacion_compatibilidad' => $perfil->puntuacion_compatibilidad ?? 0,
                'ubicacion_ciudad' => $perfil->ubicacion_ciudad ?? $user->ciudad ?? '',
                'metadatos' => $perfil->metadatos ?? [],
            ];

            // Extraer datos de metadatos
            if (isset($perfil->metadatos['edad'])) {
                $userData['edad'] = $perfil->metadatos['edad'];
                Log::debug('Edad obtenida de metadatos', [
                    'user_id' => $user->id,
                    'edad' => $perfil->metadatos['edad']
                ]);
            }
            if (isset($perfil->metadatos['ocupacion'])) {
                $userData['ocupacion'] = $perfil->metadatos['ocupacion'];
            }
            if (isset($perfil->metadatos['pareja'])) {
                $perfilData['pareja'] = $perfil->metadatos['pareja'];
                Log::debug('Datos de pareja obtenidos de metadatos', [
                    'perfil_id' => $perfil->id,
                    'pareja' => $perfil->metadatos['pareja']
                ]);
            }
            if (isset($perfil->metadatos['visibilidad_fotos'])) {
                $perfilData['privacidad_fotos'] = $perfil->metadatos['visibilidad_fotos'];
            }

            Log::info('PerfilData preparado', [
                'perfil_id' => $perfil->id,
                'tipo' => $perfilData['tipo'],
                'intereses_count' => count($perfilData['intereses']),
                'pasatiempos_count' => count($perfilData['pasatiempos']),
                'fotos_count' => count($perfilData['fotos'])
            ]);

        } else {
            // Si no hay perfil, crear uno con datos básicos
            Log::info('Creando nuevo perfil para usuario', ['user_id' => $user->id]);
            
            $perfil = Perfil::create([
                'usuario_id' => $user->id,
                'tipo' => 'personal',
                'descripcion' => '',
                'intereses' => [],
                'pasatiempos' => [],
                'fotos' => [],
                'privacidad_fotos' => 'todos',
                'ubicacion_ciudad' => $user->ciudad ?? '',
                'metadatos' => [
                    'edad' => $edad,
                    'ocupacion' => null,
                    'pareja' => null,
                    'visibilidad_fotos' => 'todos',
                    'perfil_completo' => false,
                    'fotos_completadas' => false,
                ],
            ]);

            Log::info('Perfil creado exitosamente', [
                'perfil_id' => $perfil->id,
                'user_id' => $user->id
            ]);

            $perfilData = [
                'id' => $perfil->id,
                'tipo' => 'personal',
                'descripcion' => '',
                'biografia' => '',
                'intereses' => [],
                'pasatiempos' => [],
                'fotos' => [],
                'privacidad_fotos' => 'todos',
                'esta_verificado' => false,
                'puntuacion_compatibilidad' => 0,
                'ubicacion_ciudad' => $user->ciudad ?? '',
                'metadatos' => [
                    'edad' => $edad,
                    'ocupacion' => null,
                    'pareja' => null,
                    'visibilidad_fotos' => 'todos',
                    'perfil_completo' => false,
                    'fotos_completadas' => false,
                ],
            ];
        }

        // ============================================================
        // LOG PARA DEBUG
        // ============================================================
        Log::info('Cargando datos para completar perfil - RESUMEN FINAL', [
            'user_id' => $user->id,
            'nickname' => $userData['nickname'],
            'ciudad' => $userData['ciudad'],
            'edad' => $userData['edad'],
            'tiene_perfil' => $perfil ? true : false,
            'perfil_id' => $perfil ? $perfil->id : null,
            'total_fotos' => isset($perfilData['fotos']) ? count($perfilData['fotos']) : 0,
            'intereses_count' => isset($perfilData['intereses']) ? count($perfilData['intereses']) : 0,
            'perfil_completo' => isset($perfilData['metadatos']['perfil_completo']) ? $perfilData['metadatos']['perfil_completo'] : false
        ]);

        Log::info('=== FIN completar perfil ===');

        return Inertia::render('Profile/Completar', [
            'user' => $userData,
            'perfil' => $perfilData,
            'fechaNacimiento' => $user->fecha_nacimiento ? $user->fecha_nacimiento->format('Y-m-d') : null,
        ]);
    }

    /**
     * Guarda el perfil del usuario
     */
    public function guardar(Request $request)
    {
        Log::info('=== INICIO guardar perfil ===');
        
        $user = Auth::user();
        Log::info('Usuario autenticado', [
            'user_id' => $user->id,
            'email' => $user->email,
            'estado_actual' => $user->estado
        ]);

        try {
            // Obtener el nickname actual para la validación de unicidad
            $currentNickname = $user->apodo ?? $user->nombre ?? '';
            
            $validated = $request->validate([
                'nickname' => 'required|string|min:3|max:20|unique:users,apodo,' . $user->id . '|unique:users,nombre,' . $user->id,
                'edad' => 'nullable|integer|min:18|max:120',
                'ciudad' => 'nullable|string|max:100',
                'ocupacion' => 'nullable|string|max:100',
                'bio' => 'nullable|string|max:500',
                'tipoPerfil' => 'nullable|in:personal,pareja',
                'pareja.nombre1' => 'nullable|string|max:50',
                'pareja.edad1' => 'nullable|integer|min:18|max:120',
                'pareja.nombre2' => 'nullable|string|max:50',
                'pareja.edad2' => 'nullable|integer|min:18|max:120',
                'pareja.visibleParaAmbos' => 'boolean',
                'intereses' => 'nullable|array',
                'buscando' => 'nullable|array',
                'visibilidadFotos' => 'nullable|in:todos,matches,nadie',
                'fotos' => 'nullable|array',
            ]);

            Log::info('Datos validados correctamente', [
                'user_id' => $user->id,
                'nickname' => $validated['nickname'] ?? 'No enviado',
                'ciudad' => $validated['ciudad'] ?? 'No enviada',
                'edad' => $validated['edad'] ?? 'No enviada',
                'tipoPerfil' => $validated['tipoPerfil'] ?? 'No enviado',
            ]);

            // ============================================================
            // ACTUALIZAR USUARIO (MANTENIENDO DATOS EXISTENTES)
            // ============================================================
            
            Log::info('Actualizando datos de usuario', ['user_id' => $user->id]);
            
            // Actualizar solo los campos que vienen en la petición
            $userData = [];
            
            // Nickname: usar el valor validado o mantener el actual
            if (isset($validated['nickname']) && !empty($validated['nickname'])) {
                $userData['nombre'] = $validated['nickname'];
                $userData['apodo'] = $validated['nickname'];
            }
            
            // Ciudad
            if (isset($validated['ciudad']) && !empty($validated['ciudad'])) {
                $userData['ciudad'] = $validated['ciudad'];
            }
            
            if (!empty($userData)) {
                $user->update($userData);
                Log::debug('Usuario actualizado', [
                    'user_id' => $user->id,
                    'datos_actualizados' => $userData
                ]);
            } else {
                Log::debug('No se actualizaron datos del usuario', ['user_id' => $user->id]);
            }

            // Si el usuario no tiene fecha_nacimiento y se envió edad, la calculamos
            if (!$user->fecha_nacimiento && isset($validated['edad']) && $validated['edad']) {
                $fechaNacimiento = Carbon::now()->subYears($validated['edad'])->startOfDay();
                $user->fecha_nacimiento = $fechaNacimiento;
                $user->save();
                Log::info('Fecha de nacimiento calculada y guardada', [
                    'user_id' => $user->id,
                    'edad' => $validated['edad'],
                    'fecha_nacimiento' => $fechaNacimiento->format('Y-m-d')
                ]);
            }

            // ============================================================
            // PREPARAR METADATOS
            // ============================================================
            
            $metadatos = [];
            
            // Edad (si se envió)
            if (isset($validated['edad'])) {
                $metadatos['edad'] = $validated['edad'];
            }
            
            // Ocupación (si se envió)
            if (isset($validated['ocupacion'])) {
                $metadatos['ocupacion'] = $validated['ocupacion'];
            }
            
            // Visibilidad de fotos (si se envió)
            if (isset($validated['visibilidadFotos'])) {
                $metadatos['visibilidad_fotos'] = $validated['visibilidadFotos'];
            }

            // Si es perfil de pareja, guardar datos de la pareja
            if (isset($validated['tipoPerfil']) && $validated['tipoPerfil'] === 'pareja' && isset($validated['pareja'])) {
                $metadatos['pareja'] = [
                    'nombre1' => $validated['pareja']['nombre1'] ?? '',
                    'edad1' => $validated['pareja']['edad1'] ?? null,
                    'nombre2' => $validated['pareja']['nombre2'] ?? '',
                    'edad2' => $validated['pareja']['edad2'] ?? null,
                    'visibleParaAmbos' => $validated['pareja']['visibleParaAmbos'] ?? false,
                ];
                Log::info('Datos de pareja preparados', [
                    'user_id' => $user->id,
                    'nombre1' => $metadatos['pareja']['nombre1'],
                    'nombre2' => $metadatos['pareja']['nombre2']
                ]);
            }

            // Obtener el perfil existente para preservar metadatos anteriores
            $perfil = Perfil::where('usuario_id', $user->id)->first();
            
            // Si el perfil existe, fusionar metadatos existentes con los nuevos
            if ($perfil && isset($perfil->metadatos)) {
                $metadatos = array_merge($perfil->metadatos ?? [], $metadatos);
            }

            Log::debug('Metadatos finales', [
                'user_id' => $user->id,
                'metadatos' => $metadatos
            ]);

            // ============================================================
            // ACTUALIZAR O CREAR PERFIL
            // ============================================================
            
            Log::info('Actualizando/Creando perfil', ['user_id' => $user->id]);
            
            // Preparar datos del perfil
            $perfilData = [];
            
            // Tipo de perfil (si se envió)
            if (isset($validated['tipoPerfil'])) {
                $perfilData['tipo'] = $validated['tipoPerfil'] === 'pareja' ? 'pareja' : 'personal';
            }
            
            // Descripción (bio)
            if (isset($validated['bio'])) {
                $perfilData['descripcion'] = $validated['bio'];
            }
            
            // Intereses (si se envió y es array)
            if (isset($validated['intereses']) && is_array($validated['intereses'])) {
                $perfilData['intereses'] = $validated['intereses'];
            }
            
            // Pasatiempos (buscando)
            if (isset($validated['buscando']) && is_array($validated['buscando'])) {
                $perfilData['pasatiempos'] = $validated['buscando'];
            }
            
            // Ciudad
            if (isset($validated['ciudad']) && !empty($validated['ciudad'])) {
                $perfilData['ubicacion_ciudad'] = $validated['ciudad'];
            }
            
            // Visibilidad de fotos
            if (isset($validated['visibilidadFotos'])) {
                $perfilData['privacidad_fotos'] = $validated['visibilidadFotos'];
            }
            
            // Siempre actualizar metadatos
            $perfilData['metadatos'] = $metadatos;
            
            $perfil = Perfil::updateOrCreate(
                ['usuario_id' => $user->id],
                $perfilData
            );

            Log::info('Perfil guardado', [
                'perfil_id' => $perfil->id,
                'user_id' => $user->id,
                'tipo' => $perfil->tipo,
                'intereses_count' => count($perfil->intereses ?? []),
                'pasatiempos_count' => count($perfil->pasatiempos ?? [])
            ]);

            // ============================================================
            // PROCESAR FOTOS
            // ============================================================
            
            $fotosGuardadas = 0;
            if (isset($validated['fotos']) && !empty($validated['fotos']) && is_array($validated['fotos'])) {
                Log::info('Procesando fotos', [
                    'perfil_id' => $perfil->id,
                    'fotos_recibidas' => count($validated['fotos'])
                ]);

                // Eliminar fotos anteriores
                $fotosEliminadas = Fotos::where('perfil_id', $perfil->id)->delete();
                Log::debug('Fotos anteriores eliminadas', [
                    'perfil_id' => $perfil->id,
                    'cantidad_eliminadas' => $fotosEliminadas
                ]);
                
                $fotosUrls = [];
                foreach ($validated['fotos'] as $index => $foto) {
                    Log::debug('Procesando foto individual', [
                        'perfil_id' => $perfil->id,
                        'index' => $index,
                        'es_principal' => $foto['principal'] ?? ($index === 0)
                    ]);

                    $nuevaFoto = new Fotos();
                    $nuevaFoto->perfil_id = $perfil->id;
                    $nuevaFoto->es_principal = $foto['principal'] ?? ($index === 0);
                    $nuevaFoto->fecha_subida = now();
                    $nuevaFoto->permisos = [$validated['visibilidadFotos'] ?? 'todos'];
                    
                    if (isset($foto['file']) && $foto['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $path = $foto['file']->store('perfil/fotos', 'public');
                        $nuevaFoto->ruta_foto = $path;
                        Log::debug('Foto subida como archivo', [
                            'perfil_id' => $perfil->id,
                            'path' => $path,
                            'size' => $foto['file']->getSize()
                        ]);
                    } elseif (isset($foto['url']) && filter_var($foto['url'], FILTER_VALIDATE_URL)) {
                        $nuevaFoto->ruta_foto = $foto['url'];
                        Log::debug('Foto guardada como URL', [
                            'perfil_id' => $perfil->id,
                            'url' => $foto['url']
                        ]);
                    } else {
                        Log::warning('Foto sin datos válidos, saltando', [
                            'perfil_id' => $perfil->id,
                            'index' => $index
                        ]);
                        continue;
                    }
                    
                    $nuevaFoto->save();
                    $fotosGuardadas++;
                    
                    $fotosUrls[] = [
                        'url' => $nuevaFoto->url,
                        'principal' => $nuevaFoto->es_principal,
                    ];
                }
                
                // Actualizar el campo 'fotos' del perfil para compatibilidad
                if (!empty($fotosUrls)) {
                    $perfil->update(['fotos' => $fotosUrls]);
                    Log::info('Campo fotos del perfil actualizado', [
                        'perfil_id' => $perfil->id,
                        'fotos_guardadas' => $fotosGuardadas
                    ]);
                }

                // Actualizar metadatos con estado de fotos
                $metadatos['fotos_completadas'] = $fotosGuardadas >= 4;
                $perfil->metadatos = $metadatos;
                $perfil->save();
                
                Log::info('Metadatos actualizados con estado de fotos', [
                    'perfil_id' => $perfil->id,
                    'fotos_completadas' => $metadatos['fotos_completadas']
                ]);
            }

            // ============================================================
            // VERIFICAR SI EL PERFIL ESTÁ COMPLETO
            // ============================================================
            
            $completo = $this->perfilCompleto($user, $perfil);
            Log::info('Verificación de perfil completo', [
                'user_id' => $user->id,
                'perfil_id' => $perfil->id,
                'completo' => $completo
            ]);

            // Actualizar metadatos con estado de perfil completo
            $metadatos['perfil_completo'] = $completo;
            $perfil->metadatos = $metadatos;
            $perfil->save();
            
            if ($completo) {
                $user->update([
                    'estado' => 'verificado',
                    'email_verificado_en' => now(),
                ]);
                Log::info('USUARIO VERIFICADO AL COMPLETAR PERFIL', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'fecha_verificacion' => now()->format('Y-m-d H:i:s')
                ]);
            } else {
                Log::info('Perfil incompleto, usuario en estado "incompleto"', [
                    'user_id' => $user->id,
                    'estado' => $user->estado
                ]);
            }

            Log::info('=== FIN guardar perfil - EXITOSO ===', [
                'user_id' => $user->id,
                'perfil_completo' => $completo,
                'fotos_guardadas' => $fotosGuardadas
            ]);

            return redirect()->back()->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => 'Perfil actualizado',
                    'message' => $completo ? 'Felicidades! Tu perfil está completo y verificado.' : 'Tu perfil ha sido actualizado correctamente.',
                    'duration' => 3000,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ERROR DE VALIDACION al guardar perfil', [
                'user_id' => $user->id,
                'errors' => $e->errors(),
                'all_data' => $request->all()
            ]);

            return redirect()->back()->withErrors($e->errors())->with('flash', [
                'toast' => [
                    'type' => 'error',
                    'title' => 'Error de validación',
                    'message' => 'Por favor, revisa los campos marcados.',
                    'duration' => 5000,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ERROR al guardar perfil', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('flash', [
                'toast' => [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'No se pudo guardar el perfil. Por favor, intenta nuevamente.',
                    'duration' => 5000,
                ]
            ]);
        }
    }

    /**
     * Verifica si el perfil está completo
     */
    protected function perfilCompleto($user, $perfil)
    {
        Log::debug('=== INICIO perfilCompleto ===', [
            'user_id' => $user->id,
            'perfil_id' => $perfil->id
        ]);

        // Verificar que tenga foto principal
        $fotoPrincipal = Fotos::where('perfil_id', $perfil->id)
            ->where('es_principal', true)
            ->first();

        // Verificar que tenga al menos 4 fotos
        $totalFotos = Fotos::where('perfil_id', $perfil->id)->count();

        // Verificar que tenga intereses
        $intereses = $perfil->intereses ?? [];
        $pasatiempos = $perfil->pasatiempos ?? [];

        // Verificar que tenga descripción
        $descripcion = $perfil->descripcion ?? '';

        // Verificar requisitos
        $tieneFotoPrincipal = (bool) $fotoPrincipal;
        $tieneFotosSuficientes = $totalFotos >= 4;
        $tieneInteresesSuficientes = count($intereses) >= 3;
        $tienePasatiemposSuficientes = count($pasatiempos) >= 2;
        $tieneDescripcionSuficiente = strlen($descripcion) >= 50;
        $tieneCiudad = (bool) $user->ciudad;

        $completo = (
            $tieneFotoPrincipal &&
            $tieneFotosSuficientes &&
            $tieneInteresesSuficientes &&
            $tienePasatiemposSuficientes &&
            $tieneDescripcionSuficiente &&
            $tieneCiudad
        );

        Log::debug('Verificación de requisitos de perfil completo', [
            'user_id' => $user->id,
            'requisitos' => [
                'foto_principal' => $tieneFotoPrincipal,
                'total_fotos' => $totalFotos,
                'fotos_suficientes' => $tieneFotosSuficientes,
                'intereses_count' => count($intereses),
                'intereses_suficientes' => $tieneInteresesSuficientes,
                'pasatiempos_count' => count($pasatiempos),
                'pasatiempos_suficientes' => $tienePasatiemposSuficientes,
                'descripcion_length' => strlen($descripcion),
                'descripcion_suficiente' => $tieneDescripcionSuficiente,
                'tiene_ciudad' => $tieneCiudad,
            ],
            'completo' => $completo
        ]);

        Log::debug('=== FIN perfilCompleto ===', [
            'user_id' => $user->id,
            'resultado' => $completo ? 'COMPLETO' : 'INCOMPLETO'
        ]);

        return $completo;
    }

    /**
     * Guarda el borrador del perfil
     */
    public function borrador(Request $request)
    {
        Log::info('=== INICIO guardar borrador ===', ['user_id' => Auth::id()]);
        
        $resultado = $this->guardar($request);
        
        Log::info('=== FIN guardar borrador ===', ['user_id' => Auth::id()]);
        
        return $resultado;
    }
}