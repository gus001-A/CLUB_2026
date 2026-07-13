<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('creador_id')->nullable()->constrained('creadores')->onDelete('set null');
            $table->enum('tipo', ['suscripcion', 'compra_contenido', 'propina', 'retiro']);
            $table->decimal('monto', 12, 2);
            $table->string('moneda', 3)->default('USD');
            $table->decimal('comision', 12, 2)->default(0);
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'reembolsada', 'retirada'])->default('pendiente');
            $table->string('pago_id')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['usuario_id', 'estado']);
            $table->index('creador_id');
            $table->index('tipo');
            $table->index('pago_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacciones');
    }
};