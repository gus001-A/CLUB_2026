<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('creadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->text('biografia')->nullable();
            $table->json('categorias')->nullable();
            $table->json('precios')->nullable();
            $table->enum('estado_verificacion', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->json('documentos_verificacion')->nullable();
            $table->enum('metodo_pago', ['paypal', 'banco'])->nullable();
            $table->json('detalles_pago')->nullable();
            $table->boolean('es_premium')->default(false);
            $table->json('estadisticas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('usuario_id');
            $table->index('estado_verificacion');
            $table->index('es_premium');
        });
    }

    public function down()
    {
        Schema::dropIfExists('creadores');
    }
};