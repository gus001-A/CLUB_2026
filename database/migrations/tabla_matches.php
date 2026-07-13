<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coincidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_a_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('usuario_b_id')->constrained('users')->onDelete('cascade');
            $table->integer('compatibilidad')->nullable();
            $table->enum('estado', ['pendiente', 'coincidencia', 'rechazado'])->default('pendiente');
            $table->enum('origen', ['deslizar', 'express'])->default('deslizar');
            $table->timestamp('coincidio_en')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['usuario_a_id', 'usuario_b_id']);
            $table->index(['usuario_a_id', 'usuario_b_id', 'estado']);
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('coincidencias');
    }
};