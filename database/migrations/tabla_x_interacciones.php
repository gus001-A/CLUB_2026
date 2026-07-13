<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contenido_id')->constrained('contenidos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['like', 'comentario', 'compartir', 'vista']);
            $table->text('comentario')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['contenido_id', 'usuario_id', 'tipo']);
            $table->index(['contenido_id', 'tipo']);
            $table->index('usuario_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('interacciones');
    }
};