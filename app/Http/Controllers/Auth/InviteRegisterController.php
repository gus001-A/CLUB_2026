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
                'invite_code.required' => 'El codigo de invitacion es obligatorio',
                'invite_code.min' => 'El codigo debe tener al menos 8 caracteres',
                'profile_type.required' => 'Debes seleccionar un tipo de perfil',
                'profile_type.in' => 'Tipo de perfil no valido',
                'nickname.required' => 'El nickname es obligatorio',
                'nickname.min' => 'El nickname debe tener al menos 3 caracteres',
                'nickname.max' => 'El nickname no puede tener mas de 20 caracteres',
                'nickname.regex' => 'El nickname solo puede contener letras, numeros y guiones bajos',
                'nickname.unique' => 'Este nickname ya esta en uso',
                'email.required' => 'El correo electronico es obligatorio',
                'email.email' => 'Ingresa un correo electronico valido',
                'email.unique' => 'Este correo electronico ya esta registrado',
                'password.required' => 'La contrasena es obligatoria',
                'password.min' => 'La contrasena debe tener al menos 8 caracteres',
                'password.confirmed' => 'Las contrasenas no coinciden',
                'birthdate.required' => 'La fecha de nacimiento es obligatoria',
                'birthdate.before' => 'La fecha de nacimiento debe ser anterior a hoy',
                'birthdate.after' => 'Por favor, ingresa una fecha valida',
                'accepts_terms.required' => 'Debes aceptar los terminos y condiciones',
                'accepts_terms.accepted' => 'Debes aceptar los terminos y condiciones',
            ]);

            Log::info('Validacion exitosa', [
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
                
                return back()->withErrors([
                    'birthdate' => 'Debes ser mayor de 18 años para registrarte.'
                ])->withInput()->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'title' => 'Error de edad',
                        'message' => 'Debes ser mayor de 18 años para registrarte.',
                        'duration' => 5000,
                    ]
                ]);
            }

            // Verificar el codigo de invitacion
            Log::debug('Validando codigo de invitacion', ['code' => $validated['invite_code']]);
            $inviteCode = $this->validateInviteCode($validated['invite_code']);

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
            Log::debug('Marcando codigo de invitacion como usado', [
                'code_id' => $inviteCode->id,
                'user_id' => $user->id
            ]);
            
            $marcado = $this->markInviteCodeAsUsed($inviteCode, $user->id);
            
            if (!$marcado) {
                Log::error('Error al marcar codigo de invitacion como usado', [
                    'code_id' => $inviteCode->id,
                    'user_id' => $user->id
                ]);
                
                // Eliminar el usuario y perfil si no se pudo marcar el codigo
                $user->delete();
                
                return back()->withInput()->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'title' => 'Error en el registro',
                        'message' => 'No se pudo completar el registro. Por favor, intenta nuevamente.',
                        'duration' => 5000,
                    ]
                ]);
            }

            // Iniciar sesion automaticamente
            Log::info('Iniciando sesion automatica para el nuevo usuario', ['user_id' => $user->id]);
            
            // Cerrar cualquier sesion existente
            Auth::logout();
            
            // Iniciar sesion con el nuevo usuario
            Auth::login($user, true);
            
            // Verificar que la sesion se haya iniciado correctamente
            if (Auth::check()) {
                Log::info('Sesion iniciada correctamente', [
                    'user_id' => Auth::id(),
                    'user_email' => Auth::user()->email
                ]);
            } else {
                Log::error('Error al iniciar sesion automaticamente', [
                    'user_id' => $user->id
                ]);
            }
            
            // Regenerar la sesion por seguridad
            Log::debug('Regenerando sesion');
            $request->session()->regenerate();
            
            // Crear nueva sesion para el usuario
            $request->session()->put('auth.password_confirmed_at', time());

            // Log del registro exitoso
            Log::info('Registro completado exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'nickname' => $user->apodo,
                'estado' => $user->estado,
                'codigo_invitacion' => $user->codigo_invitacion,
                'codigo_invitacion_id' => $inviteCode->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->session()->getId()
            ]);

            // Redireccionar a completar perfil con mensaje de exito
            $toastData = [
                'type' => 'success',
                'title' => '¡Registro exitoso!',
                'message' => 'Bienvenido a Club de Fantasías. Completa tu perfil para comenzar.',
                'duration' => 5000,
            ];

            Log::debug('Enviando toast notification de exito', $toastData);

            // Redirigir a la pagina de completar perfil
            return redirect()->route('perfil.completar')
                ->with('flash', ['toast' => $toastData])
                ->with('toast', $toastData);

        } catch (ValidationException $e) {
            Log::warning('Error de validacion en el registro', [
                'errors' => $e->errors(),
                'ip' => $request->ip()
            ]);
            
            // Obtener el primer mensaje de error
            $errors = $e->errors();
            $firstError = reset($errors)[0] ?? 'Error de validacion';
            $field = key($errors);
            
            // Mapeo de campos para mensajes mas amigables
            $fieldNames = [
                'invite_code' => 'codigo de invitacion',
                'profile_type' => 'tipo de perfil',
                'nickname' => 'nickname',
                'email' => 'correo electronico',
                'password' => 'contrasena',
                'password_confirmation' => 'confirmacion de contrasena',
                'city' => 'ciudad',
                'phone' => 'telefono',
                'birthdate' => 'fecha de nacimiento',
                'accepts_terms' => 'terminos y condiciones'
            ];
            
            $fieldName = $fieldNames[$field] ?? $field;
            
            // Mensaje especifico para cada tipo de error
            $errorMessage = $firstError;
            
            // Si el error es de nickname duplicado
            if ($field === 'nickname' && str_contains($firstError, 'ya esta en uso')) {
                $nickname = $request->input('nickname');
                $errorMessage = 'El nickname "' . $nickname . '" ya esta en uso. Por favor, elige otro.';
            }
            
            // Si el error es de email duplicado
            if ($field === 'email' && str_contains($firstError, 'ya esta registrado')) {
                $email = $request->input('email');
                $errorMessage = 'El correo "' . $email . '" ya esta registrado. Por favor, usa otro.';
            }
            
            // Redirigir de vuelta con errores y toast
            return back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('flash', [
                    'toast' => [
                        'type' => 'error',
                        'title' => 'Error en el registro',
                        'message' => $errorMessage,
                        'duration' => 5000,
                    ]
                ]);
            
        } catch (\Exception $e) {
            Log::error('Error critico en el registro', [
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
                        'title' => 'Error inesperado',
                        'message' => 'Ocurrio un error durante el registro. Por favor, intenta nuevamente.',
                        'duration' => 5000,
                    ]
                ]);
        }
    }

    /**
     * Valida el codigo de invitacion
     */
    protected function validateInviteCode(string $code)
    {
        Log::debug('Buscando codigo de invitacion', ['code' => $code]);

        // Buscar el codigo en la tabla de codigos de invitacion
        $invite = CodigoInvitacion::where('codigo', $code)
            ->where('esta_activo', true)
            ->where('expira_en', '>', Carbon::now())
            ->whereColumn('contador_usos', '<', 'usos_maximos')
            ->first();
        
        if (!$invite) {
            Log::warning('Codigo de invitacion no valido', ['code' => $code]);
            
            // Mensajes de error mas descriptivos
            $errorMessage = 'El codigo de invitacion no es valido.';
            
            // Verificar si el codigo existe pero esta inactivo
            $exists = CodigoInvitacion::where('codigo', $code)->exists();
            if ($exists) {
                $inviteData = CodigoInvitacion::where('codigo', $code)->first();
                Log::debug('Codigo encontrado pero no valido', [
                    'code' => $code,
                    'esta_activo' => $inviteData->esta_activo,
                    'expira_en' => $inviteData->expira_en,
                    'contador_usos' => $inviteData->contador_usos,
                    'usos_maximos' => $inviteData->usos_maximos
                ]);
                
                if (!$inviteData->esta_activo) {
                    $errorMessage = 'Este codigo de invitacion ha sido desactivado.';
                } elseif ($inviteData->expira_en <= Carbon::now()) {
                    $errorMessage = 'Este codigo de invitacion ha expirado.';
                } elseif ($inviteData->contador_usos >= $inviteData->usos_maximos) {
                    $errorMessage = 'Este codigo de invitacion ya ha alcanzado su limite de usos.';
                }
            } else {
                Log::warning('Codigo de invitacion no existe en la base de datos', ['code' => $code]);
                $errorMessage = 'El codigo de invitacion ingresado no existe.';
            }
            
            throw ValidationException::withMessages([
                'invite_code' => $errorMessage,
            ]);
        }

        Log::info('Codigo de invitacion valido', [
            'code' => $code,
            'id' => $invite->id,
            'usos_restantes' => $invite->usos_maximos - $invite->contador_usos,
            'expira_en' => $invite->expira_en,
            'usos_maximos' => $invite->usos_maximos
        ]);
        
        return $invite;
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
                'estado' => 'incompleto', // CAMBIADO: estado incompleto
                'codigo_invitacion' => $data['invite_code'],
                'email_verificado_en' => null, // CAMBIADO: null hasta que verifique email
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
        Log::debug('Marcando codigo como usado', [
            'code_id' => $inviteCode->id,
            'user_id' => $userId,
            'codigo' => $inviteCode->codigo
        ]);

        try {
            // Verificar que el codigo es valido antes de marcarlo
            if (!$inviteCode->esValido()) {
                Log::warning('El codigo no es valido para ser usado', [
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
                Log::info('Codigo marcado como usado exitosamente', [
                    'code_id' => $inviteCode->id,
                    'codigo' => $inviteCode->codigo,
                    'user_id' => $userId,
                    'nuevo_contador' => $inviteCode->fresh()->contador_usos,
                    'usado_en' => $inviteCode->fresh()->usado_en,
                    'esta_activo' => $inviteCode->fresh()->esta_activo
                ]);
            } else {
                Log::error('Error al marcar codigo como usado - metodo retorno false', [
                    'code_id' => $inviteCode->id,
                    'user_id' => $userId
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Error al marcar codigo como usado', [
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
        
        Log::debug('Resultado verificacion email', [
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
        
        Log::debug('Resultado verificacion nickname', [
            'nickname' => $request->nickname,
            'available' => !$exists
        ]);

        return response()->json([
            'available' => !$exists,
        ]);
    }
}