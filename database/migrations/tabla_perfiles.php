<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('perfiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['personal', 'pareja'])->default('personal');
            $table->text('descripcion')->nullable();
            $table->json('intereses')->nullable();
            $table->json('pasatiempos')->nullable();
            $table->json('fotos')->nullable();
            $table->enum('privacidad_fotos', ['publico', 'coincidencias', 'oculto'])->default('publico');
            $table->enum('estado_verificacion', ['pendiente', 'verificado', 'rechazado'])->default('pendiente');
            $table->boolean('esta_verificado')->default(false);
            $table->integer('puntuacion_compatibilidad')->nullable();
            $table->decimal('ubicacion_lat', 10, 8)->nullable();
            $table->decimal('ubicacion_lng', 11, 8)->nullable();
            $table->string('ubicacion_ciudad')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('usuario_id');
            $table->index('esta_verificado');
            $table->index('tipo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('perfiles');
    }
};