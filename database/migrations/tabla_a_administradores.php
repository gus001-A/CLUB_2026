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
        // Tabla de administradores
        Schema::create('administradores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->unique();
            $table->timestamp('email_verificado_en')->nullable();
            $table->string('password');
            $table->string('rol')->default('admin'); // super_admin, admin, moderador, soporte
            $table->boolean('esta_activo')->default(true);
            $table->timestamp('ultimo_acceso_en')->nullable();
            $table->string('ultimo_acceso_ip')->nullable();
            $table->json('permisos')->nullable();
            $table->string('foto_perfil_url')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('autenticacion_doble_habilitada')->default(false);
            $table->string('autenticacion_doble_secreto')->nullable();
            $table->text('autenticacion_doble_codigos_recuperacion')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['email', 'esta_activo']);
            $table->index('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administradores');
    }
};