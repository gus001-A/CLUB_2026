<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('llamadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('llamante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receptor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('tipo', ['audio', 'video']);
            $table->enum('estado', ['sonando', 'en_curso', 'finalizada', 'rechazada', 'perdida'])
                ->default('sonando');
            $table->timestamp('iniciada_en')->useCurrent();
            $table->timestamp('contestada_en')->nullable();
            $table->timestamp('finalizada_en')->nullable();
            $table->unsignedInteger('duracion_segundos')->nullable();
            $table->string('motivo_fin')->nullable(); // 'colgada' | 'rechazada' | 'sin_respuesta' | 'error'
            $table->timestamps();

            $table->index(['chat_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llamadas');
    }
};
