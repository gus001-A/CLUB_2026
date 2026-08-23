<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            // 'archivos_adjuntos' (json) ya existía en tu modelo — aquí solo
            // agregamos columnas que sí conviene poder filtrar/indexar en SQL
            // directamente, sin tener que leer el JSON.
            $table->enum('tipo', ['texto', 'imagen', 'video', 'audio', 'sistema'])
                ->default('texto')
                ->after('texto');

            $table->string('archivo_path')->nullable()->after('archivos_adjuntos');
            $table->string('archivo_nombre_original')->nullable()->after('archivo_path');
            $table->unsignedInteger('archivo_tamano_bytes')->nullable()->after('archivo_nombre_original');
            $table->unsignedSmallInteger('duracion_segundos')->nullable()->after('archivo_tamano_bytes');
            $table->string('miniatura_path')->nullable()->after('duracion_segundos');

            $table->index(['chat_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::table('mensajes', function (Blueprint $table) {
            $table->dropIndex(['chat_id', 'tipo']);
            $table->dropColumn([
                'tipo',
                'archivo_path',
                'archivo_nombre_original',
                'archivo_tamano_bytes',
                'duracion_segundos',
                'miniatura_path',
            ]);
        });
    }
};
