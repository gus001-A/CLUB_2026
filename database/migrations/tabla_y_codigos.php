<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla de códigos de invitación
        Schema::create('codigos_invitacion', function (Blueprint $table) {
            $table->id();
            
            // Código principal
            $table->string('codigo')->unique();
            
            // Información del destinatario
            $table->string('email')->nullable();
            $table->string('nombre_destinatario')->nullable();
            
            // Control de uso - Relación con tu tabla users
            $table->foreignId('usado_por_usuario_id')
                ->nullable()
                ->constrained('users', 'id')
                ->nullOnDelete();
            
            $table->timestamp('usado_en')->nullable();
            
            // Límites y expiración
            $table->integer('usos_maximos')->default(1);
            $table->integer('contador_usos')->default(0);
            $table->timestamp('expira_en')->nullable();
            
            // Relación con administradores
            $table->foreignId('creado_por_admin_id')
                ->nullable()
                ->constrained('administradores', 'id')
                ->nullOnDelete();
            
            // Estado
            $table->boolean('esta_activo')->default(true);
            
            // Información adicional
            $table->text('notas')->nullable();
            $table->json('metadata')->nullable();
            
            // Auditoría
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_recordatorio')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index('codigo');
            $table->index('email');
            $table->index('esta_activo');
            $table->index(['expira_en', 'esta_activo']);
            $table->index('usado_por_usuario_id');
            $table->index('creado_por_admin_id');
        });

        // Tabla de historial de uso de códigos
        Schema::create('historial_codigos_invitacion', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('codigo_invitacion_id')
                ->constrained('codigos_invitacion')
                ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                ->constrained('users', 'id')
                ->cascadeOnDelete();
            
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('datos_adicionales')->nullable();
            
            $table->timestamps();

            $table->index('codigo_invitacion_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        // Tabla de plantillas de invitación
        Schema::create('plantillas_invitacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('asunto');
            $table->text('contenido_html');
            $table->text('contenido_texto')->nullable();
            $table->json('variables')->nullable();
            $table->boolean('esta_activo')->default(true);
            
            $table->foreignId('creado_por_admin_id')
                ->constrained('administradores')
                ->cascadeOnDelete();
            
            $table->timestamps();

            $table->index('nombre');
            $table->index('esta_activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas_invitacion');
        Schema::dropIfExists('historial_codigos_invitacion');
        Schema::dropIfExists('codigos_invitacion');
    }
};