<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================================
        // 1. Modificar la columna estado para incluir 'completo'
        // ============================================================
        DB::statement("ALTER TABLE users MODIFY COLUMN estado ENUM('incompleto', 'pendiente', 'verificado', 'bloqueado', 'completo') DEFAULT 'incompleto'");

        // ============================================================
        // 2. Eliminar el índice existente (si existe)
        // ============================================================
        try {
            DB::statement("ALTER TABLE eventos DROP INDEX eventos_organizador_id_index");
        } catch (\Exception $e) {
            // El índice puede no existir, continuamos
        }

        // ============================================================
        // 3. Crear la nueva clave foránea apuntando a administradores
        // ============================================================
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreign('organizador_id')
                  ->references('id')
                  ->on('administradores')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });

        // ============================================================
        // 4. Crear un administrador por defecto si no existe
        // ============================================================
        $adminExists = DB::table('administradores')->where('email', 'admin@eventos.com')->first();
        
        if (!$adminExists) {
            DB::table('administradores')->insert([
                'nombre' => 'Admin Eventos',
                'nickname' => 'admin_eventos',
                'email' => 'admin@eventos.com',
                'password' => bcrypt('admin123'),
                'esta_activo' => true,
                'email_verificado_en' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            if (isset($this->command)) {
                $this->command->info('✅ Administrador creado: admin@eventos.com / admin123');
            }
        } else {
            if (isset($this->command)) {
                $this->command->info('✅ Administrador ya existe: admin@eventos.com');
            }
        }

        // ============================================================
        // 5. Crear un usuario admin en users si no existe
        // ============================================================
        $userExists = DB::table('users')->where('email', 'admin@eventos.com')->first();
        
        if (!$userExists) {
            DB::table('users')->insert([
                'nombre' => 'Admin Eventos',
                'apodo' => 'admin_eventos',
                'email' => 'admin@eventos.com',
                'password' => bcrypt('admin123'),
                'rol' => 'admin',
                'estado' => 'verificado',
                'email_verificado_en' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            if (isset($this->command)) {
                $this->command->info('✅ Usuario administrador creado en users: admin@eventos.com / admin123');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ============================================================
        // 1. Revertir el ENUM de estado
        // ============================================================
        DB::statement("ALTER TABLE users MODIFY COLUMN estado ENUM('incompleto', 'pendiente', 'verificado', 'bloqueado') DEFAULT 'incompleto'");

        // ============================================================
        // 2. Eliminar la clave foránea hacia administradores
        // ============================================================
        try {
            Schema::table('eventos', function (Blueprint $table) {
                $table->dropForeign('eventos_organizador_id_foreign');
            });
        } catch (\Exception $e) {
            // La clave foránea puede no existir, continuamos
        }

        // ============================================================
        // 3. Restaurar el índice original
        // ============================================================
        try {
            Schema::table('eventos', function (Blueprint $table) {
                $table->index('organizador_id', 'eventos_organizador_id_index');
            });
        } catch (\Exception $e) {
            // El índice puede ya existir, continuamos
        }
    }
};