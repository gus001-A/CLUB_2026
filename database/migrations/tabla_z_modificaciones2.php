<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fotos_eventos')) {
            Schema::table('fotos_eventos', function (Blueprint $table) {
                $table->dropForeign(['usuario_subio']);
            });

            Schema::table('fotos_eventos', function (Blueprint $table) {
                $table->foreign('usuario_subio')->references('id')->on('administradores')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('fotos_eventos')) {
            Schema::table('fotos_eventos', function (Blueprint $table) {
                $table->dropForeign(['usuario_subio']);
            });

            Schema::table('fotos_eventos', function (Blueprint $table) {
                $table->foreign('usuario_subio')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};