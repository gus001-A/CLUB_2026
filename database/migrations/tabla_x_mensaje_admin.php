<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Revierte lo que agregó tabla_x_mensaje_admin.php. Ese enfoque (admin
     * interviniendo dentro del chat de dos usuarios) se reemplaza por un
     * sistema de soporte dedicado (tabla_x_soporte.php / tabla_x_soporte_mensajes.php).
     */
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('intervenido_por_admin_id');
            $table->dropColumn('ultima_intervencion_en');
        });

        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('administrador_id');
        });

        // remitente_id vuelve a ser obligatorio: todo mensaje en `mensajes`
        // es de nuevo estrictamente usuario-a-usuario.
        Schema::table('mensajes', function (Blueprint $table) {
            $table->foreignId('remitente_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->foreignId('remitente_id')->nullable()->change();
        });

        Schema::table('mensajes', function (Blueprint $table) {
            $table->foreignId('administrador_id')
                ->nullable()
                ->after('remitente_id')
                ->constrained('administradores')
                ->nullOnDelete();
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->foreignId('intervenido_por_admin_id')
                ->nullable()
                ->after('estado')
                ->constrained('administradores')
                ->nullOnDelete();
            $table->timestamp('ultima_intervencion_en')->nullable()->after('intervenido_por_admin_id');
        });
    }
};