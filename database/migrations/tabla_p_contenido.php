<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('contenidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creador_id')->constrained('creadores')->onDelete('cascade');
            $table->enum('tipo', ['foto', 'video', 'galeria', 'exclusivo'])->default('foto');
            $table->string('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->json('archivos');
            $table->decimal('precio', 10, 2)->default(0);
            $table->enum('visibilidad', ['publico', 'suscriptores', 'individual'])->default('publico');
            $table->enum('estado', ['borrador', 'publicado', 'programado'])->default('borrador');
            $table->json('etiquetas')->nullable();
            $table->timestamp('programado_en')->nullable();
            $table->boolean('es_premium')->default(false);
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('creador_id');
            $table->index(['estado', 'programado_en']);
            $table->index('visibilidad');
            $table->index('es_premium');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contenidos');
    }
};