<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Illuminate\Support\Str;

class InviteRegisterController extends Controller
{
    /**
     * Mostrar el formulario de registro por invitación.
     */
    public function showRegistrationForm(Request $request)
    {
        // Si viene con código de invitación en la URL, lo pasamos al frontend
        $inviteCode = $request->query('code');

        return Inertia::render('Auth/RegisterInvite', [
            'invite_code' => $inviteCode,
        ]);
    }

    /**
     * Registrar un nuevo usuario con código de invitación.
     */
    public function register(Request $request)
    {
        // 🔥 VALIDACIÓN
        $validator = Validator::make($request->all(), [
            'invite_code' => 'required|string|max:50|exists:invitaciones,codigo',
            'profile_type' => 'required|in:pareja,hombre,mujer,trans,otro',
            'nickname' => 'required|string|max:50|unique:users,apodo|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date|before:today',
            'accepts_terms' => 'required|accepted',
        ], [
            'invite_code.required' => 'El código de invitación es obligatorio.',
            'invite_code.exists' => 'El código de invitación no es válido o ya fue utilizado.',
            'nickname.required' => 'El nickname es obligatorio.',
            'nickname.unique' => 'Este nickname ya está en uso.',
            'nickname.regex' => 'El nickname solo puede contener letras, números y guiones bajos.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'accepts_terms.required' => 'Debes aceptar los términos y condiciones.',
            'accepts_terms.accepted' => 'Debes aceptar los términos y condiciones para continuar.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // 🔥 VERIFICAR QUE EL CÓDIGO DE INVITACIÓN ES VÁLIDO
        $invitacion = \App\Models\Invitacion::where('codigo', $request->invite_code)
            ->where('usado', false)
            ->where('fecha_expiracion', '>', now())
            ->first();

        if (!$invitacion) {
            return back()
                ->withErrors(['invite_code' => 'El código de invitación no es válido, ya fue utilizado o ha expirado.'])
                ->withInput();
        }

        // 🔥 CREAR EL USUARIO
        $user = User::create([
            'nombre' => $request->nickname, // Usamos el nickname como nombre inicial
            'apodo' => $request->nickname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->phone,
            'ciudad' => $request->city,
            'fecha_nacimiento' => $request->birthdate,
            'rol' => 'usuario',
            'estado' => 'pendiente',
            'codigo_invitacion' => $request->invite_code,
        ]);

        // 🔥 CREAR EL PERFIL DEL USUARIO
        $profileTypeMap = [
            'pareja' => 'Pareja',
            'hombre' => 'Hombre',
            'mujer' => 'Mujer',
            'trans' => 'Trans',
            'otro' => 'Otro',
        ];

        Perfil::create([
            'usuario_id' => $user->id,
            'tipo_perfil' => $profileTypeMap[$request->profile_type] ?? 'Otro',
            'bio' => null,
            'intereses' => null,
            'fotos' => null,
            'ubicacion' => $request->city,
            'configuracion' => json_encode([
                'notificaciones' => true,
                'privacidad' => 'publico',
            ]),
        ]);

        // 🔥 MARCAR EL CÓDIGO DE INVITACIÓN COMO USADO
        $invitacion->update([
            'usado' => true,
            'usado_por' => $user->id,
            'fecha_uso' => now(),
        ]);

        // 🔥 LOGIN AUTOMÁTICO (opcional)
        // auth()->login($user);

        // 🔥 REDIRIGIR CON MENSAJE DE ÉXITO
        return redirect()
            ->route('login')
            ->with('success', '¡Cuenta creada exitosamente! Ahora puedes iniciar sesión con tus credenciales.');
    }
}