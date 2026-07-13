<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->json('variante')->nullable();
            $table->integer('cantidad');
            $table->decimal('precio', 10, 2);
            $table->decimal('total', 10, 2);
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pedido_id', 'producto_id']);
            $table->index('producto_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('items_pedido');
    }
};