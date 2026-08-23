<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔧 FIX: el modelo Notificacion usa el trait SoftDeletes (igual que
        // casi todos los modelos de la app), lo que hace que Eloquent agregue
        // automáticamente "WHERE deleted_at IS NULL" a CADA consulta contra
        // esta tabla — pero la migración original de 'notificaciones' nunca
        // incluyó esa columna. Por eso tronaba con:
        // "Unknown column 'notificaciones.deleted_at' in 'where clause'"
        // en /notificaciones y /notificaciones/nuevas (ambos consultan esta
        // tabla a través del modelo).
        if (!Schema::hasColumn('notificaciones', 'deleted_at')) {
            Schema::table('notificaciones', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};