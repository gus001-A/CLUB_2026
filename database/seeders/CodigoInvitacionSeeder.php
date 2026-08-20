<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CodigoInvitacion;
use App\Models\User;
use App\Models\Administrador;
use Carbon\Carbon;

class CodigoInvitacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan usuarios y administradores
        $admin = Administrador::first();
        $usuario = User::first();

        if (!$admin) {
            $this->command->error('❌ No hay administradores. Ejecuta primero el seeder de administradores.');
            return;
        }

        // ============================================================
        // 1. CÓDIGO VÁLIDO Y DISPONIBLE (Para pruebas)
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-TEST-001',
            'email' => 'test@example.com',
            'nombre_destinatario' => 'Usuario Test',
            'expira_en' => Carbon::now()->addDays(30),
            'usos_maximos' => 1,
            'contador_usos' => 0,
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'notas' => 'Código de prueba para testing',
            'metadata' => ['tipo' => 'test', 'version' => '1.0'],
        ]);

        // ============================================================
        // 2. CÓDIGO CON MÚLTIPLES USOS
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-MULTI-001',
            'email' => 'multi@example.com',
            'nombre_destinatario' => 'Usuario Multi Uso',
            'expira_en' => Carbon::now()->addDays(60),
            'usos_maximos' => 5,
            'contador_usos' => 0,
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'notas' => 'Código para múltiples usos (hasta 5)',
        ]);

        // ============================================================
        // 3. CÓDIGO PARCIALMENTE USADO
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-USADO-001',
            'email' => 'usado@example.com',
            'nombre_destinatario' => 'Usuario Parcial',
            'expira_en' => Carbon::now()->addDays(30),
            'usos_maximos' => 3,
            'contador_usos' => 2, // Ya usado 2 veces
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'notas' => 'Código parcialmente usado (2/3)',
        ]);

        // ============================================================
        // 4. CÓDIGO EXPIRODO
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-EXPIRADO-001',
            'email' => 'expirado@example.com',
            'nombre_destinatario' => 'Usuario Expirado',
            'expira_en' => Carbon::now()->subDays(5), // Ya expiró
            'usos_maximos' => 1,
            'contador_usos' => 0,
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'notas' => 'Código expirado para pruebas',
        ]);

        // ============================================================
        // 5. CÓDIGO AGOTADO (Alcanzó el máximo de usos)
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-AGOTADO-001',
            'email' => 'agotado@example.com',
            'nombre_destinatario' => 'Usuario Agotado',
            'expira_en' => Carbon::now()->addDays(10),
            'usos_maximos' => 2,
            'contador_usos' => 2, // Alcanzó el máximo
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'notas' => 'Código agotado (máximo de usos alcanzado)',
        ]);

        // ============================================================
        // 6. CÓDIGO INACTIVO (Desactivado manualmente)
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-INACTIVO-001',
            'email' => 'inactivo@example.com',
            'nombre_destinatario' => 'Usuario Inactivo',
            'expira_en' => Carbon::now()->addDays(30),
            'usos_maximos' => 1,
            'contador_usos' => 0,
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => false, // Desactivado manualmente
            'notas' => 'Código desactivado manualmente',
        ]);

        // ============================================================
        // 7. CÓDIGO CON FECHA DE ENVÍO Y RECORDATORIO
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-ENVIO-001',
            'email' => 'envio@example.com',
            'nombre_destinatario' => 'Usuario Con Envío',
            'expira_en' => Carbon::now()->addDays(15),
            'usos_maximos' => 1,
            'contador_usos' => 0,
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'fecha_envio' => Carbon::now()->subDays(2),
            'fecha_recordatorio' => Carbon::now()->addDays(3),
            'notas' => 'Código con fechas de envío y recordatorio',
            'metadata' => ['canal' => 'email', 'plantilla' => 'invitacion_v1'],
        ]);

        // ============================================================
        // 8. CÓDIGO CON NOTAS ADICIONALES
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-NOTAS-001',
            'email' => 'notas@example.com',
            'nombre_destinatario' => 'Usuario Con Notas',
            'expira_en' => Carbon::now()->addDays(45),
            'usos_maximos' => 3,
            'contador_usos' => 1,
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'notas' => "Código generado para el evento especial\n- Invitación VIP\n- Acceso a contenido premium\n- Válido por 45 días",
            'metadata' => [
                'evento' => 'Conferencia Anual 2024',
                'nivel' => 'VIP',
                'beneficios' => ['acceso_premium', 'descuento_especial']
            ],
        ]);

        // ============================================================
        // 9. CÓDIGO CON USUARIO ASIGNADO (Si existe usuario)
        // ============================================================
        if ($usuario) {
            CodigoInvitacion::create([
                'codigo' => 'CF-USER-001',
                'email' => $usuario->email,
                'nombre_destinatario' => $usuario->nombre,
                'usado_por_usuario_id' => $usuario->id,
                'usado_en' => Carbon::now()->subDays(1),
                'expira_en' => Carbon::now()->addDays(20),
                'usos_maximos' => 1,
                'contador_usos' => 1,
                'creado_por_admin_id' => $admin->id,
                'esta_activo' => false, // Ya usado
                'notas' => 'Código ya utilizado por un usuario existente',
            ]);
        }

        // ============================================================
        // 10. CÓDIGO PARA USO INMEDIATO (Válido por 1 día)
        // ============================================================
        CodigoInvitacion::create([
            'codigo' => 'CF-URGENTE-001',
            'email' => 'urgente@example.com',
            'nombre_destinatario' => 'Usuario Urgente',
            'expira_en' => Carbon::now()->addDay(), // Solo 1 día
            'usos_maximos' => 1,
            'contador_usos' => 0,
            'creado_por_admin_id' => $admin->id,
            'esta_activo' => true,
            'notas' => 'Código de uso urgente (válido solo 24 horas)',
        ]);

        $this->command->info('✅ Códigos de invitación creados exitosamente!');
        $this->command->info('📊 Total: 10 códigos con diferentes estados');
        
        // Mostrar resumen de estados
        $estados = [
            'activos' => CodigoInvitacion::where('esta_activo', true)->count(),
            'expirados' => CodigoInvitacion::where('expira_en', '<', Carbon::now())->count(),
            'usados' => CodigoInvitacion::where('usado_en', '!=', null)->count(),
        ];
        
        $this->command->table(
            ['Estado', 'Cantidad'],
            [
                ['Activos', $estados['activos']],
                ['Expirados', $estados['expirados']],
                ['Usados', $estados['usados']],
            ]
        );
    }
}