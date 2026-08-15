<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corre después de: users (0001_01_01_...), tabla_a_administradores.php
     * y tabla_reportes.php (reporte_id es opcional pero si existe debe
     * poder apuntar a una fila real).
     */
    public function up(): void
    {
        Schema::create('soportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('administrador_id')->nullable()->constrained('administradores')->nullOnDelete();
            $table->foreignId('reporte_id')->nullable()->constrained('reportes')->nullOnDelete();
            $table->string('asunto')->nullable();
            $table->enum('origen', ['reporte', 'manual', 'otro'])->default('manual');
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->timestamp('ultimo_mensaje_en')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soportes');
    }
};