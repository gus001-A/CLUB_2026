<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporta_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reportado_id')->constrained('users')->onDelete('cascade');
            $table->morphs('reportable');
            $table->enum('tipo', ['spam', 'inapropiado', 'falso', 'acoso', 'otro']);
            $table->text('descripcion')->nullable();
            $table->enum('estado', ['pendiente', 'revisado', 'resuelto'])->default('pendiente');
            // Qué admin atendió el reporte (lo marcó revisado/resuelto/bloqueó
            // al reportado). Nullable porque un reporte "pendiente" todavía
            // no lo atiende nadie.
            $table->foreignId('atendido_por_admin_id')->nullable()->constrained('administradores')->nullOnDelete();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reporta_id', 'reportado_id']);
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reportes');
    }
};