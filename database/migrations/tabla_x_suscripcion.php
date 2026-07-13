<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creador_id')->constrained('creadores')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('plan')->default('mensual');
            $table->decimal('precio', 10, 2);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_renovacion')->nullable();
            $table->enum('estado', ['activa', 'cancelada', 'expirada', 'pago_fallido'])->default('activa');
            $table->string('pago_id')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['creador_id', 'usuario_id']);
            $table->index(['creador_id', 'estado']);
            $table->index('usuario_id');
            $table->index('fecha_renovacion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('suscripciones');
    }
};