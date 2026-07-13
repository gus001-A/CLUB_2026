<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->string('numero_pedido')->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('envio', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('estado', ['carrito', 'pagado', 'enviado', 'entregado', 'cancelado'])->default('carrito');
            $table->string('pago_id')->nullable();
            $table->json('direccion_envio')->nullable();
            $table->string('numero_seguimiento')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('usuario_id');
            $table->index('numero_pedido');
            $table->index('estado');
            $table->index('numero_seguimiento');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};