<?php

namespace App\Http\Controllers\Usuario;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\Fotos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Muestra la página para completar el perfil
     */
    public function completar()
    {
        Log::info('ProfileController@completar - Iniciando');
        
        $user = Auth::user();
        
        if (!$user) {
            Log::warning('ProfileController@completar - Usuario no autenticado');
            return redirect()->route('login');
        }

        Log::info('ProfileController@completar - Usuario autenticado', [
            'user_id' => $user->id,
            'email' => $user->email,
            'estado' => $user->estado
        ]);

        $perfil = Perfil::where('usuario_id', $user->id)->first();
        
        Log::info('ProfileController@completar - Perfil encontrado', [
            'existe_perfil' => $perfil ? true : false,
            'perfil_id' => $perfil ? $perfil->id : null,
            'tipo' => $perfil ? $perfil->tipo : null
        ]);
        
        // ============================================================
        // PREPARAR DATOS DEL USUARIO
        // ============================================================
        
        $edad = null;
        if ($user->fecha_nacimiento) {
            $edad = Carbon::parse($user->fecha_nacimiento)->age;
        }

        // Obtener avatar desde foto_principal
        $avatar = '/images/shared/avatar-default.jpg';
        $fotoPrincipalUrl = null;
        
        if ($user->foto_principal) {
            $fotoPrincipalUrl = $user->foto_principal;
            $avatar = $this->getUrlFromPath($user->foto_principal);
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
            'avatar' => $avatar,
            'foto_principal' => $fotoPrincipalUrl,
        ];

        Log::debug('ProfileController@completar - Datos de usuario preparados', [
            'user_data' => $userData
        ]);

        // ============================================================
        // PREPARAR DATOS DEL PERFIL
        // ============================================================
        
        $perfilData = null;
        if ($perfil) {
            // OBTENER FOTOS DESDE LA TABLA FOTOS
            $fotosList = [];
            $fotosQuery = Fotos::where('perfil_id', $perfil->id)
                ->orderBy('es_principal', 'desc')
                ->get();
            
            Log::info('ProfileController@completar - Fotos encontradas', [
                'perfil_id' => $perfil->id,
                'total_fotos' => $fotosQuery->count()
            ]);
            
            if ($fotosQuery->count() > 0) {
                foreach ($fotosQuery as $foto) {
                    $fotoUrl = $this->getUrlFromPath($foto->ruta_foto);
                    $fotosList[] = [
                        'id' => $foto->id,
                        'url' => $fotoUrl,
                        'principal' => (bool) $foto->es_principal,
                        'ruta_foto' => $foto->ruta_foto,
                        'es_principal' => (bool) $foto->es_principal,
                    ];
                }
            }

            // CORRECCIÓN: Mapear valores de privacidad
            $privacidadFotos = $perfil->privacidad_fotos ?? 'publico';
            // Mapear a los valores que espera el frontend
            $privacidadMap = [
                'publico' => 'todos',
                'coincidencias' => 'matches',
                'oculto' => 'nadie'
            ];
            $privacidadFrontend = $privacidadMap[$privacidadFotos] ?? 'todos';

            // CORRECCIÓN: Asegurar que metadatos sea un array
            $metadatos = $perfil->metadatos;
            if (is_string($metadatos)) {
                $metadatos = json_decode($metadatos, true) ?? [];
            } elseif (!is_array($metadatos)) {
                $metadatos = [];
            }

            $perfilData = [
                'id' => $perfil->id,
                'tipo' => $perfil->tipo ?? 'personal',
                'descripcion' => $perfil->descripcion ?? '',
                'biografia' => $perfil->descripcion ?? '',
                'intereses' => $perfil->intereses ?? [],
                'pasatiempos' => $perfil->pasatiempos ?? [],
                'fotos' => $fotosList,
                'privacidad_fotos' => $privacidadFrontend,
                'esta_verificado' => $perfil->esta_verificado ?? false,
                'puntuacion_compatibilidad' => $perfil->puntuacion_compatibilidad ?? 0,
                'ubicacion_ciudad' => $perfil->ubicacion_ciudad ?? $user->ciudad ?? '',
                'metadatos' => $metadatos,
            ];

            if (isset($metadatos['edad'])) {
                $userData['edad'] = $metadatos['edad'];
            }
            if (isset($metadatos['ocupacion'])) {
                $userData['ocupacion'] = $metadatos['ocupacion'];
            }
            if (isset($metadatos['pareja'])) {
                $perfilData['pareja'] = $metadatos['pareja'];
            }
            if (isset($metadatos['visibilidad_fotos'])) {
                $perfilData['privacidad_fotos'] = $metadatos['visibilidad_fotos'];
            }

        } else {
            Log::info('ProfileController@completar - Creando nuevo perfil para usuario', [
                'user_id' => $user->id
            ]);
            
            // CORRECCIÓN: Usar valores válidos para la BD
            $perfil = Perfil::create([
                'usuario_id' => $user->id,
                'tipo' => 'personal',
                'descripcion' => '',
                'intereses' => [], // El cast lo convierte a JSON
                'pasatiempos' => [], // El cast lo convierte a JSON
                'privacidad_fotos' => 'publico', // Valor válido para la BD
                'ubicacion_ciudad' => $user->ciudad ?? '',
                'metadatos' => [ // El cast lo convierte a JSON
                    'edad' => $edad,
                    'ocupacion' => null,
                    'pareja' => null,
                    'visibilidad_fotos' => 'todos',
                    'perfil_completo' => false,
                    'fotos_completadas' => false,
                ],
            ]);

            Log::info('ProfileController@completar - Nuevo perfil creado', [
                'perfil_id' => $perfil->id
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

        $perfilCompleto = isset($perfilData['metadatos']['perfil_completo']) && $perfilData['metadatos']['perfil_completo'] === true;
        $usuarioVerificado = $user->estado === 'verificado' || $user->email_verificado_en !== null;
        
        $perfilData['perfil_completo'] = $perfilCompleto;
        $perfilData['usuario_verificado'] = $usuarioVerificado;

        Log::info('ProfileController@completar - Finalizando', [
            'perfil_completo' => $perfilCompleto,
            'usuario_verificado' => $usuarioVerificado
        ]);

        return Inertia::render('Usuario/CompletarPerfil', [
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
        Log::info('ProfileController@guardar - INICIO', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'has_files' => $request->hasFile('foto_0_file') ? 'si' : 'no'
        ]);

        $user = Auth::user();

        if (!$user) {
            Log::warning('ProfileController@guardar - Usuario no autenticado');
            return redirect()->route('login');
        }

        Log::info('ProfileController@guardar - Usuario autenticado', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        try {
            // ============================================================
            // LOG DE DATOS RECIBIDOS
            // ============================================================
            Log::info('ProfileController@guardar - Datos recibidos', [
                'all_data' => $request->all(),
                'files_keys' => array_keys($request->allFiles()),
                'total_fotos' => $request->input('total_fotos', 0)
            ]);

            // ============================================================
            // VALIDACIÓN
            // ============================================================
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
                'total_fotos' => 'nullable|integer|min:0|max:8',
            ]);

            Log::info('ProfileController@guardar - Validación exitosa', [
                'validated_data' => $validated
            ]);

            // ============================================================
            // ACTUALIZAR USUARIO
            // ============================================================
            $userData = [];
            if (isset($validated['nickname']) && !empty($validated['nickname'])) {
                $userData['nombre'] = $validated['nickname'];
                $userData['apodo'] = $validated['nickname'];
            }
            if (isset($validated['ciudad']) && !empty($validated['ciudad'])) {
                $userData['ciudad'] = $validated['ciudad'];
            }
            
            if (!empty($userData)) {
                Log::info('ProfileController@guardar - Actualizando usuario', $userData);
                $user->update($userData);
            }

            if (!$user->fecha_nacimiento && isset($validated['edad']) && $validated['edad']) {
                $fechaNacimiento = Carbon::now()->subYears($validated['edad'])->startOfDay();
                $user->fecha_nacimiento = $fechaNacimiento;
                $user->save();
                Log::info('ProfileController@guardar - Fecha de nacimiento establecida', [
                    'edad' => $validated['edad'],
                    'fecha' => $fechaNacimiento
                ]);
            }

            // ============================================================
            // OBTENER O CREAR PERFIL
            // ============================================================
            $perfil = Perfil::where('usuario_id', $user->id)->first();
            
            // CORRECCIÓN: Asegurar que metadatos sea un array
            $metadatos = [];
            if ($perfil) {
                $metadatos = $perfil->metadatos;
                if (is_string($metadatos)) {
                    $metadatos = json_decode($metadatos, true) ?? [];
                } elseif (!is_array($metadatos)) {
                    $metadatos = [];
                }
            }
            
            // Agregar nuevos datos a metadatos
            if (isset($validated['edad'])) {
                $metadatos['edad'] = $validated['edad'];
            }
            if (isset($validated['ocupacion'])) {
                $metadatos['ocupacion'] = $validated['ocupacion'];
            }
            
            // CORRECCIÓN: Mapear visibilidad de fotos para la BD
            $visibilidadMap = [
                'todos' => 'publico',
                'matches' => 'coincidencias',
                'nadie' => 'oculto'
            ];
            
            if (isset($validated['visibilidadFotos'])) {
                $metadatos['visibilidad_fotos'] = $validated['visibilidadFotos'];
                $privacidadBD = $visibilidadMap[$validated['visibilidadFotos']] ?? 'publico';
            } else {
                $privacidadBD = 'publico';
            }

            if (isset($validated['tipoPerfil']) && $validated['tipoPerfil'] === 'pareja' && isset($validated['pareja'])) {
                $metadatos['pareja'] = [
                    'nombre1' => $validated['pareja']['nombre1'] ?? '',
                    'edad1' => $validated['pareja']['edad1'] ?? null,
                    'nombre2' => $validated['pareja']['nombre2'] ?? '',
                    'edad2' => $validated['pareja']['edad2'] ?? null,
                    'visibleParaAmbos' => $validated['pareja']['visibleParaAmbos'] ?? false,
                ];
                Log::info('ProfileController@guardar - Datos de pareja configurados', $metadatos['pareja']);
            }

            // ============================================================
            // ACTUALIZAR O CREAR PERFIL
            // ============================================================
            $perfilData = [];
            
            if (isset($validated['tipoPerfil'])) {
                $perfilData['tipo'] = $validated['tipoPerfil'] === 'pareja' ? 'pareja' : 'personal';
            }
            if (isset($validated['bio'])) {
                $perfilData['descripcion'] = $validated['bio'];
            }
            
            // CORRECCIÓN: Asegurar que intereses y pasatiempos sean arrays
            if (isset($validated['intereses']) && is_array($validated['intereses'])) {
                $perfilData['intereses'] = $validated['intereses'];
                Log::info('ProfileController@guardar - Intereses guardados', [
                    'count' => count($validated['intereses']),
                    'intereses' => $validated['intereses']
                ]);
            } else {
                $perfilData['intereses'] = [];
            }
            
            if (isset($validated['buscando']) && is_array($validated['buscando'])) {
                $perfilData['pasatiempos'] = $validated['buscando'];
                Log::info('ProfileController@guardar - Pasatiempos guardados', [
                    'count' => count($validated['buscando']),
                    'pasatiempos' => $validated['buscando']
                ]);
            } else {
                $perfilData['pasatiempos'] = [];
            }
            
            if (isset($validated['ciudad']) && !empty($validated['ciudad'])) {
                $perfilData['ubicacion_ciudad'] = $validated['ciudad'];
            }
            
            // CORRECCIÓN: Usar el valor mapeado para la BD
            $perfilData['privacidad_fotos'] = $privacidadBD;
            
            // CORRECCIÓN: Asegurar que metadatos sea un array antes de asignarlo
            $perfilData['metadatos'] = $metadatos;
            
            Log::info('ProfileController@guardar - Datos a guardar en perfil', $perfilData);
            
            $perfil = Perfil::updateOrCreate(
                ['usuario_id' => $user->id],
                $perfilData
            );

            // Recargar el perfil para asegurar que tenemos los datos actualizados
            $perfil->refresh();

            Log::info('ProfileController@guardar - Perfil guardado/actualizado', [
                'perfil_id' => $perfil->id,
                'tipo' => $perfil->tipo
            ]);

            // ============================================================
            // PROCESAR FOTOS
            // ============================================================
            $totalFotos = (int)$request->input('total_fotos', 0);
            Log::info('ProfileController@guardar - Procesando fotos', [
                'total_fotos' => $totalFotos
            ]);
            
            $fotoPrincipalRuta = null;
            $idsAMantener = [];
            $rutasAMantener = [];
            
            // PASO 1: Recopilar IDs y rutas de fotos existentes a mantener
            for ($i = 0; $i < $totalFotos; $i++) {
                $tipo = $request->input("foto_{$i}_tipo");
                Log::debug("ProfileController@guardar - Foto {$i}", [
                    'tipo' => $tipo,
                    'principal' => $request->input("foto_{$i}_principal", '0')
                ]);
                
                if ($tipo === 'existente_id') {
                    $id = $request->input("foto_{$i}_id");
                    if ($id) {
                        $idsAMantener[] = (int)$id;
                        Log::debug("ProfileController@guardar - Foto existente_id: {$id}");
                    }
                } elseif ($tipo === 'existente_ruta') {
                    $ruta = $request->input("foto_{$i}_ruta");
                    if ($ruta) {
                        $rutasAMantener[] = $ruta;
                        Log::debug("ProfileController@guardar - Foto existente_ruta: {$ruta}");
                    }
                }
            }
            
            // PASO 2: Eliminar fotos que no están en las listas
            $fotosAEliminar = Fotos::where('perfil_id', $perfil->id)->get();
            Log::info('ProfileController@guardar - Fotos existentes en BD', [
                'total' => $fotosAEliminar->count()
            ]);
            
            $fotosAEliminar = $fotosAEliminar->filter(function($foto) use ($idsAMantener, $rutasAMantener) {
                $enId = in_array($foto->id, $idsAMantener);
                $enRuta = in_array($foto->ruta_foto, $rutasAMantener);
                return !$enId && !$enRuta;
            });
            
            Log::info('ProfileController@guardar - Fotos a eliminar', [
                'total_eliminar' => $fotosAEliminar->count()
            ]);
            
            foreach ($fotosAEliminar as $foto) {
                Log::debug("ProfileController@guardar - Eliminando foto ID: {$foto->id}, ruta: {$foto->ruta_foto}");
                if ($foto->ruta_foto && Storage::disk('public')->exists($foto->ruta_foto)) {
                    Storage::disk('public')->delete($foto->ruta_foto);
                    Log::debug("ProfileController@guardar - Archivo eliminado: {$foto->ruta_foto}");
                }
                $foto->delete();
            }
            
            // PASO 3: Procesar nuevas fotos y actualizar existentes
            $fotosProcesadas = 0;
            for ($i = 0; $i < $totalFotos; $i++) {
                $tipo = $request->input("foto_{$i}_tipo");
                $principal = $request->input("foto_{$i}_principal", '0');
                $esPrincipal = $principal === '1' || $principal === true;
                
                if ($tipo === 'existente_id') {
                    $id = $request->input("foto_{$i}_id");
                    if ($id) {
                        $foto = Fotos::find((int)$id);
                        if ($foto) {
                            if ($esPrincipal) {
                                Fotos::where('perfil_id', $perfil->id)
                                    ->where('id', '!=', $foto->id)
                                    ->update(['es_principal' => false]);
                                Log::debug("ProfileController@guardar - Foto {$id} marcada como principal");
                            }
                            
                            $foto->es_principal = $esPrincipal;
                            $foto->save();
                            $fotosProcesadas++;
                            
                            if ($esPrincipal) {
                                $fotoPrincipalRuta = $foto->ruta_foto;
                            }
                        }
                    }
                } elseif ($tipo === 'existente_ruta') {
                    $ruta = $request->input("foto_{$i}_ruta");
                    if ($ruta) {
                        $foto = Fotos::where('perfil_id', $perfil->id)
                            ->where('ruta_foto', $ruta)
                            ->first();
                        
                        if ($foto) {
                            if ($esPrincipal) {
                                Fotos::where('perfil_id', $perfil->id)
                                    ->where('id', '!=', $foto->id)
                                    ->update(['es_principal' => false]);
                                Log::debug("ProfileController@guardar - Foto por ruta {$ruta} marcada como principal");
                            }
                            
                            $foto->es_principal = $esPrincipal;
                            $foto->save();
                            $fotosProcesadas++;
                            
                            if ($esPrincipal) {
                                $fotoPrincipalRuta = $ruta;
                            }
                        }
                    }
                } elseif ($tipo === 'nueva') {
                    $file = $request->file("foto_{$i}_file");
                    Log::debug("ProfileController@guardar - Procesando foto nueva {$i}", [
                        'has_file' => $file ? 'si' : 'no',
                        'is_valid' => $file && $file->isValid() ? 'si' : 'no',
                        'size' => $file ? $file->getSize() : null,
                        'mime' => $file ? $file->getMimeType() : null
                    ]);
                    
                    if ($file && $file->isValid()) {
                        $rutaFoto = $file->store('perfil/fotos', 'public');
                        Log::info("ProfileController@guardar - Foto guardada", [
                            'ruta' => $rutaFoto
                        ]);
                        
                        if ($esPrincipal) {
                            Fotos::where('perfil_id', $perfil->id)->update(['es_principal' => false]);
                        }
                        
                        // Obtener permisos de la foto desde los metadatos
                        $permisos = [$perfil->privacidad_fotos ?? 'publico'];
                        
                        $nuevaFoto = Fotos::create([
                            'perfil_id' => $perfil->id,
                            'ruta_foto' => $rutaFoto,
                            'es_principal' => $esPrincipal,
                            'fecha_subida' => now(),
                            'permisos' => $permisos,
                        ]);
                        
                        $fotosProcesadas++;
                        Log::info("ProfileController@guardar - Nueva foto creada", [
                            'foto_id' => $nuevaFoto->id,
                            'es_principal' => $esPrincipal
                        ]);
                        
                        if ($esPrincipal) {
                            $fotoPrincipalRuta = $rutaFoto;
                        }
                    }
                }
            }
            
            Log::info('ProfileController@guardar - Fotos procesadas', [
                'total_procesadas' => $fotosProcesadas,
                'foto_principal' => $fotoPrincipalRuta
            ]);
            
            // PASO 4: Actualizar foto_principal en users
            if ($fotoPrincipalRuta) {
                $user->foto_principal = $fotoPrincipalRuta;
                $user->save();
                Log::info('ProfileController@guardar - Foto principal actualizada en usuario', [
                    'ruta' => $fotoPrincipalRuta
                ]);
            } else {
                $fotoPrincipal = Fotos::where('perfil_id', $perfil->id)
                    ->where('es_principal', true)
                    ->first();
                    
                if ($fotoPrincipal) {
                    $user->foto_principal = $fotoPrincipal->ruta_foto;
                    $user->save();
                    Log::info('ProfileController@guardar - Foto principal recuperada de BD', [
                        'ruta' => $fotoPrincipal->ruta_foto
                    ]);
                }
            }

            // PASO 5: Verificar si el perfil está completo
            $totalFotosGuardadas = Fotos::where('perfil_id', $perfil->id)->count();
            $tieneFotoPrincipal = !empty($user->foto_principal);
            
            // CORRECCIÓN: Asegurar que intereses y pasatiempos sean arrays
            $intereses = $perfil->intereses ?? [];
            if (is_string($intereses)) {
                $intereses = json_decode($intereses, true) ?? [];
            }
            $pasatiempos = $perfil->pasatiempos ?? [];
            if (is_string($pasatiempos)) {
                $pasatiempos = json_decode($pasatiempos, true) ?? [];
            }
            
            $interesesCount = count($intereses);
            $pasatiemposCount = count($pasatiempos);
            $descripcionLength = strlen($perfil->descripcion ?? '');
            
            Log::info('ProfileController@guardar - Evaluando completitud', [
                'total_fotos' => $totalFotosGuardadas,
                'tiene_foto_principal' => $tieneFotoPrincipal,
                'intereses_count' => $interesesCount,
                'pasatiempos_count' => $pasatiemposCount,
                'descripcion_length' => $descripcionLength,
                'tiene_ciudad' => !empty($user->ciudad)
            ]);
            
            $completo = (
                $tieneFotoPrincipal &&
                $totalFotosGuardadas >= 4 &&
                $interesesCount >= 3 &&
                $pasatiemposCount >= 2 &&
                $descripcionLength >= 50 &&
                !empty($user->ciudad)
            );
            
            Log::info('ProfileController@guardar - Perfil completo', [
                'completo' => $completo ? 'SI' : 'NO'
            ]);
            
            // Actualizar metadatos con la información de completitud
            $metadatosActualizados = $perfil->metadatos;
            if (is_string($metadatosActualizados)) {
                $metadatosActualizados = json_decode($metadatosActualizados, true) ?? [];
            } elseif (!is_array($metadatosActualizados)) {
                $metadatosActualizados = [];
            }
            
            $metadatosActualizados['perfil_completo'] = $completo;
            $metadatosActualizados['fotos_completadas'] = $totalFotosGuardadas >= 4;
            $perfil->metadatos = $metadatosActualizados;
            $perfil->save();
            
            if ($completo && $user->estado === 'incompleto') {
                $user->update([
                    'estado' => 'pendiente',
                ]);
                Log::info('ProfileController@guardar - Estado de usuario actualizado a pendiente');
            }

            Log::info('ProfileController@guardar - FINALIZADO CON ÉXITO');

            return redirect()->route('perfil.completar')->with('flash', [
                'toast' => [
                    'type' => $completo ? 'success' : 'info',
                    'title' => $completo ? '¡Perfil completado!' : 'Perfil guardado',
                    'message' => $completo 
                        ? '¡Felicidades! Tu perfil está completo. Será revisado por el equipo.'
                        : 'Tu perfil ha sido guardado. Continúa completando la información.',
                    'duration' => 5000,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ProfileController@guardar - ERROR DE VALIDACIÓN', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            
            return redirect()->route('perfil.completar')
                ->withErrors($e->errors())
                ->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'title' => 'Error de validación',
                        'message' => 'Por favor, revisa los campos marcados.',
                        'duration' => 5000,
                    ]
                ]);
        } catch (\Exception $e) {
            Log::error('ProfileController@guardar - ERROR INESPERADO', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return redirect()->route('perfil.completar')->with('flash', [
                'toast' => [
                    'type' => 'error',
                    'title' => 'Error del sistema',
                    'message' => 'Ha ocurrido un error inesperado: ' . $e->getMessage(),
                    'duration' => 5000,
                ]
            ]);
        }
    }

    /**
     * Guarda el borrador del perfil
     */
    public function borrador(Request $request)
    {
        Log::info('ProfileController@borrador - INICIO');
        return $this->guardar($request);
    }

    /**
     * Actualiza un perfil existente
     */
    public function actualizar(Request $request)
    {
        Log::info('ProfileController@actualizar - INICIO');
        return $this->guardar($request);
    }

    /**
     * Obtiene la URL pública de una ruta de imagen
     */
    private function getUrlFromPath($path)
    {
        if (empty($path)) {
            return '/images/shared/avatar-default.jpg';
        }
        
        // Si ya es una URL completa
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        // Si ya tiene /storage/
        if (strpos($path, '/storage/') === 0) {
            return $path;
        }
        
        // Si tiene storage/ sin slash inicial
        if (strpos($path, 'storage/') === 0) {
            return '/' . $path;
        }
        
        // Caso por defecto
        return asset('storage/' . ltrim($path, '/'));
    }
}