<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_id')->constrained('perfiles')->onDelete('cascade');
            $table->string('ruta_foto');
            $table->timestamp('fecha_subida')->nullable();
            $table->json('permisos')->nullable()->comment('["publica", "privada", "solo_matches"]');
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('perfil_id');
            $table->index('es_principal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fotos');
    }
};