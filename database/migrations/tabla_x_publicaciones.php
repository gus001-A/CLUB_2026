<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 👈 ESTABLECER LONGITUD PREDETERMINADA
        Schema::defaultStringLength(191);

        // Tabla publicaciones
        Schema::create('publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->text('texto')->nullable();
            $table->string('imagen')->nullable();
            $table->boolean('es_premium')->default(false);
            $table->boolean('destacado')->default(false);
            $table->integer('likes')->default(0);
            $table->integer('comentarios_count')->default(0);
            
            // 👈 ESPECIFICAR LONGITUD PARA EL ESTADO
            $table->string('estado', 50)->default('publicado'); // publicado, oculto, reportado
            
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['usuario_id', 'created_at']);
            
            // 👈 ÍNDICE CON LONGITUD ESPECÍFICA
            $table->index('estado', 'publicaciones_estado_index');
        });

        // Tabla comentarios
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacion_id')->constrained('publicaciones')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->text('texto');
            $table->integer('likes')->default(0);
            
            // 👈 ESPECIFICAR LONGITUD PARA EL ESTADO
            $table->string('estado', 50)->default('aprobado');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['publicacion_id', 'created_at']);
        });

        // Tabla likes_publicaciones
        Schema::create('likes_publicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publicacion_id')->constrained('publicaciones')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['publicacion_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes_publicaciones');
        Schema::dropIfExists('comentarios');
        Schema::dropIfExists('publicaciones');
    }
};