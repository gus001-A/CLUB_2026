<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizador_id')->constrained('users')->onDelete('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->time('hora');
            $table->string('ciudad');
            $table->string('zona_ubicacion')->nullable();
            $table->decimal('ubicacion_lat', 10, 8)->nullable();
            $table->decimal('ubicacion_lng', 11, 8)->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->integer('capacidad')->nullable();
            $table->enum('tipo', ['vip', 'general'])->default('general');
            $table->string('categoria')->nullable();
            $table->string('codigo_vestimenta')->nullable();
            $table->enum('estado', ['borrador', 'publicado', 'cancelado', 'completo'])->default('borrador');
            $table->boolean('destacado')->default(false);
            $table->string('imagen')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['fecha', 'estado']);
            $table->index('organizador_id');
            $table->index('ciudad');
            $table->index('destacado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('eventos');
    }
};