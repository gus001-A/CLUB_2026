<?php

namespace App\Http\Controllers\Usuario;

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
        
        if (!$user) {
            Log::error('Usuario no autenticado en completar perfil');
            return redirect()->route('login');
        }

        $perfil = Perfil::where('usuario_id', $user->id)->first();
        
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

        // ============================================================
        // PREPARAR DATOS DEL PERFIL
        // ============================================================
        
        $perfilData = null;
        if ($perfil) {
            $fotosList = [];
            $fotosQuery = $perfil->fotos()->orderBy('es_principal', 'desc')->get();
            
            if ($fotosQuery->count() > 0) {
                foreach ($fotosQuery as $foto) {
                    $fotosList[] = [
                        'id' => $foto->id,
                        'url' => $foto->url,
                        'principal' => (bool) $foto->es_principal,
                        'ruta_foto' => $foto->ruta_foto,
                    ];
                }
            }
            
            if (empty($fotosList) && !empty($perfil->fotos) && is_array($perfil->fotos)) {
                $fotosList = collect($perfil->fotos)->map(function($foto) {
                    return [
                        'id' => $foto['id'] ?? null,
                        'url' => $foto['url'] ?? null,
                        'principal' => $foto['principal'] ?? false,
                        'ruta_foto' => $foto['ruta_foto'] ?? null,
                    ];
                })->toArray();
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

            if (isset($perfil->metadatos['edad'])) {
                $userData['edad'] = $perfil->metadatos['edad'];
            }
            if (isset($perfil->metadatos['ocupacion'])) {
                $userData['ocupacion'] = $perfil->metadatos['ocupacion'];
            }
            if (isset($perfil->metadatos['pareja'])) {
                $perfilData['pareja'] = $perfil->metadatos['pareja'];
            }
            if (isset($perfil->metadatos['visibilidad_fotos'])) {
                $perfilData['privacidad_fotos'] = $perfil->metadatos['visibilidad_fotos'];
            }

        } else {
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

        try {
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
                $user->update($userData);
            }

            if (!$user->fecha_nacimiento && isset($validated['edad']) && $validated['edad']) {
                $fechaNacimiento = Carbon::now()->subYears($validated['edad'])->startOfDay();
                $user->fecha_nacimiento = $fechaNacimiento;
                $user->save();
            }

            // ============================================================
            // PREPARAR METADATOS
            // ============================================================
            
            $metadatos = [];
            
            if (isset($validated['edad'])) {
                $metadatos['edad'] = $validated['edad'];
            }
            if (isset($validated['ocupacion'])) {
                $metadatos['ocupacion'] = $validated['ocupacion'];
            }
            if (isset($validated['visibilidadFotos'])) {
                $metadatos['visibilidad_fotos'] = $validated['visibilidadFotos'];
            }

            if (isset($validated['tipoPerfil']) && $validated['tipoPerfil'] === 'pareja' && isset($validated['pareja'])) {
                $metadatos['pareja'] = [
                    'nombre1' => $validated['pareja']['nombre1'] ?? '',
                    'edad1' => $validated['pareja']['edad1'] ?? null,
                    'nombre2' => $validated['pareja']['nombre2'] ?? '',
                    'edad2' => $validated['pareja']['edad2'] ?? null,
                    'visibleParaAmbos' => $validated['pareja']['visibleParaAmbos'] ?? false,
                ];
            }

            $perfil = Perfil::where('usuario_id', $user->id)->first();
            
            if ($perfil && isset($perfil->metadatos)) {
                $metadatos = array_merge($perfil->metadatos ?? [], $metadatos);
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
            if (isset($validated['intereses']) && is_array($validated['intereses'])) {
                $perfilData['intereses'] = $validated['intereses'];
            }
            if (isset($validated['buscando']) && is_array($validated['buscando'])) {
                $perfilData['pasatiempos'] = $validated['buscando'];
            }
            if (isset($validated['ciudad']) && !empty($validated['ciudad'])) {
                $perfilData['ubicacion_ciudad'] = $validated['ciudad'];
            }
            if (isset($validated['visibilidadFotos'])) {
                $perfilData['privacidad_fotos'] = $validated['visibilidadFotos'];
            }
            
            $perfilData['metadatos'] = $metadatos;
            
            $perfil = Perfil::updateOrCreate(
                ['usuario_id' => $user->id],
                $perfilData
            );

            // ============================================================
            // 🔥 PROCESAR FOTOS
            // ============================================================
            
            $fotosEnviadas = $request->input('fotos', []);
            $fotoPrincipalRuta = null;
            
            // Obtener IDs de fotos existentes que se mantienen
            $idsAMantener = [];
            
            foreach ($fotosEnviadas as $fotoData) {
                if (isset($fotoData['vacia']) && $fotoData['vacia'] == '1') {
                    continue;
                }
                
                if (isset($fotoData['id']) && !empty($fotoData['id'])) {
                    $idsAMantener[] = $fotoData['id'];
                }
            }
            
            // ELIMINAR fotos que no están en la lista de mantenimiento
            $fotosAEliminar = Fotos::where('perfil_id', $perfil->id)
                ->whereNotIn('id', $idsAMantener)
                ->get();
                
            foreach ($fotosAEliminar as $foto) {
                // Eliminar archivo físico
                if ($foto->ruta_foto && Storage::disk('public')->exists($foto->ruta_foto)) {
                    Storage::disk('public')->delete($foto->ruta_foto);
                }
                $foto->delete();
                Log::info('🗑️ Foto eliminada', ['id' => $foto->id, 'ruta' => $foto->ruta_foto]);
            }
            
            // Procesar fotos enviadas
            foreach ($fotosEnviadas as $index => $fotoData) {
                if (isset($fotoData['vacia']) && $fotoData['vacia'] == '1') {
                    continue;
                }
                
                $esPrincipal = isset($fotoData['principal']) && (
                    $fotoData['principal'] === true || 
                    $fotoData['principal'] === '1' || 
                    $fotoData['principal'] === 1
                );
                
                // Si tiene ID, actualizar
                if (isset($fotoData['id']) && !empty($fotoData['id'])) {
                    $foto = Fotos::find($fotoData['id']);
                    if ($foto) {
                        $foto->es_principal = $esPrincipal;
                        $foto->save();
                        
                        if ($esPrincipal) {
                            $fotoPrincipalRuta = $foto->ruta_foto;
                        }
                    }
                    continue;
                }
                
                // Si es una nueva foto
                $rutaFoto = null;
                
                if (isset($fotoData['file']) && $fotoData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $rutaFoto = $fotoData['file']->store('perfil/fotos', 'public');
                } elseif (isset($fotoData['ruta_foto']) && !empty($fotoData['ruta_foto'])) {
                    $rutaFoto = ltrim(str_replace('/storage/', '', $fotoData['ruta_foto']), '/');
                }
                
                if (empty($rutaFoto)) {
                    continue;
                }
                
                $nuevaFoto = Fotos::create([
                    'perfil_id' => $perfil->id,
                    'ruta_foto' => $rutaFoto,
                    'es_principal' => $esPrincipal,
                    'fecha_subida' => now(),
                    'permisos' => [$validated['visibilidadFotos'] ?? 'todos'],
                ]);
                
                Log::info('✅ Nueva foto guardada', ['id' => $nuevaFoto->id]);
                
                if ($esPrincipal) {
                    $fotoPrincipalRuta = $rutaFoto;
                }
            }
            
            // ============================================================
            // 🔥 ACTUALIZAR FOTO PRINCIPAL EN LA TABLA USERS
            // ============================================================
            
            // Si hay una foto principal definida, guardarla en users
            if ($fotoPrincipalRuta) {
                $user->foto_principal = $fotoPrincipalRuta;
                $user->save();
                Log::info('👤 Foto principal asignada en users', [
                    'user_id' => $user->id,
                    'foto_principal' => $fotoPrincipalRuta,
                ]);
            } else {
                // Si no se definió una nueva foto principal, verificar si hay alguna
                $fotoPrincipal = Fotos::where('perfil_id', $perfil->id)
                    ->where('es_principal', true)
                    ->first();
                    
                if ($fotoPrincipal) {
                    $user->foto_principal = $fotoPrincipal->ruta_foto;
                    $user->save();
                    Log::info('👤 Foto principal existente asignada', [
                        'user_id' => $user->id,
                        'foto_principal' => $fotoPrincipal->ruta_foto,
                    ]);
                } elseif ($user->foto_principal) {
                    // Mantener la foto principal actual si no se ha cambiado
                    Log::info('👤 Manteniendo foto principal actual', [
                        'user_id' => $user->id,
                        'foto_principal' => $user->foto_principal,
                    ]);
                }
            }

            // ============================================================
            // VERIFICAR SI EL PERFIL ESTÁ COMPLETO
            // ============================================================
            
            $totalFotos = Fotos::where('perfil_id', $perfil->id)->count();
            $tieneFotoPrincipal = !empty($user->foto_principal);
            $interesesCount = count($perfil->intereses ?? []);
            $pasatiemposCount = count($perfil->pasatiempos ?? []);
            $descripcionLength = strlen($perfil->descripcion ?? '');
            
            $completo = (
                $tieneFotoPrincipal &&
                $totalFotos >= 4 &&
                $interesesCount >= 3 &&
                $pasatiemposCount >= 2 &&
                $descripcionLength >= 50 &&
                !empty($user->ciudad)
            );
            
            $metadatos['perfil_completo'] = $completo;
            $perfil->metadatos = $metadatos;
            $perfil->save();
            
            if ($completo && $user->estado === 'incompleto') {
                $user->update([
                    'estado' => 'pendiente',
                ]);
                Log::info('👤 Usuario cambiado a estado "pendiente"', ['user_id' => $user->id]);
            }

            Log::info('✅ Perfil guardado exitosamente', [
                'user_id' => $user->id,
                'completo' => $completo,
                'foto_principal_final' => $user->foto_principal,
                'total_fotos' => $totalFotos,
            ]);

            return redirect()->route('perfil.completar')->with('flash', [
                'toast' => [
                    'type' => $completo ? 'success' : 'info',
                    'title' => $completo ? '¡Perfil completado!' : 'Perfil guardado',
                    'message' => $completo 
                        ? '¡Felicidades! Tu perfil está completo. Será revisado por el equipo de Club de Fantasías.'
                        : 'Tu perfil ha sido guardado. Continúa completando la información.',
                    'duration' => 5000,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('❌ ERROR DE VALIDACIÓN', [
                'errors' => $e->errors(),
                'user_id' => $user->id,
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
            Log::error('❌ ERROR al guardar perfil', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('perfil.completar')->with('flash', [
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
     * Guarda el borrador del perfil
     */
    public function borrador(Request $request)
    {
        return $this->guardar($request);
    }

    /**
     * Actualiza un perfil existente
     */
    public function actualizar(Request $request)
    {
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
        
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        if (strpos($path, '/storage/') === 0) {
            return $path;
        }
        
        return asset('storage/' . ltrim($path, '/'));
    }
}