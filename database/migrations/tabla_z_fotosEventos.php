<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fotos_eventos', function (Blueprint $table) {
            $table->id();
            
            // Campos principales
            $table->string('nombre_imagen');
            $table->string('ruta');
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('usuario_subio')->constrained('users')->onDelete('cascade');
            $table->timestamp('fecha_subida')->useCurrent();
            
            // Timestamps y soft delete
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para mejorar rendimiento
            $table->index(['evento_id', 'fecha_subida']);
            $table->index('usuario_subio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fotos_eventos');
    }
};