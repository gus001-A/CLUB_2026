<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('categoria')->nullable();
            $table->string('marca')->nullable();
            $table->decimal('precio', 10, 2);
            $table->json('variantes')->nullable();
            $table->integer('stock')->default(0);
            $table->json('imagenes')->nullable();
            $table->decimal('calificacion', 3, 2)->default(0);
            $table->boolean('esta_activo')->default(true);
            $table->json('etiquetas')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index(['categoria', 'esta_activo']);
            $table->index('precio');
            $table->index('esta_activo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};