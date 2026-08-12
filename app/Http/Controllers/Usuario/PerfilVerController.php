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

class PerfilVerController extends Controller
{
    /**
     * Muestra la página de perfil del usuario
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $perfil = Perfil::where('usuario_id', $user->id)->first();
        
        // Datos del usuario
        $edad = null;
        if ($user->fecha_nacimiento) {
            $edad = Carbon::parse($user->fecha_nacimiento)->age;
        }

        $avatar = '/images/shared/avatar-default.jpg';
        if ($user->foto_principal) {
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
            'avatar' => $avatar,
            'foto_principal' => $user->foto_principal,
        ];

        // Datos del perfil
        $perfilData = null;
        if ($perfil) {
            $fotosList = [];
            $fotosQuery = $perfil->fotos()->orderBy('es_principal', 'desc')->get();
            
            foreach ($fotosQuery as $foto) {
                $fotosList[] = [
                    'id' => $foto->id,
                    'url' => $foto->url,
                    'principal' => (bool) $foto->es_principal,
                    'ruta_foto' => $foto->ruta_foto,
                ];
            }

            $perfilData = [
                'id' => $perfil->id,
                'tipo' => $perfil->tipo ?? 'personal',
                'descripcion' => $perfil->descripcion ?? '',
                'intereses' => $perfil->intereses ?? [],
                'pasatiempos' => $perfil->pasatiempos ?? [],
                'fotos' => $fotosList,
                'privacidad_fotos' => $perfil->privacidad_fotos ?? 'todos',
                'esta_verificado' => $perfil->esta_verificado ?? false,
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
        }

        return Inertia::render('Profile/Perfil', [
            'user' => $userData,
            'perfil' => $perfilData,
        ]);
    }

    /**
     * Actualiza el perfil del usuario
     */
    public function actualizar(Request $request)
    {
        $user = Auth::user();
        
        Log::info('=== INICIO actualizar perfil ===', ['user_id' => $user->id]);

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
                'fotos_eliminar' => 'nullable|array',
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
            
            $perfil = Perfil::where('usuario_id', $user->id)->first();
            $metadatos = $perfil->metadatos ?? [];
            
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

            // ============================================================
            // ACTUALIZAR PERFIL
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
            // PROCESAR FOTOS - CORREGIDO
            // ============================================================
            
            // 1. ELIMINAR FOTOS
            $fotosEliminar = $request->input('fotos_eliminar', []);
            $fotosEliminadas = 0;
            
            if (!empty($fotosEliminar)) {
                foreach ($fotosEliminar as $fotoId) {
                    $foto = Fotos::where('perfil_id', $perfil->id)
                        ->where('id', $fotoId)
                        ->first();
                        
                    if ($foto) {
                        if ($foto->ruta_foto && Storage::disk('public')->exists($foto->ruta_foto)) {
                            Storage::disk('public')->delete($foto->ruta_foto);
                        }
                        $foto->delete();
                        $fotosEliminadas++;
                        Log::info('Foto eliminada', ['id' => $fotoId]);
                    }
                }
            }

            // 2. GUARDAR NUEVAS FOTOS Y ACTUALIZAR EXISTENTES
            $fotosEnviadas = $request->input('fotos', []);
            $fotoPrincipalRuta = null;
            $fotosNuevas = 0;
            $idsExistentes = [];
            
            // Procesar todas las fotos enviadas
            foreach ($fotosEnviadas as $fotoData) {
                // Si tiene ID, es una foto existente
                if (isset($fotoData['id']) && !empty($fotoData['id'])) {
                    $foto = Fotos::find($fotoData['id']);
                    if ($foto) {
                        $idsExistentes[] = $fotoData['id'];
                        $esPrincipal = isset($fotoData['principal']) && (
                            $fotoData['principal'] === true || 
                            $fotoData['principal'] === '1' || 
                            $fotoData['principal'] === 1
                        );
                        $foto->es_principal = $esPrincipal;
                        $foto->save();
                        
                        if ($esPrincipal) {
                            $fotoPrincipalRuta = $foto->ruta_foto;
                        }
                    }
                    continue;
                }
                
                // Si es una foto nueva (tiene file)
                if (isset($fotoData['file']) && $fotoData['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $esPrincipal = isset($fotoData['principal']) && (
                        $fotoData['principal'] === true || 
                        $fotoData['principal'] === '1' || 
                        $fotoData['principal'] === 1
                    );
                    
                    $rutaFoto = $fotoData['file']->store('perfil/fotos', 'public');
                    
                    $nuevaFoto = Fotos::create([
                        'perfil_id' => $perfil->id,
                        'ruta_foto' => $rutaFoto,
                        'es_principal' => $esPrincipal,
                        'fecha_subida' => now(),
                        'permisos' => [$validated['visibilidadFotos'] ?? 'todos'],
                    ]);
                    
                    $fotosNuevas++;
                    Log::info('Nueva foto guardada', ['id' => $nuevaFoto->id, 'ruta' => $rutaFoto]);
                    
                    if ($esPrincipal) {
                        $fotoPrincipalRuta = $rutaFoto;
                    }
                }
            }
            
            // Eliminar fotos que no están en la lista de existentes
            $fotosAEliminar = Fotos::where('perfil_id', $perfil->id)
                ->whereNotIn('id', $idsExistentes)
                ->get();
                
            foreach ($fotosAEliminar as $foto) {
                if ($foto->ruta_foto && Storage::disk('public')->exists($foto->ruta_foto)) {
                    Storage::disk('public')->delete($foto->ruta_foto);
                }
                $foto->delete();
                $fotosEliminadas++;
                Log::info('Foto eliminada (no enviada)', ['id' => $foto->id]);
            }

            // ============================================================
            // ACTUALIZAR FOTO PRINCIPAL EN USERS
            // ============================================================
            
            if ($fotoPrincipalRuta) {
                $user->foto_principal = $fotoPrincipalRuta;
                $user->save();
                Log::info('Foto principal actualizada', ['ruta' => $fotoPrincipalRuta]);
            } else {
                // Buscar si hay alguna foto principal
                $fotoPrincipal = Fotos::where('perfil_id', $perfil->id)
                    ->where('es_principal', true)
                    ->first();
                    
                if ($fotoPrincipal) {
                    $user->foto_principal = $fotoPrincipal->ruta_foto;
                    $user->save();
                    Log::info('Foto principal encontrada y asignada', ['id' => $fotoPrincipal->id]);
                } elseif ($user->foto_principal) {
                    // Verificar que la foto principal aún existe
                    $fotoExiste = Fotos::where('perfil_id', $perfil->id)
                        ->where('ruta_foto', $user->foto_principal)
                        ->exists();
                        
                    if (!$fotoExiste) {
                        // Si no existe, buscar la primera foto
                        $primeraFoto = Fotos::where('perfil_id', $perfil->id)->first();
                        if ($primeraFoto) {
                            $primeraFoto->es_principal = true;
                            $primeraFoto->save();
                            $user->foto_principal = $primeraFoto->ruta_foto;
                            $user->save();
                            Log::info('Primera foto asignada como principal');
                        } else {
                            $user->foto_principal = null;
                            $user->save();
                            Log::info('No hay fotos, foto_principal eliminada');
                        }
                    }
                }
            }

            // ============================================================
            // VERIFICAR PERFIL COMPLETO
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

            // ============================================================
            // MENSAJES DE TOAST
            // ============================================================
            
            $mensajes = [];
            $mensajePrincipal = 'Tu perfil ha sido actualizado correctamente.';
            
            if ($fotosNuevas > 0) {
                $mensajes[] = "Se subieron {$fotosNuevas} foto(s) nueva(s)";
            }
            
            if ($fotosEliminadas > 0) {
                $mensajes[] = "Se eliminaron {$fotosEliminadas} foto(s)";
            }
            
            if ($completo && $user->estado === 'incompleto') {
                $user->update(['estado' => 'pendiente']);
                $mensajes[] = "¡Felicidades! Tu perfil ahora está completo";
            }
            
            $mensajeFinal = $mensajePrincipal;
            if (!empty($mensajes)) {
                $mensajeFinal = implode(' ', $mensajes);
            }

            Log::info('Perfil actualizado exitosamente', [
                'user_id' => $user->id,
                'completo' => $completo,
                'total_fotos' => $totalFotos,
                'fotos_nuevas' => $fotosNuevas,
                'fotos_eliminadas' => $fotosEliminadas,
                'foto_principal' => $user->foto_principal,
            ]);

            return redirect()->route('perfil.ver')->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => 'Perfil actualizado',
                    'message' => $mensajeFinal,
                    'duration' => 5000,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ERROR DE VALIDACION', [
                'errors' => $e->errors(),
                'user_id' => $user->id,
            ]);

            $errors = $e->errors();
            $firstError = reset($errors)[0] ?? 'Error de validación';
            $field = key($errors);
            
            $errorMessage = $firstError;
            $errorTitle = 'Error de validación';
            
            $fieldMessages = [
                'nickname' => ['title' => 'Nickname inválido', 'message' => 'El nickname debe tener entre 3 y 20 caracteres y solo letras, números y guiones bajos.'],
                'edad' => ['title' => 'Edad inválida', 'message' => 'La edad debe ser entre 18 y 120 años.'],
                'ciudad' => ['title' => 'Ciudad inválida', 'message' => 'La ciudad no puede tener más de 100 caracteres.'],
                'bio' => ['title' => 'Descripción muy larga', 'message' => 'La descripción no puede tener más de 500 caracteres.'],
                'fotos' => ['title' => 'Error en fotos', 'message' => 'Hubo un problema con las fotos. Verifica que sean imágenes válidas.'],
            ];
            
            if (isset($fieldMessages[$field])) {
                $errorTitle = $fieldMessages[$field]['title'];
                $errorMessage = $fieldMessages[$field]['message'];
            }

            return redirect()->route('perfil.ver')
                ->withErrors($e->errors())
                ->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'title' => $errorTitle,
                        'message' => $errorMessage,
                        'duration' => 5000,
                    ]
                ]);
            
        } catch (\Exception $e) {
            Log::error('ERROR al actualizar perfil', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('perfil.ver')->with('flash', [
                'toast' => [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'No se pudo actualizar el perfil. Por favor, intenta nuevamente.',
                    'duration' => 5000,
                ]
            ]);
        }
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