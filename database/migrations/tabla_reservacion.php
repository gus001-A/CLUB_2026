<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('folio')->unique();
            $table->integer('asistentes')->default(1);
            $table->enum('tipo_acceso', ['vip', 'general'])->default('general');
            $table->string('pago_id')->nullable();
            $table->string('codigo_qr')->nullable();
            $table->enum('estado', ['pendiente', 'aprobada', 'cancelada', 'usada'])->default('pendiente');
            $table->decimal('total', 10, 2);
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['evento_id', 'estado']);
            $table->index('usuario_id');
            $table->index('folio');
            $table->index('codigo_qr');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservas');
    }
};