<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el ENUM para agregar más valores
        DB::statement("ALTER TABLE coincidencias MODIFY COLUMN origen ENUM('deslizar', 'express', 'like', 'super', 'pass', 'match') NOT NULL DEFAULT 'deslizar'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE coincidencias MODIFY COLUMN origen ENUM('deslizar', 'express') NOT NULL DEFAULT 'deslizar'");
    }
};