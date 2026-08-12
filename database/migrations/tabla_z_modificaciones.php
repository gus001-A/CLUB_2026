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
        // 1. Agregar campo nickname a la tabla administradores (después de nombre)
        Schema::table('administradores', function (Blueprint $table) {
            $table->string('nickname')->nullable()->after('nombre');
        });

        // 2. Eliminar el campo fotos de la tabla perfiles
        Schema::table('perfiles', function (Blueprint $table) {
            $table->dropColumn('fotos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: eliminar nickname de administradores
        Schema::table('administradores', function (Blueprint $table) {
            $table->dropColumn('nickname');
        });

        // Revertir: restaurar campo fotos en perfiles
        Schema::table('perfiles', function (Blueprint $table) {
            $table->json('fotos')->nullable()->after('pasatiempos');
        });
    }
};