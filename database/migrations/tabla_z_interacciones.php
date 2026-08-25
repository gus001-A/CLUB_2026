<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('interacciones')) {
            Schema::create('interacciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contenido_id');
                $table->unsignedBigInteger('usuario_id');
                $table->enum('tipo', ['like', 'comentario', 'vista', 'compartir']);
                $table->text('comentario')->nullable();
                $table->json('metadatos')->nullable();
                $table->unsignedBigInteger('publicacion_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Índices
                $table->index('contenido_id');
                $table->index('usuario_id');
                $table->index('tipo');
                $table->index('publicacion_id');

                // Llaves foráneas
                $table->foreign('contenido_id')
                    ->references('id')
                    ->on('contenidos')
                    ->onDelete('cascade');

                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('publicacion_id')
                    ->references('id')
                    ->on('publicaciones')
                    ->onDelete('cascade');
            });
        } else {
            // Si la tabla existe, agregamos columnas faltantes
            Schema::table('interacciones', function (Blueprint $table) {
                if (!Schema::hasColumn('interacciones', 'publicacion_id')) {
                    $table->unsignedBigInteger('publicacion_id')->nullable()->after('metadatos');
                    $table->foreign('publicacion_id')
                        ->references('id')
                        ->on('publicaciones')
                        ->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('interacciones');
    }
};