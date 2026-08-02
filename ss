[1mdiff --git a/.env.example b/.env.example[m
[1mindex c0660ea..db89919 100644[m
[1m--- a/.env.example[m
[1m+++ b/.env.example[m
[36m@@ -2,57 +2,38 @@[m [mAPP_NAME=Laravel[m
 APP_ENV=local[m
 APP_KEY=[m
 APP_DEBUG=true[m
[31m-APP_URL=http://localhost[m
[31m-[m
[31m-APP_LOCALE=en[m
[31m-APP_FALLBACK_LOCALE=en[m
[31m-APP_FAKER_LOCALE=en_US[m
[31m-[m
[31m-APP_MAINTENANCE_DRIVER=file[m
[31m-# APP_MAINTENANCE_STORE=database[m
[31m-[m
[31m-# PHP_CLI_SERVER_WORKERS=4[m
[31m-[m
[31m-BCRYPT_ROUNDS=12[m
[32m+[m[32mAPP_URL=http://localhost:8000[m
 [m
 LOG_CHANNEL=stack[m
[31m-LOG_STACK=single[m
 LOG_DEPRECATIONS_CHANNEL=null[m
 LOG_LEVEL=debug[m
 [m
[31m-DB_CONNECTION=sqlite[m
[31m-# DB_HOST=127.0.0.1[m
[31m-# DB_PORT=3306[m
[31m-# DB_DATABASE=laravel[m
[31m-# DB_USERNAME=root[m
[31m-# DB_PASSWORD=[m
[32m+[m[32mDB_CONNECTION=mysql[m
[32m+[m[32mDB_HOST=127.0.0.1[m
[32m+[m[32mDB_PORT=3306[m
[32m+[m[32mDB_DATABASE=mi_base_de_datos[m
[32m+[m[32mDB_USERNAME=root[m
[32m+[m[32mDB_PASSWORD=[m
 [m
[31m-SESSION_DRIVER=database[m
[31m-SESSION_LIFETIME=120[m
[31m-SESSION_ENCRYPT=false[m
[31m-SESSION_PATH=/[m
[31m-SESSION_DOMAIN=null[m
[31m-[m
[31m-BROADCAST_CONNECTION=log[m
[32m+[m[32mBROADCAST_DRIVER=log[m
[32m+[m[32mCACHE_DRIVER=file[m
 FILESYSTEM_DISK=local[m
[31m-QUEUE_CONNECTION=database[m
[31m-[m
[31m-CACHE_STORE=database[m
[31m-# CACHE_PREFIX=[m
[32m+[m[32mQUEUE_CONNECTION=sync[m
[32m+[m[32mSESSION_DRIVER=file[m
[32m+[m[32mSESSION_LIFETIME=120[m
 [m
 MEMCACHED_HOST=127.0.0.1[m
 [m
[31m-REDIS_CLIENT=phpredis[m
 REDIS_HOST=127.0.0.1[m
 REDIS_PASSWORD=null[m
 REDIS_PORT=6379[m
 [m
[31m-MAIL_MAILER=log[m
[31m-MAIL_SCHEME=null[m
[31m-MAIL_HOST=127.0.0.1[m
[31m-MAIL_PORT=2525[m
[32m+[m[32mMAIL_MAILER=smtp[m
[32m+[m[32mMAIL_HOST=mailpit[m
[32m+[m[32mMAIL_PORT=1025[m
 MAIL_USERNAME=null[m
 MAIL_PASSWORD=null[m
[32m+[m[32mMAIL_ENCRYPTION=null[m
 MAIL_FROM_ADDRESS="hello@example.com"[m
 MAIL_FROM_NAME="${APP_NAME}"[m
 [m
[36m@@ -62,4 +43,4 @@[m [mAWS_DEFAULT_REGION=us-east-1[m
 AWS_BUCKET=[m
 AWS_USE_PATH_STYLE_ENDPOINT=false[m
 [m
[31m-VITE_APP_NAME="${APP_NAME}"[m
[32m+[m[32mVITE_APP_NAME="${APP_NAME}"[m
\ No newline at end of file[m
[1mdiff --git a/app/Http/Controllers/Admin/AdminAuthController.php b/app/Http/Controllers/Admin/AdminAuthController.php[m
[1mnew file mode 100644[m
[1mindex 0000000..66034a7[m
[1m--- /dev/null[m
[1m+++ b/app/Http/Controllers/Admin/AdminAuthController.php[m
[36m@@ -0,0 +1,66 @@[m
[32m+[m[32m<?php[m
[32m+[m
[32m+[m[32mnamespace App\Http\Controllers\Admin;[m
[32m+[m
[32m+[m[32muse App\Http\Controllers\Controller;[m
[32m+[m[32muse Illuminate\Http\Request;[m
[32m+[m[32muse Illuminate\Support\Facades\Auth;[m
[32m+[m[32muse Inertia\Inertia;[m
[32m+[m[32muse Inertia\Response;[m
[32m+[m
[32m+[m[32mclass AdminAuthController extends Controller[m
[32m+[m[32m{[m
[32m+[m[32m    /**[m
[32m+[m[32m     * Muestra el formulario de inicio de sesión del panel admin.[m
[32m+[m[32m     */[m
[32m+[m[32m    public function showLogin(): Response[m
[32m+[m[32m    {[m
[32m+[m[32m        return Inertia::render('Admin/Auth/Login');[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    /**[m
[32m+[m[32m     * Procesa el inicio de sesión del administrador.[m
[32m+[m[32m     */[m
[32m+[m[32m    public function login(Request $request)[m
[32m+[m[32m    {[m
[32m+[m[32m        $credentials = $request->validate([[m
[32m+[m[32m            'email' => ['required', 'email'],[m
[32m+[m[32m            'password' => ['required', 'string'],[m
[32m+[m[32m        ]);[m
[32m+[m
[32m+[m[32m        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {[m
[32m+[m[32m            return back()->withErrors([[m
[32m+[m[32m                'email' => 'Las credenciales no coinciden con ningún administrador.',[m
[32m+[m[32m            ])->onlyInput('email');[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        $admin = Auth::guard('admin')->user();[m
[32m+[m
[32m+[m[32m        if (! $admin->estaActivo()) {[m
[32m+[m[32m            Auth::guard('admin')->logout();[m
[32m+[m
[32m+[m[32m            return back()->withErrors([[m
[32m+[m[32m                'email' => 'Esta cuenta de administrador está inactiva o no ha sido verificada.',[m
[32m+[m[32m            ])->onlyInput('email');[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        $request->session()->regenerate();[m
[32m+[m
[32m+[m[32m        $admin->registrarAcceso($request->ip());[m
[32m+[m
[32m+[m[32m        return redirect()->intended(route('admin.dashboard'));[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    /**[m
[32m+[m[32m     * Cierra la sesión del administrador.[m
[32m+[m[32m     */[m
[32m+[m[32m    public function logout(Request $request)[m
[32m+[m[32m    {[m
[32m+[m[32m        Auth::guard('admin')->logout();[m
[32m+[m
[32m+[m[32m        $request->session()->invalidate();[m
[32m+[m[32m        $request->session()->regenerateToken();[m
[32m+[m
[32m+[m[32m        return redirect()->route('admin.login');[m
[32m+[m[32m    }[m
[32m+[m[32m}[m
\ No newline at end of file[m
[1mdiff --git a/app/Http/Controllers/Admin/Cobrocontroller.php b/app/Http/Controllers/Admin/Cobrocontroller.php[m
[1mnew file mode 100644[m
[1mindex 0000000..81ab417[m
[1m--- /dev/null[m
[1m+++ b/app/Http/Controllers/Admin/Cobrocontroller.php[m
[36m@@ -0,0 +1,198 @@[m
[32m+[m[32m<?php[m
[32m+[m
[32m+[m[32mnamespace App\Http\Controllers\Admin;[m
[32m+[m
[32m+[m[32muse App\Http\Controllers\Controller;[m
[32m+[m[32muse App\Models\Transaccion;[m
[32m+[m[32muse Illuminate\Http\Request;[m
[32m+[m[32muse Illuminate\Support\Facades\DB;[m
[32m+[m[32muse Inertia\Inertia;[m
[32m+[m[32muse Inertia\Response;[m
[32m+[m
[32m+[m[32mclass CobroController extends Controller[m
[32m+[m[32m{[m
[32m+[m[32m    public function index(Request $request): Response[m
[32m+[m[32m    {[m
[32m+[m[32m        $inicioMes = now()->startOfMonth();[m
[32m+[m[32m        $inicioMesAnterior = now()->subMonthNoOverflow()->startOfMonth();[m
[32m+[m[32m        $finMesAnterior = now()->subMonthNoOverflow()->endOfMonth();[m
[32m+[m
[32m+[m[32m        // --- KPIs ---[m
[32m+[m[32m        $ingresosTotales = (float) Transaccion::aprobadas()->sum('monto');[m
[32m+[m
[32m+[m[32m        $cobrosDelMes = (float) Transaccion::aprobadas()[m
[32m+[m[32m            ->whereIn('tipo', ['suscripcion', 'compra_contenido', 'propina'])[m
[32m+[m[32m            ->where('created_at', '>=', $inicioMes)[m
[32m+[m[32m            ->sum('monto');[m
[32m+[m
[32m+[m[32m        $cobrosMesAnterior = (float) Transaccion::aprobadas()[m
[32m+[m[32m            ->whereIn('tipo', ['suscripcion', 'compra_contenido', 'propina'])[m
[32m+[m[32m            ->whereBetween('created_at', [$inicioMesAnterior, $finMesAnterior])[m
[32m+[m[32m            ->sum('monto');[m
[32m+[m
[32m+[m[32m        $reembolsosDelMes = (float) Transaccion::where('estado', 'reembolsada')[m
[32m+[m[32m            ->where('created_at', '>=', $inicioMes)[m
[32m+[m[32m            ->sum('monto');[m
[32m+[m
[32m+[m[32m        $pagosPendientesQuery = Transaccion::where('estado', 'pendiente');[m
[32m+[m[32m        $pagosPendientesMonto = (float) (clone $pagosPendientesQuery)->sum('monto');[m
[32m+[m[32m        $pagosPendientesCount = (clone $pagosPendientesQuery)->count();[m
[32m+[m
[32m+[m[32m        // --- Filtros de la tabla ---[m
[32m+[m[32m        $query = Transaccion::with('usuario:id,nombre,apodo');[m
[32m+[m
[32m+[m[32m        if ($tipo = $request->string('tipo')->value()) {[m
[32m+[m[32m            $query->where('tipo', $tipo);[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        if ($desde = $request->date('desde')) {[m
[32m+[m[32m            $query->where('created_at', '>=', $desde->startOfDay());[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        if ($hasta = $request->date('hasta')) {[m
[32m+[m[32m            $query->where('created_at', '<=', $hasta->endOfDay());[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        if ($search = $request->string('q')->trim()->value()) {[m
[32m+[m[32m            $query->whereHas('usuario', function ($q) use ($search) {[m
[32m+[m[32m                $q->where('nombre', 'like', "%{$search}%")[m
[32m+[m[32m                    ->orWhere('apodo', 'like', "%{$search}%");[m
[32m+[m[32m            });[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        $transacciones = $query->latest()->paginate(10)->withQueryString();[m
[32m+[m[32m        $transacciones->through(fn ($t) => [[m
[32m+[m[32m            'id' => $t->id,[m
[32m+[m[32m            'usuario' => $t->usuario,[m
[32m+[m[32m            'tipo' => $t->tipo,[m
[32m+[m[32m            'tipo_nombre' => $t->tipo_nombre,[m
[32m+[m[32m            'monto' => $t->monto,[m
[32m+[m[32m            'estado' => $t->estado,[m
[32m+[m[32m            'estado_nombre' => $t->estado_nombre,[m
[32m+[m[32m            'metodo_pago_nombre' => $t->metodo_pago_nombre,[m
[32m+[m[32m            'es_reembolso' => in_array($t->estado, ['reembolsada']) || $t->tipo === 'retiro',[m
[32m+[m[32m            'created_at' => $t->created_at,[m
[32m+[m[32m        ]);[m
[32m+[m
[32m+[m[32m        // --- Gráfica de ingresos (últimos 30 días) ---[m
[32m+[m[32m        $ingresosPorDia = Transaccion::aprobadas()[m
[32m+[m[32m            ->where('created_at', '>=', now()->subDays(30))[m
[32m+[m[32m            ->selectRaw('DATE(created_at) as fecha, SUM(monto) as total')[m
[32m+[m[32m            ->groupBy('fecha')[m
[32m+[m[32m            ->orderBy('fecha')[m
[32m+[m[32m            ->get()[m
[32m+[m[32m            ->map(fn ($r) => ['fecha' => $r->fecha, 'total' => (float) $r->total]);[m
[32m+[m
[32m+[m[32m        $comisionesDelMes = (float) Transaccion::aprobadas()[m
[32m+[m[32m            ->where('created_at', '>=', $inicioMes)[m
[32m+[m[32m            ->sum('comision');[m
[32m+[m
[32m+[m[32m        // --- Tipos de transacción (para el donut, solo aprobadas) ---[m
[32m+[m[32m        $tiposTotales = Transaccion::aprobadas()[m
[32m+[m[32m            ->selectRaw('tipo, SUM(monto) as total, COUNT(*) as cantidad')[m
[32m+[m[32m            ->groupBy('tipo')[m
[32m+[m[32m            ->get()[m
[32m+[m[32m            ->map(fn ($r) => [[m
[32m+[m[32m                'tipo' => $r->tipo,[m
[32m+[m[32m                'total' => (float) $r->total,[m
[32m+[m[32m                'cantidad' => $r->cantidad,[m
[32m+[m[32m            ]);[m
[32m+[m
[32m+[m[32m        // --- Métodos de pago (solo aprobadas) ---[m
[32m+[m[32m        $totalAprobado = max($ingresosTotales, 0.01); // evita división entre 0[m
[32m+[m[32m        $metodosPago = Transaccion::aprobadas()[m
[32m+[m[32m            ->selectRaw('COALESCE(metodo_pago, "otro") as metodo_pago, SUM(monto) as total')[m
[32m+[m[32m            ->groupBy('metodo_pago')[m
[32m+[m[32m            ->orderByDesc('total')[m
[32m+[m[32m            ->get()[m
[32m+[m[32m            ->map(fn ($r) => [[m
[32m+[m[32m                'metodo' => $r->metodo_pago,[m
[32m+[m[32m                'total' => (float) $r->total,[m
[32m+[m[32m                'porcentaje' => round(($r->total / $totalAprobado) * 100),[m
[32m+[m[32m            ]);[m
[32m+[m
[32m+[m[32m        return Inertia::render('Admin/Cobros/Index', [[m
[32m+[m[32m            'stats' => [[m
[32m+[m[32m                'ingresosTotales' => $ingresosTotales,[m
[32m+[m[32m                'cobrosDelMes' => $cobrosDelMes,[m
[32m+[m[32m                'cobrosVariacion' => $cobrosMesAnterior > 0[m
[32m+[m[32m                    ? round((($cobrosDelMes - $cobrosMesAnterior) / $cobrosMesAnterior) * 100, 1)[m
[32m+[m[32m                    : null,[m
[32m+[m[32m                'reembolsosDelMes' => $reembolsosDelMes,[m
[32m+[m[32m                'comisionesDelMes' => $comisionesDelMes,[m
[32m+[m[32m                'pagosPendientesMonto' => $pagosPendientesMonto,[m
[32m+[m[32m                'pagosPendientesCount' => $pagosPendientesCount,[m
[32m+[m[32m            ],[m
[32m+[m[32m            'transacciones' => $transacciones,[m
[32m+[m[32m            'filtros' => $request->only(['q', 'tipo', 'desde', 'hasta']),[m
[32m+[m[32m            'ingresosPorDia' => $ingresosPorDia,[m
[32m+[m[32m            'tiposTotales' => $tiposTotales,[m
[32m+[m[32m            'metodosPago' => $metodosPago,[m
[32m+[m[32m            'pagosPendientes' => Transaccion::with('usuario:id,nombre,apodo')[m
[32m+[m[32m                ->where('estado', 'pendiente')[m
[32m+[m[32m                ->latest()[m
[32m+[m[32m                ->take(5)[m
[32m+[m[32m                ->get(),[m
[32m+[m[32m        ]);[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    public function exportar(Request $request)[m
[32m+[m[32m    {[m
[32m+[m[32m        $query = Transaccion::with('usuario:id,nombre,apodo');[m
[32m+[m
[32m+[m[32m        if ($tipo = $request->string('tipo')->value()) {[m
[32m+[m[32m            $query->where('tipo', $tipo);[m
[32m+[m[32m        }[m
[32m+[m[32m        if ($desde = $request->date('desde')) {[m
[32m+[m[32m            $query->where('created_at', '>=', $desde->startOfDay());[m
[32m+[m[32m        }[m
[32m+[m[32m        if ($hasta = $request->date('hasta')) {[m
[32m+[m[32m            $query->where('created_at', '<=', $hasta->endOfDay());[m
[32m+[m[32m        }[m
[32m+[m[32m        if ($search = $request->string('q')->trim()->value()) {[m
[32m+[m[32m            $query->whereHas('usuario', fn ($q) => $q->where('nombre', 'like', "%{$search}%")->orWhere('apodo', 'like', "%{$search}%"));[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        $transacciones = $query->latest()->get();[m
[32m+[m
[32m+[m[32m        $filename = 'transacciones_' . now()->format('Y-m-d_His') . '.csv';[m
[32m+[m
[32m+[m[32m        $callback = function () use ($transacciones) {[m
[32m+[m[32m            $handle = fopen('php://output', 'w');[m
[32m+[m[32m            fputcsv($handle, ['ID', 'Usuario', 'Correo', 'Tipo', 'Monto', 'Estado', 'Método de pago', 'Fecha']);[m
[32m+[m
[32m+[m[32m            foreach ($transacciones as $t) {[m
[32m+[m[32m                fputcsv($handle, [[m
[32m+[m[32m                    'TRX-' . str_pad($t->id, 4, '0', STR_PAD_LEFT),[m
[32m+[m[32m                    $t->usuario?->nombre,[m
[32m+[m[32m                    $t->usuario?->apodo,[m
[32m+[m[32m                    $t->tipo_nombre,[m
[32m+[m[32m                    $t->monto,[m
[32m+[m[32m                    $t->estado_nombre,[m
[32m+[m[32m                    $t->metodo_pago_nombre,[m
[32m+[m[32m                    $t->created_at->format('Y-m-d H:i'),[m
[32m+[m[32m                ]);[m
[32m+[m[32m            }[m
[32m+[m
[32m+[m[32m            fclose($handle);[m
[32m+[m[32m        };[m
[32m+[m
[32m+[m[32m        return response()->streamDownload($callback, $filename, [[m
[32m+[m[32m            'Content-Type' => 'text/csv',[m
[32m+[m[32m        ]);[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    public function aprobar(Transaccion $cobro)[m
[32m+[m[32m    {[m
[32m+[m[32m        $cobro->update(['estado' => 'aprobada']);[m
[32m+[m
[32m+[m[32m        return back()->with('success', "Transacción #{$cobro->id} aprobada.");[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    public function reembolsar(Transaccion $cobro)[m
[32m+[m[32m    {[m
[32m+[m[32m        $cobro->update(['estado' => 'reembolsada']);[m
[32m+[m
[32m+[m[32m        return back()->with('success', "Transacción #{$cobro->id} reembolsada.");[m
[32m+[m[32m    }[m
[32m+[m[32m}[m
\ No newline at end of file[m
[1mdiff --git a/app/Http/Controllers/Admin/ContenidoController.php b/app/Http/Controllers/Admin/ContenidoController.php[m
[1mnew file mode 100644[m
[1mindex 0000000..4655a56[m
[1m--- /dev/null[m
[1m+++ b/app/Http/Controllers/Admin/ContenidoController.php[m
[36m@@ -0,0 +1,132 @@[m
[32m+[m[32m<?php[m
[32m+[m
[32m+[m[32mnamespace App\Http\Controllers\Admin;[m
[32m+[m
[32m+[m[32muse App\Http\Controllers\Controller;[m
[32m+[m[32muse App\Models\Contenido;[m
[32m+[m[32muse App\Models\Interaccion;[m
[32m+[m[32muse Illuminate\Http\Request;[m
[32m+[m[32muse Inertia\Inertia;[m
[32m+[m[32muse Inertia\Response;[m
[32m+[m
[32m+[m[32mclass ContenidoController extends Controller[m
[32m+[m[32m{[m
[32m+[m[32m    public function index(Request $request): Response[m
[32m+[m[32m    {[m
[32m+[m[32m        $total = Contenido::count();[m
[32m+[m[32m        $publicados = Contenido::where('estado', 'publicado')->count();[m
[32m+[m[32m        $borradores = Contenido::where('estado', 'borrador')->count();[m
[32m+[m[32m        $archivados = Contenido::where('estado', 'archivado')->count();[m
[32m+[m
[32m+[m[32m        $query = Contenido::query();[m
[32m+[m
[32m+[m[32m        if ($search = $request->string('q')->trim()->value()) {[m
[32m+[m[32m            $query->where(function ($q) use ($search) {[m
[32m+[m[32m                $q->where('titulo', 'like', "%{$search}%")[m
[32m+[m[32m                    ->orWhere('categoria', 'like', "%{$search}%");[m
[32m+[m[32m            });[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        if ($tipo = $request->string('tipo')->value()) {[m
[32m+[m[32m            $query->where('tipo', $tipo);[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        if ($estado = $request->string('estado')->value()) {[m
[32m+[m[32m            $query->where('estado', $estado);[m
[32m+[m[32m        }[m
[32m+[m
[32m+[m[32m        $contenidos = $query->latest()->paginate(7)->withQueryString();[m
[32m+[m[32m        $contenidos->through(fn ($c) => [[m
[32m+[m[32m            'id' => $c->id,[m
[32m+[m[32m            'titulo' => $c->titulo ?: 'Sin título',[m
[32m+[m[32m            'tipo' => $c->tipo,[m
[32m+[m[32m            'categoria' => $c->categoria,[m
[32m+[m[32m            'estado' => $c->estado,[m
[32m+[m[32m            'created_at' => $c->created_at,[m
[32m+[m[32m            'vistas' => $c->interacciones()->where('tipo', 'vista')->count(),[m
[32m+[m[32m            'imagen' => $c->archivos[0] ?? null,[m
[32m+[m[32m        ]);[m
[32m+[m
[32m+[m[32m        $tiposContenido = Contenido::selectRaw('tipo, COUNT(*) as cantidad')->groupBy('tipo')->pluck('cantidad', 'tipo');[m
[32m+[m
[32m+[m[32m        return Inertia::render('Admin/Contenido/Index', [[m
[32m+[m[32m            'stats' => [[m
[32m+[m[32m                'total' => $total,[m
[32m+[m[32m                'nuevosEsteMes' => Contenido::where('created_at', '>=', now()->startOfMonth())->count(),[m
[32m+[m[32m                'publicados' => $publicados,[m
[32m+[m[32m                'borradores' => $borradores,[m
[32m+[m[32m                'archivados' => $archivados,[m
[32m+[m[32m            ],[m
[32m+[m[32m            'contenidos' => $contenidos,[m
[32m+[m[32m            'filtros' => $request->only(['q', 'tipo', 'estado']),[m
[32m+[m[32m            'tiposContenido' => [[m
[32m+[m[32m                'video' => $tiposContenido['video'] ?? 0,[m
[32m+[m[32m                'articulo' => $tiposContenido['articulo'] ?? 0,[m
[32m+[m[32m                'galeria' => $tiposContenido['galeria'] ?? 0,[m
[32m+[m[32m                'audio' => $tiposContenido['audio'] ?? 0,[m
[32m+[m[32m                'documento' => $tiposContenido['documento'] ?? 0,[m
[32m+[m[32m            ],[m
[32m+[m[32m            'contenidoReciente' => Contenido::latest()->take(4)->get()->map(fn ($c) => [[m
[32m+[m[32m                'id' => $c->id,[m
[32m+[m[32m                'titulo' => $c->titulo ?: 'Sin título',[m
[32m+[m[32m                'tipo' => $c->tipo,[m
[32m+[m[32m                'estado' => $c->estado,[m
[32m+[m[32m                'created_at' => $c->created_at,[m
[32m+[m[32m                'imagen' => $c->archivos[0] ?? null,[m
[32m+[m[32m            ]),[m
[32m+[m[32m            'estadisticas' => [[m
[32m+[m[32m                'vistasPorDia' => Interaccion::where('tipo', 'vista')[m
[32m+[m[32m                    ->where('created_at', '>=', now()->subDays(30))[m
[32m+[m[32m                    ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total')[m
[32m+[m[32m                    ->groupBy('fecha')->orderBy('fecha')->get()[m
[32m+[m[32m                    ->map(fn ($r) => ['fecha' => $r->fecha, 'total' => (int) $r->total]),[m
[32m+[m[32m                'vistasTotales' => Interaccion::where('tipo', 'vista')->count(),[m
[32m+[m[32m                'usuariosUnicos' => Interaccion::where('tipo', 'vista')->distinct('usuario_id')->count('usuario_id'),[m
[32m+[m[32m                'interaccionesTotales' => Interaccion::whereIn('tipo', ['like', 'comentario', 'compartir'])->count(),[m
[32m+[m[32m            ],[m
[32m+[m[32m        ]);[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    public function create(Request $request): Response[m
[32m+[m[32m    {[m
[32m+[m[32m        return Inertia::render('Admin/Contenido/Create', [[m
[32m+[m[32m            'tipoPreseleccionado' => $request->query('tipo'),[m
[32m+[m[32m        ]);[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    public function store(Request $request)[m
[32m+[m[32m    {[m
[32m+[m[32m        $data = $request->validate([[m
[32m+[m[32m            'titulo' => ['required', 'string', 'max:255'],[m
[32m+[m[32m            'categoria' => ['nullable', 'string', 'max:255'],[m
[32m+[m[32m            'descripcion' => ['nullable', 'string'],[m
[32m+[m[32m            'tipo' => ['required', 'in:foto,video,galeria,audio,articulo,documento,exclusivo'],[m
[32m+[m[32m            'visibilidad' => ['required', 'in:publico,suscriptores,individual'],[m
[32m+[m[32m            'estado' => ['required', 'in:borrador,publicado,programado,archivado'],[m
[32m+[m[32m            'precio' => ['required', 'numeric', 'min:0'],[m
[32m+[m[32m            'es_premium' => ['boolean'],[m
[32m+[m[32m            'url_archivo' => ['nullable', 'string', 'max:2048'],[m
[32m+[m[32m        ]);[m
[32m+[m
[32m+[m[32m        $archivos = $data['url_archivo'] ? [$data['url_archivo']] : [];[m
[32m+[m[32m        unset($data['url_archivo']);[m
[32m+[m
[32m+[m[32m        $contenido = Contenido::create($data + [[m
[32m+[m[32m            'creador_id' => null,[m
[32m+[m[32m            'archivos' => $archivos,[m
[32m+[m[32m        ]);[m
[32m+[m
[32m+[m[32m        return redirect()->route('admin.contenido.index')->with('success', "Contenido \"{$contenido->titulo}\" creado correctamente.");[m
[32m+[m[32m    }[m
[32m+[m
[32m+[m[32m    public function show(Contenido $contenido): Response[m
[32m+[m[32m    {[m
[32m+[m[32m        $contenido->vistas = $contenido->interacciones()->where('tipo', 'vista')->count();[m
[32m+[m[32m        $contenido->likes = $contenido->interacciones()->where('tipo', 'like')->count();[m
[32m+[m[32m        $contenido->comentarios = $contenido->interacciones()->where('tipo', 'comentario')->count();[m
[32m+[m
[32m+[m[32m        return Inertia::render('Admin/Contenido/Show', [[m
[32m+[m[32m            'contenido' => $contenido,[m
[32m+[m[32m        ]);[m
[32m+[m[32m    }[m
[32m+[m[32m}[m
\ No newline at end of file[m
[1mdiff --git a/app/Http/Controllers/Admin/DashboardController.php b/app/Http/Controllers/Admin/DashboardController.php[m
[1mnew file mode 100644[m
[1mindex 0000000..b677bc8[m
[1m--- /dev/null[m
[1m+++ b/app/Http/Controllers/Admin/DashboardController.php[m
[36m@@ -0,0 +1,190 @@[m
[32m+[m[32m<?php[m
[32m+[m
[32m+[m[32mnamespace App\Http\Controllers\Admin;[m
[32m+[m
[32m+[m[32muse App\Http\Controllers\Controller;[m
[32m+[m[32muse Illuminate\Http\Request;[m
[32m+[m[32muse App\Models\Evento;[m
[32m+[m[32muse App\Models\Pedido;[m
[32m+[m[32muse App\Models\Suscripcion;[m
[32m+[m[32muse App\Models\Transaccion;[m
[32m+[m[32muse App\Models\User;[m
[32m+[m[32muse Inertia\Inertia;[m
[32m+[m[32muse Inertia\Response;[m
[32m+[m
[32m+[m[32mclass DashboardController extends Controller[m
[32m+[m[32m{[m
[32m+[m[32m    public function index(Request $request): Response[m
[32m+[m[32m    {[m
[32m+[m[32m        $hoy = now()->startOfDay();[m
[32m+[m
[32m+[m[32m        // Usuarios recientes (se reutiliza en dos secciones)[m
[32m+[m[32m        $usuariosRecientes = User::latest()[m
[32m+[m[32m            ->take(5)[m
[32m+[m[32m            ->get([[m
[32m+[m[32m                'id',[m
[32m+[m[32m                'nombre',[m
[32m+[m[32m                'apodo',[m
[32m+[m[32m                'email',[m
[32m+[m[32m                'rol',[m
[32m+[m[32m                'estado',[m
[32m+[m[32m                'created_at',[m
[32m+[m[32m            ]);[m
[32m+[m
[32m+[m[32m        return Inertia::render('Admin/Dashboard', [[m
[32m+[m
[32m+[m[32m            'stats' => [[m
[32m+[m[32m                'usuariosTotales' => User::count(),[m
[32m+[m[32m                'usuariosNuevosHoy' => User::where('created_at', '>=', $hoy)->count(),[m
[32m+[m
[32m+[m[32m                'ingresosTotales' => (float) Transaccion::where('estado', 'aprobada')->sum('monto'),[m
[32m+[m[32m                'ingresosHoy' => (float) Transaccion::where('estado', 'aprobada')[m
[32m+[m[32m                    ->whereDate('created_at', today())[m
[32m+[m[32m                    ->sum('monto'),[m
[32m+[m
[32m+[m[32m                'suscripcionesActivas' => Suscripcion::activas()->count(),[m
[32m+[m[32m                'suscripcionesNuevasHoy' => Suscripcion::activas()[m
[32m+[m[32m                    ->where('created_at', '>=', $hoy)[m
[32m+[m[32m                    ->count(),[m
[32m+[m
[32m+[m[32m                'ventasShop' => (float) Pedido::pagados()->sum('total'),[m
[32m+[m[32m                'ventasHoy' => (float) Pedido::pagados()[m
[32m+[m[32m                    ->whereDate('created_at', today())[m
[32m+[m[32m                    ->sum('total'),[m
[32m+[m
[32m+[m[32m                'eventosActivos' => Evento::where('estado', 'activo')->count(),[m
[32m+[m[32m                'eventosProximos' => Evento::where('fecha', '>=', now())->count(),[m
[32m+[m[32m            ],[m
[32m+[m
[32m+[m[32m            'usuariosRecientes' => $usuariosRecientes,[m
[32m+[m
[32m+[m[32m            'gestionUsuarios' => (function () use ($request) {[m
[32m+[m[32m                $query = User::query();[m
[32m+[m
[32m+[m[32m                if ($search = $request->string('q')->trim()->value()) {[m
[32m+[m[32m                    $query->where(function ($q) use ($search) {[m
[32m+[m[32m                        $q->where('nombre', 'like', "%{$search}%")[m
[32m+[m[32m                            ->orWhere('apodo', 'like', "%{$search}%")[m
[32m+[m[32m                            ->orWhere('email', 'like', "%{$search}%");[m
[32m+[m[32m                    });[m
[32m+[m[32m                }[m
[32m+[m
[32m+[m[32m                if ($rol = $request->string('rol')->value()) {[m
[32m+[m[32m                    $query->where('rol', $rol);[m
[32m+[m[32m                }[m
[32m+[m
[32m+[m[32m                if ($estado = $request->string('estado')->value()) {[m
[32m+[m[32m                    $query->where('estado', $estado);[m
[32m+[m[32m                }[m
[32m+[m
[32m+[m[32m                return $query->latest()->take(5)->get([[m
[32m+[m[32m                    'id', 'nombre', 'apodo', 'email', 'rol', 'estado', 'created_at',[m
[32m+[m[32m    