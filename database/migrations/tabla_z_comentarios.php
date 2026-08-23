<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('comentarios')) {
            Schema::create('comentarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contenido_id');
                $table->unsignedBigInteger('usuario_id');
                $table->text('texto');
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Índices
                $table->index('contenido_id');
                $table->index('usuario_id');
                $table->index('parent_id');

                // Llaves foráneas
                $table->foreign('contenido_id')
                    ->references('id')
                    ->on('contenidos')
                    ->onDelete('cascade');

                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');

                $table->foreign('parent_id')
                    ->references('id')
                    ->on('comentarios')
                    ->onDelete('cascade');
            });
        } else {
            // Si la tabla existe, verificamos que tenga la columna
            if (!Schema::hasColumn('comentarios', 'contenido_id')) {
                Schema::table('comentarios', function (Blueprint $table) {
                    $table->unsignedBigInteger('contenido_id')->after('id');
                    $table->foreign('contenido_id')
                        ->references('id')
                        ->on('contenidos')
                        ->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};