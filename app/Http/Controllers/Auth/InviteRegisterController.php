<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use App\Models\CodigoInvitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class InviteRegisterController extends Controller
{
    /**
     * Muestra el formulario de registro con invitacion
     */
    public function showRegistrationForm()
    {
        Log::info('Mostrando formulario de registro con invitacion');
        return Inertia::render('Auth/RegisterInvite');
    }

    /**
     * Maneja el registro de un nuevo usuario con codigo de invitacion
     */
    public function register(Request $request)
    {
        Log::info('Iniciando proceso de registro', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data' => $request->except(['password', 'password_confirmation'])
        ]);

        try {
            // Validacion de los campos
            Log::debug('Validando datos del formulario');
            $validated = $request->validate([
                'invite_code' => 'required|string|min:8|max:50',
                'profile_type' => 'required|string|in:pareja,hombre,mujer,trans,otro',
                'nickname' => 'required|string|min:3|max:20|regex:/^[A-Za-z0-9_]+$/|unique:users,apodo',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'city' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20',
                'birthdate' => 'required|date|before:today|after:1900-01-01',
                'accepts_terms' => 'required|boolean|accepted',
            ], [
                'invite_code.required' => 'El código de invitación es obligatorio',
                'invite_code.min' => 'El código debe tener al menos 8 caracteres',
                'profile_type.required' => 'Debes seleccionar un tipo de perfil',
                'profile_type.in' => 'Tipo de perfil no válido',
                'nickname.required' => 'El nickname es obligatorio',
                'nickname.min' => 'El nickname debe tener al menos 3 caracteres',
                'nickname.max' => 'El nickname no puede tener más de 20 caracteres',
                'nickname.regex' => 'El nickname solo puede contener letras, números y guiones bajos',
                'nickname.unique' => 'Este nickname ya está en uso',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'Ingresa un correo electrónico válido',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres',
                'password.confirmed' => 'Las contraseñas no coinciden',
                'birthdate.required' => 'La fecha de nacimiento es obligatoria',
                'birthdate.before' => 'La fecha de nacimiento debe ser anterior a hoy',
                'birthdate.after' => 'Por favor, ingresa una fecha válida',
                'accepts_terms.required' => 'Debes aceptar los términos y condiciones',
                'accepts_terms.accepted' => 'Debes aceptar los términos y condiciones',
            ]);

            Log::info('Validación exitosa', [
                'nickname' => $validated['nickname'],
                'email' => $validated['email'],
                'profile_type' => $validated['profile_type']
            ]);

            // Verificar que el usuario es mayor de 18 años
            Log::debug('Verificando edad del usuario');
            $birthDate = new \DateTime($validated['birthdate']);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;

            if ($age < 18) {
                Log::warning('Intento de registro de usuario menor de edad', [
                    'age' => $age,
                    'birthdate' => $validated['birthdate']
                ]);
                
                return back()->withInput()->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'message' => 'Debes ser mayor de 18 años para registrarte.',
                        'duration' => 5000,
                    ]
                ]);
            }

            // Verificar el codigo de invitacion
            Log::debug('Validando código de invitación', ['code' => $validated['invite_code']]);
            
            // Validar el código de invitación
            $inviteCode = CodigoInvitacion::where('codigo', $validated['invite_code'])
                ->where('esta_activo', true)
                ->where('expira_en', '>', Carbon::now())
                ->whereColumn('contador_usos', '<', 'usos_maximos')
                ->first();
            
            if (!$inviteCode) {
                Log::warning('Código de invitación no válido', ['code' => $validated['invite_code']]);
                
                // Determinar el mensaje de error específico
                $errorMessage = 'El código de invitación ingresado no existe.';
                $exists = CodigoInvitacion::where('codigo', $validated['invite_code'])->exists();
                
                if ($exists) {
                    $inviteData = CodigoInvitacion::where('codigo', $validated['invite_code'])->first();
                    if (!$inviteData->esta_activo) {
                        $errorMessage = 'Este código de invitación ha sido desactivado.';
                    } elseif ($inviteData->expira_en <= Carbon::now()) {
                        $errorMessage = 'Este código de invitación ha expirado.';
                    } elseif ($inviteData->contador_usos >= $inviteData->usos_maximos) {
                        $errorMessage = 'Este código de invitación ya ha alcanzado su límite de usos.';
                    }
                }
                
                // Solo devolver el Toast, sin errores en el campo
                return back()
                    ->withInput()
                    ->with('flash', [
                        'toast' => [
                            'type' => 'error',
                            'message' => $errorMessage,
                            'duration' => 5000,
                        ]
                    ]);
            }

            Log::info('Código de invitación válido', [
                'code' => $validated['invite_code'],
                'id' => $inviteCode->id,
                'usos_restantes' => $inviteCode->usos_maximos - $inviteCode->contador_usos
            ]);

            // Crear el usuario
            Log::info('Creando nuevo usuario');
            $user = $this->createUser($validated, $inviteCode);
            Log::info('Usuario creado exitosamente', [
                'user_id' => $user->id,
                'nickname' => $user->apodo,
                'email' => $user->email,
                'codigo_invitacion' => $user->codigo_invitacion,
                'estado' => $user->estado,
                'email_verificado_en' => $user->email_verificado_en
            ]);

            // Crear el perfil del usuario
            Log::debug('Creando perfil para el usuario', ['user_id' => $user->id, 'profile_type' => $validated['profile_type']]);
            $this->createProfile($user, $validated['profile_type']);

            // Marcar el codigo de invitacion como usado
            Log::debug('Marcando código de invitación como usado', [
                'code_id' => $inviteCode->id,
                'user_id' => $user->id
            ]);
            
            $marcado = $this->markInviteCodeAsUsed($inviteCode, $user->id);
            
            if (!$marcado) {
                Log::error('Error al marcar código de invitación como usado', [
                    'code_id' => $inviteCode->id,
                    'user_id' => $user->id
                ]);
                
                // Eliminar el usuario y perfil si no se pudo marcar el codigo
                $user->delete();
                
                return back()->withInput()->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'message' => 'No se pudo completar el registro. Por favor, intenta nuevamente.',
                        'duration' => 5000,
                    ]
                ]);
            }

            // Iniciar sesion automaticamente
            Log::info('Iniciando sesión automática para el nuevo usuario', ['user_id' => $user->id]);
            
            // Cerrar cualquier sesion existente
            Auth::logout();
            
            // Iniciar sesion con el nuevo usuario
            Auth::login($user, true);
            
            // Verificar que la sesion se haya iniciado correctamente
            if (Auth::check()) {
                Log::info('Sesión iniciada correctamente', [
                    'user_id' => Auth::id(),
                    'user_email' => Auth::user()->email
                ]);
            } else {
                Log::error('Error al iniciar sesión automáticamente', [
                    'user_id' => $user->id
                ]);
            }
            
            // Regenerar la sesion por seguridad
            Log::debug('Regenerando sesión');
            $request->session()->regenerate();
            
            // Crear nueva sesion para el usuario
            $request->session()->put('auth.password_confirmed_at', time());

            // Log del registro exitoso
            Log::info('Registro completado exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'nickname' => $user->apodo,
                'estado' => $user->estado,
                'email_verificado_en' => $user->email_verificado_en,
                'codigo_invitacion' => $user->codigo_invitacion,
                'codigo_invitacion_id' => $inviteCode->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId()
            ]);

            // Redireccionar a completar perfil con mensaje de exito
            $toastData = [
                'type' => 'success',
                'message' => '¡Bienvenido a Club de Fantasías! Completa tu perfil para comenzar.',
                'duration' => 5000,
            ];

            Log::debug('Enviando toast notification de éxito', $toastData);

            // Redirigir a la pagina de completar perfil
            return redirect()->route('perfil.completar')
                ->with('flash', ['toast' => $toastData])
                ->with('toast', $toastData);

        } catch (ValidationException $e) {
            Log::warning('Error de validación en el registro', [
                'errors' => $e->errors(),
                'ip' => $request->ip()
            ]);
            
            // Obtener el primer mensaje de error
            $errors = $e->errors();
            $firstError = reset($errors)[0] ?? 'Error de validación';
            $field = key($errors);
            
            // Personalizar mensajes específicos
            $errorMessage = $firstError;
            
            // Si el error es de nickname duplicado
            if ($field === 'nickname' && str_contains($firstError, 'ya está en uso')) {
                $errorMessage = 'El nickname "' . $request->input('nickname') . '" ya está en uso. Por favor, elige otro.';
            }
            
            // Si el error es de email duplicado
            if ($field === 'email' && str_contains($firstError, 'ya está registrado')) {
                $errorMessage = 'El correo "' . $request->input('email') . '" ya está registrado. Por favor, usa otro.';
            }
            
            // Si el error es de código de invitación (validación básica)
            if ($field === 'invite_code') {
                $errorMessage = $firstError;
            }
            
            // Redirigir con solo el Toast, sin errores en los campos
            return back()
                ->withInput()
                ->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'message' => $errorMessage,
                        'duration' => 5000,
                    ]
                ]);
            
        } catch (\Exception $e) {
            Log::error('Error crítico en el registro', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            
            // Redirigir de vuelta con error general y toast
            return back()
                ->withInput()
                ->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'message' => 'Ocurrió un error durante el registro. Por favor, intenta nuevamente.',
                        'duration' => 5000,
                    ]
                ]);
        }
    }

    /**
     * Crea un nuevo usuario con el codigo de invitacion
     */
    protected function createUser(array $data, CodigoInvitacion $inviteCode)
    {
        Log::debug('Creando usuario en la base de datos', [
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'codigo_invitacion' => $data['invite_code']
        ]);

        try {
            $user = User::create([
                'nombre' => $data['nickname'],
                'apodo' => $data['nickname'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'telefono' => $data['phone'] ?? null,
                'ciudad' => $data['city'] ?? null,
                'fecha_nacimiento' => $data['birthdate'],
                'rol' => 'usuario',
                'estado' => 'incompleto', // ✅ Estado incompleto
                'codigo_invitacion' => $data['invite_code'],
                'email_verificado_en' => null, // ✅ Email no verificado
            ]);

            Log::debug('Usuario creado correctamente', [
                'user_id' => $user->id,
                'codigo_invitacion' => $user->codigo_invitacion,
                'estado' => $user->estado,
                'email_verificado_en' => $user->email_verificado_en
            ]);
            
            return $user;
        } catch (\Exception $e) {
            Log::error('Error al crear usuario', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Crea el perfil del usuario
     */
    protected function createProfile(User $user, string $profileType)
    {
        $profileTypeMap = [
            'pareja' => 'pareja',
            'hombre' => 'hombre',
            'mujer' => 'mujer',
            'trans' => 'trans',
            'otro' => 'otro',
        ];

        Log::debug('Creando perfil para usuario', [
            'user_id' => $user->id,
            'profile_type' => $profileType
        ]);

        try {
            $perfil = Perfil::create([
                'usuario_id' => $user->id,
                'tipo' => $profileTypeMap[$profileType] ?? 'otro',
                'biografia' => null,
                'intereses' => null,
                'fotos' => null,
                'preferencias' => null,
            ]);

            Log::debug('Perfil creado correctamente', ['perfil_id' => $perfil->id]);
            return $perfil;
        } catch (\Exception $e) {
            Log::error('Error al crear perfil', [
                'message' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    /**
     * Marca el codigo de invitacion como usado en la tabla codigos_invitacion
     */
    protected function markInviteCodeAsUsed(CodigoInvitacion $inviteCode, int $userId): bool
    {
        Log::debug('Marcando código como usado', [
            'code_id' => $inviteCode->id,
            'user_id' => $userId,
            'codigo' => $inviteCode->codigo
        ]);

        try {
            // Verificar que el codigo es valido antes de marcarlo
            if (!$inviteCode->esValido()) {
                Log::warning('El código no es válido para ser usado', [
                    'code_id' => $inviteCode->id,
                    'esta_activo' => $inviteCode->esta_activo,
                    'expira_en' => $inviteCode->expira_en,
                    'contador_usos' => $inviteCode->contador_usos,
                    'usos_maximos' => $inviteCode->usos_maximos
                ]);
                return false;
            }

            $result = $inviteCode->marcarComoUsado($userId);
            
            if ($result) {
                Log::info('Código marcado como usado exitosamente', [
                    'code_id' => $inviteCode->id,
                    'codigo' => $inviteCode->codigo,
                    'user_id' => $userId,
                    'nuevo_contador' => $inviteCode->fresh()->contador_usos,
                    'usado_en' => $inviteCode->fresh()->usado_en,
                    'esta_activo' => $inviteCode->fresh()->esta_activo
                ]);
            } else {
                Log::error('Error al marcar código como usado - método retorno false', [
                    'code_id' => $inviteCode->id,
                    'user_id' => $userId
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error al marcar código como usado', [
                'message' => $e->getMessage(),
                'code_id' => $inviteCode->id,
                'user_id' => $userId
            ]);
            throw $e;
        }
    }

    /**
     * Verifica si el email ya esta registrado (para validacion en tiempo real)
     */
    public function checkEmail(Request $request)
    {
        Log::debug('Verificando disponibilidad de email', ['email' => $request->email]);
        
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();
        
        Log::debug('Resultado verificación email', [
            'email' => $request->email,
            'available' => !$exists
        ]);

        return response()->json([
            'available' => !$exists,
        ]);
    }

    /**
     * Verifica si el nickname ya esta registrado (para validacion en tiempo real)
     */
    public function checkNickname(Request $request)
    {
        Log::debug('Verificando disponibilidad de nickname', ['nickname' => $request->nickname]);
        
        $request->validate([
            'nickname' => 'required|string|min:3|max:20',
        ]);

        $exists = User::where('apodo', $request->nickname)->exists();
        
        Log::debug('Resultado verificación nickname', [
            'nickname' => $request->nickname,
            'available' => !$exists
        ]);

        return response()->json([
            'available' => !$exists,
        ]);
    }
}