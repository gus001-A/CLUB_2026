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
                    'url' => $this->getUrlFromPath($foto->ruta_foto),
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
        
        if (!$user) {
            return redirect()->route('login');
        }

        try {
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
            // PROCESAR FOTOS
            // ============================================================
            
            // 1. ELIMINAR FOTOS MARCADAS
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
                    }
                }
            }

            // 2. PROCESAR FOTOS ENVIADAS
            $totalFotos = (int) $request->input('total_fotos', 0);
            $fotoPrincipalRuta = null;
            $fotosNuevas = 0;
            $idsExistentes = [];
            $reglaArchivo = 'image|mimes:jpg,jpeg,png,webp|max:5120';

            for ($i = 0; $i < $totalFotos; $i++) {
                $tipo = $request->input("foto_{$i}_tipo");

                if (!$tipo) {
                    continue;
                }

                $esPrincipal = $request->input("foto_{$i}_principal") === '1';

                // FOTO NUEVA
                if ($tipo === 'nueva') {
                    $file = $request->file("foto_{$i}_file");

                    if (!$file || !$file->isValid()) {
                        continue;
                    }

                    $validator = \Illuminate\Support\Facades\Validator::make(
                        ['archivo' => $file],
                        ['archivo' => $reglaArchivo]
                    );

                    if ($validator->fails()) {
                        continue;
                    }

                    $rutaFoto = $file->store('perfil/fotos', 'public');

                    if ($esPrincipal) {
                        Fotos::where('perfil_id', $perfil->id)->update(['es_principal' => false]);
                    }

                    $nuevaFoto = Fotos::create([
                        'perfil_id' => $perfil->id,
                        'ruta_foto' => $rutaFoto,
                        'es_principal' => $esPrincipal,
                        'fecha_subida' => now(),
                        'permisos' => [$validated['visibilidadFotos'] ?? 'todos'],
                    ]);

                    $idsExistentes[] = $nuevaFoto->id;
                    $fotosNuevas++;

                    if ($esPrincipal) {
                        $fotoPrincipalRuta = $rutaFoto;
                    }

                    continue;
                }

                // REEMPLAZO: misma foto, nuevo archivo
                if ($tipo === 'reemplazo') {
                    $fotoId = $request->input("foto_{$i}_id");
                    $file = $request->file("foto_{$i}_file");
                    $foto = $fotoId ? Fotos::where('perfil_id', $perfil->id)->where('id', $fotoId)->first() : null;

                    if (!$foto || !$file || !$file->isValid()) {
                        continue;
                    }

                    $validator = \Illuminate\Support\Facades\Validator::make(
                        ['archivo' => $file],
                        ['archivo' => $reglaArchivo]
                    );

                    if ($validator->fails()) {
                        $idsExistentes[] = $foto->id;
                        continue;
                    }

                    $rutaAnterior = $foto->ruta_foto;
                    $rutaNueva = $file->store('perfil/fotos', 'public');

                    if ($esPrincipal) {
                        Fotos::where('perfil_id', $perfil->id)
                            ->where('id', '!=', $foto->id)
                            ->update(['es_principal' => false]);
                    }

                    $foto->ruta_foto = $rutaNueva;
                    $foto->es_principal = $esPrincipal;
                    $foto->fecha_subida = now();
                    $foto->save();

                    if ($rutaAnterior && $rutaAnterior !== $rutaNueva && Storage::disk('public')->exists($rutaAnterior)) {
                        Storage::disk('public')->delete($rutaAnterior);
                    }

                    $idsExistentes[] = $foto->id;

                    if ($esPrincipal) {
                        $fotoPrincipalRuta = $rutaNueva;
                    }

                    continue;
                }

                // FOTO EXISTENTE (solo cambia si es principal)
                if ($tipo === 'existente_id') {
                    $fotoId = $request->input("foto_{$i}_id");
                    $foto = $fotoId ? Fotos::where('perfil_id', $perfil->id)->where('id', $fotoId)->first() : null;

                    if (!$foto) {
                        continue;
                    }

                    $idsExistentes[] = $foto->id;

                    if ($esPrincipal) {
                        Fotos::where('perfil_id', $perfil->id)
                            ->where('id', '!=', $foto->id)
                            ->update(['es_principal' => false]);
                    }

                    $foto->es_principal = $esPrincipal;
                    $foto->save();

                    if ($esPrincipal) {
                        $fotoPrincipalRuta = $foto->ruta_foto;
                    }

                    continue;
                }

                // FOTO EXISTENTE sin id (fallback por ruta)
                if ($tipo === 'existente_ruta') {
                    $ruta = $request->input("foto_{$i}_ruta");
                    $foto = $ruta ? Fotos::where('perfil_id', $perfil->id)->where('ruta_foto', $ruta)->first() : null;

                    if (!$foto) {
                        continue;
                    }

                    $idsExistentes[] = $foto->id;

                    if ($esPrincipal) {
                        Fotos::where('perfil_id', $perfil->id)
                            ->where('id', '!=', $foto->id)
                            ->update(['es_principal' => false]);
                    }

                    $foto->es_principal = $esPrincipal;
                    $foto->save();

                    if ($esPrincipal) {
                        $fotoPrincipalRuta = $foto->ruta_foto;
                    }
                }
            }

            // 3. ELIMINAR FOTOS HUÉRFANAS
            if (!empty($idsExistentes)) {
                $fotosOrfanas = Fotos::where('perfil_id', $perfil->id)
                    ->whereNotIn('id', $idsExistentes)
                    ->get();
                    
                foreach ($fotosOrfanas as $foto) {
                    if (!in_array($foto->id, $fotosEliminar)) {
                        if ($foto->ruta_foto && Storage::disk('public')->exists($foto->ruta_foto)) {
                            Storage::disk('public')->delete($foto->ruta_foto);
                        }
                        $foto->delete();
                        $fotosEliminadas++;
                    }
                }
            }

            // 4. ACTUALIZAR FOTO PRINCIPAL EN USERS
            if ($fotoPrincipalRuta) {
                $user->foto_principal = $fotoPrincipalRuta;
                $user->save();
            } else {
                $fotoPrincipal = Fotos::where('perfil_id', $perfil->id)
                    ->where('es_principal', true)
                    ->first();
                    
                if ($fotoPrincipal) {
                    $user->foto_principal = $fotoPrincipal->ruta_foto;
                    $user->save();
                } else {
                    $primeraFoto = Fotos::where('perfil_id', $perfil->id)->first();
                    if ($primeraFoto) {
                        $primeraFoto->es_principal = true;
                        $primeraFoto->save();
                        $user->foto_principal = $primeraFoto->ruta_foto;
                        $user->save();
                    } else {
                        $user->foto_principal = null;
                        $user->save();
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

            return redirect()->route('perfil.ver')->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => 'Perfil actualizado',
                    'message' => $mensajeFinal,
                    'duration' => 5000,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
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