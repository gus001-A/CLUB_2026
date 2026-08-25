<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificacionCorreoMail;
use App\Models\User;
use App\Models\Administrador;
use App\Support\CodigoVerificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LoginController extends Controller
{
    /**
     * Muestra el formulario de login unificado
     */
    public function showLoginForm()
    {
        $pendienteId = session('login_pendiente_user_id');
        $modoVerificacion = false;
        $emailPendiente = null;

        if ($pendienteId) {
            $usuario = User::find($pendienteId);

            if ($usuario && !$usuario->email_verificado_en) {
                $modoVerificacion = true;
                $emailPendiente = $usuario->email;
            } else {
                // El usuario ya se verificó por otro medio, o ya no existe
                // — no lo dejamos atorado en modo verificación para siempre.
                session()->forget(['login_pendiente_user_id', 'login_pendiente_remember']);
            }
        }

        return Inertia::render('Auth/Login', [
            'modoVerificacion' => $modoVerificacion,
            'emailPendiente' => $emailPendiente,
        ]);
    }

    /**
     * Procesa el inicio de sesión (tanto para usuarios como administradores)
     */
    public function login(Request $request)
    {
        // Validación de los campos
        $request->validate([
            'nickname' => 'required|string|max:50',
            'password' => 'required|string|min:6',
            'remember' => 'boolean',
        ], [
            'nickname.required' => 'El nickname es obligatorio',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ]);

        // PRIMERO: Buscar en administradores (por nickname o email)
        $admin = Administrador::where('nickname', $request->nickname)
            ->orWhere('email', $request->nickname)
            ->first();
        
        // Si existe administrador y la contraseña es correcta
        if ($admin && Hash::check($request->password, $admin->password)) {
            return $this->loginAdmin($admin, $request);
        }

        // SEGUNDO: Buscar en usuarios normales (por apodo o email)
        $user = User::where('apodo', $request->nickname)
            ->orWhere('email', $request->nickname)
            ->first();

        // Si existe usuario normal y la contraseña es correcta
        if ($user && Hash::check($request->password, $user->password)) {
            return $this->loginUser($user, $request);
        }

        // Si llegamos aquí, las credenciales son incorrectas
        return back()->withErrors([
            'login' => 'Nickname y/o contraseña incorrecta'
        ])->onlyInput('nickname');
    }

    /**
     * Login para administradores
     */
    private function loginAdmin($admin, Request $request)
    {
        // Verificar si el admin está activo
        if (!$admin->esta_activo) {
            return back()->withErrors([
                'login' => 'Esta cuenta de administrador está inactiva.'
            ])->onlyInput('nickname');
        }

        // Iniciar sesión como administrador
        Auth::guard('admin')->login($admin, $request->remember ?? false);

        $request->session()->regenerate();

        // Actualizar último acceso
        $admin->ultimo_acceso_en = now();
        $admin->save();

        // Redirigir al dashboard de admin
        return redirect()->route('admin.dashboard')
            ->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => '¡Bienvenido Administrador!',
                    'message' => 'Has iniciado sesión correctamente, ' . ($admin->nombre ?? $admin->nickname ?? $admin->email) . '.',
                    'duration' => 5000,
                ]
            ]);
    }

    /**
     * Login para usuarios normales
     */
    private function loginUser($user, Request $request)
    {
        // OJO: esto comparaba contra 'inactivo'/'suspendido', pero tu enum
        // real de 'estado' es pendiente/verificado/incompleto/bloqueado —
        // esos valores nunca existen, así que esta condición nunca se
        // cumplía y un usuario bloqueado sí podía entrar. Corregido para
        // usar el valor real.
        if ($user->estado === 'bloqueado') {
            return back()->withErrors([
                'login' => 'Tu cuenta está bloqueada. Contacta al soporte.'
            ])->onlyInput('nickname');
        }

        // Si su correo aún no está verificado (pasa con cuentas que un
        // admin crea directo desde el panel, o si algo interrumpió la
        // verificación de un registro por invitación), no lo dejamos
        // entrar todavía — primero confirma el código de 6 dígitos.
        if (!$user->email_verificado_en) {
            return $this->iniciarVerificacionLogin($user, $request);
        }

        try {
            // Intentar login con guard web
            Auth::guard('web')->login($user, $request->remember ?? false);
            
            // Verificar que el login fue exitoso
            if (!Auth::guard('web')->check()) {
                return back()->withErrors([
                    'login' => 'Error al iniciar sesión. Por favor intenta de nuevo.'
                ])->onlyInput('nickname');
            }

            // Guardar el perfil en la sesión para acceso rápido
            if ($user->perfil) {
                session()->put('user_perfil', $user->perfil);
            }

            $request->session()->regenerate();
            $request->session()->put('auth.password_confirmed_at', time());

            // Redirigir a inicio
            return redirect()->intended(route('inicio'))
                ->with('flash', [
                    'toast' => [
                        'type' => 'success',
                        'title' => '¡Bienvenido de nuevo!',
                        'message' => 'Has iniciado sesión correctamente, ' . ($user->nombre ?? $user->apodo) . '.',
                        'duration' => 5000,
                    ]
                ]);

        } catch (\Exception $e) {
            return back()->withErrors([
                'login' => 'Error al iniciar sesión: ' . $e->getMessage()
            ])->onlyInput('nickname');
        }
    }

    /**
     * Manda (o reenvía, si acaban de pedirlo por otro lado) el código de
     * verificación y deja al usuario "en espera" en sesión — todavía NO
     * inicia sesión, eso solo pasa si el código resulta correcto.
     */
    private function iniciarVerificacionLogin(User $user, Request $request)
    {
        // Si ya hay un código vigente (por ejemplo el que va en el correo
        // de bienvenida cuando un admin crea la cuenta), NO lo pisamos con
        // uno nuevo solo porque están intentando entrar — eso invalidaba
        // el código que la persona ya tenía en su correo sin haber tenido
        // chance de usarlo. Solo mandamos uno nuevo si de verdad no hay
        // ninguno vigente todavía.
        if (!CodigoVerificacion::existeVigente($user->email) && CodigoVerificacion::puedeReenviar($user->email)) {
            $codigo = CodigoVerificacion::generar($user->email);
            CodigoVerificacion::marcarEnviado($user->email);

            try {
                Mail::to($user->email)->send(new VerificacionCorreoMail($codigo));
            } catch (\Throwable $e) {
                Log::error('No se pudo enviar el código de verificación de login', [
                    'usuario_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        session([
            'login_pendiente_user_id' => $user->id,
            'login_pendiente_remember' => $request->boolean('remember'),
        ]);

        return redirect()->route('login');
    }

    /**
     * Confirma el código de 6 dígitos y, si es correcto, ahí sí inicia
     * sesión de verdad (equivalente a lo que hacía loginUser() antes de
     * este cambio).
     */
    public function verificarCodigoLogin(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'string', 'size:6'],
        ], [
            'codigo.required' => 'Ingresa el código que te mandamos por correo',
            'codigo.size' => 'El código debe tener 6 dígitos',
        ]);

        $userId = session('login_pendiente_user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'login' => 'Tu sesión de verificación expiró. Inicia sesión de nuevo.',
            ]);
        }

        if (!CodigoVerificacion::valido($user->email, $request->codigo)) {
            return back()->withErrors([
                'codigo' => 'El código es incorrecto o ya expiró.',
            ]);
        }

        CodigoVerificacion::olvidar($user->email);

        $user->email_verificado_en = now();
        $user->save();

        $remember = session('login_pendiente_remember', false);
        session()->forget(['login_pendiente_user_id', 'login_pendiente_remember']);

        Auth::guard('web')->login($user, $remember);

        if ($user->perfil) {
            session()->put('user_perfil', $user->perfil);
        }

        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('inicio'))
            ->with('flash', [
                'toast' => [
                    'type' => 'success',
                    'title' => '¡Bienvenido!',
                    'message' => 'Correo verificado. Has iniciado sesión correctamente, ' . ($user->nombre ?? $user->apodo) . '.',
                    'duration' => 5000,
                ]
            ]);
    }

    /**
     * Reenvía el código (con el mismo throttle de 45s que ya usa el
     * registro por invitación).
     */
    public function reenviarCodigoLogin(Request $request)
    {
        $userId = session('login_pendiente_user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'login' => 'Tu sesión de verificación expiró. Inicia sesión de nuevo.',
            ]);
        }

        if (!CodigoVerificacion::puedeReenviar($user->email)) {
            return back()->withErrors([
                'codigo' => 'Espera unos segundos antes de pedir otro código.',
            ]);
        }

        $codigo = CodigoVerificacion::generar($user->email);
        CodigoVerificacion::marcarEnviado($user->email);

        try {
            Mail::to($user->email)->send(new VerificacionCorreoMail($codigo));
        } catch (\Throwable $e) {
            Log::error('No se pudo reenviar el código de verificación de login', [
                'usuario_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'codigo' => 'No se pudo enviar el código. Intenta de nuevo.',
            ]);
        }

        return back()->with('flash', [
            'toast' => [
                'type' => 'success',
                'message' => "Te enviamos un nuevo código a {$user->email}.",
                'duration' => 4000,
            ],
        ]);
    }

    /**
     * "Ya no quiero verificar ahorita" — regresa al login normal, limpio.
     */
    public function cancelarVerificacionLogin(Request $request)
    {
        session()->forget(['login_pendiente_user_id', 'login_pendiente_remember']);

        return redirect()->route('login');
    }

    /**
     * Cierra la sesión (tanto para usuarios como administradores)
     */
    public function logout(Request $request)
    {
        // Limpiar sesión de perfil
        session()->forget('user_perfil');

        // Cerrar sesión del guard que esté activo
        if (Auth::check()) {
            Auth::logout();
        }
        
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('flash', [
                'toast' => [
                    'type' => 'info',
                    'title' => 'Sesión cerrada',
                    'message' => 'Has cerrado sesión correctamente.',
                    'duration' => 3000,
                ]
            ]);
    }
}