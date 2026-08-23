<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();

            // Destinatario — quién RECIBE la notificación.
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();

            // Emisor — quién generó la notificación (quien dio like, comentó,
            // se suscribió, hizo match, etc.). Nullable porque notificaciones
            // "del sistema" (ej. suscripción vencida) no tienen un emisor humano.
            $table->foreignId('emisor_id')->nullable()->constrained('users')->nullOnDelete();

            // like | comentario | suscripcion | suscripcion_vencida | match |
            // mensaje | contenido_nuevo | perfil_like
            $table->string('tipo', 40);

            // Texto ya formateado (puede incluir <strong> para negritas del
            // nombre, el frontend lo pinta con v-html).
            $table->string('mensaje', 500);

            // Referencias opcionales usadas por el frontend para saber a
            // dónde navegar al hacer clic (ver AppLayout.vue -> clickNotificacion).
            $table->unsignedBigInteger('contenido_id')->nullable();
            $table->string('link')->nullable();

            $table->boolean('leida')->default(false);
            $table->timestamp('leida_en')->nullable();

            $table->timestamps();

            $table->index(['usuario_id', 'leida']);
            $table->index(['usuario_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
