<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Corre después de tabla_x_soporte.php (necesita la tabla `soportes`).
     */
    public function up(): void
    {
        Schema::create('mensajes_soporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('soporte_id')->constrained('soportes')->cascadeOnDelete();
            // Un mensaje lo manda el usuario O el admin, nunca ambos.
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('administrador_id')->nullable()->constrained('administradores')->nullOnDelete();
            $table->text('texto');
            $table->json('archivos_adjuntos')->nullable();
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_en')->nullable();
            $table->string('estado')->default('enviado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_soporte');
    }
};