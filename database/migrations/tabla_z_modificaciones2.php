<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * fotos_eventos.usuario_subio apuntaba a "users", pero quien sube
     * fotos de un evento desde el panel es un administrador (tabla
     * "administradores", guard "admin") — igual que organizador_id en
     * "eventos". La dejamos apuntando a la tabla correcta.
     */
    public function up(): void
    {
        Schema::table('fotos_eventos', function (Blueprint $table) {
            $table->dropForeign(['usuario_subio']);
        });

        Schema::table('fotos_eventos', function (Blueprint $table) {
            $table->foreign('usuario_subio')->references('id')->on('administradores')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('fotos_eventos', function (Blueprint $table) {
            $table->dropForeign(['usuario_subio']);
        });

        Schema::table('fotos_eventos', function (Blueprint $table) {
            $table->foreign('usuario_subio')->references('id')->on('users')->onDelete('cascade');
        });
    }
};