<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->foreignId('remitente_id')->constrained('users')->onDelete('cascade');
            $table->text('texto')->nullable();
            $table->json('archivos_adjuntos')->nullable();
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_en')->nullable();
            $table->enum('estado', ['enviado', 'entregado', 'leido', 'fallido'])->default('enviado');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['chat_id', 'created_at']);
            $table->index('remitente_id');
            $table->index('leido');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mensajes');
    }
};