<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contenido_id')->constrained('contenidos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // Un usuario solo puede dar like una vez al mismo contenido —
            // esto es lo que hace posible el "toggle" (dar/quitar like) de
            // forma segura sin duplicados, y sin tener que checar primero
            // desde PHP en cada request.
            $table->unique(['contenido_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
