<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coincidencia_id')->constrained('coincidencias')->onDelete('cascade');
            $table->enum('estado', ['activo', 'bloqueado', 'archivado'])->default('activo');
            $table->timestamp('ultimo_mensaje_en')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('coincidencia_id');
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chats');
    }
};