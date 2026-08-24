<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_monetizacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creador_id')->constrained('creadores')->onDelete('cascade');
            
            // ============================================================
            // DATOS DE MERCADO PAGO (OAuth)
            // ============================================================
            // Especificamos longitud para evitar error de índice
            $table->string('mp_access_token', 500)->nullable(); // Token de Mercado Pago del creador
            $table->string('mp_refresh_token', 500)->nullable(); // Para renovar el token
            $table->timestamp('mp_token_expires_at')->nullable(); // Cuándo expira
            $table->string('mp_user_id', 100)->nullable(); // ID del usuario en Mercado Pago - reducido
            $table->string('mp_public_key', 255)->nullable(); // Public key para el frontend
            
            // ============================================================
            // CONFIGURACIÓN DE PAGOS
            // ============================================================
            $table->enum('modelo_ingresos', ['suscripcion', 'foto', 'video', 'exclusivo'])
                  ->default('suscripcion');
            
            $table->decimal('precio_personalizado', 10, 2)->nullable(); // Para modelo 'exclusivo'
            
            // Porcentaje de comisión que la plataforma retiene
            $table->decimal('comision_plataforma', 5, 2)->default(20.00); // 20%
            
            // ============================================================
            // PROMOCIONES Y BENEFICIOS
            // ============================================================
            $table->boolean('prueba_gratuita')->default(true);
            $table->boolean('descuento_lanzamiento')->default(true);
            $table->boolean('paquete_vip')->default(true);
            
            // ============================================================
            // MÉTODO DE PAGO (Referencias seguras)
            // ============================================================
            // Tarjeta guardada en Mercado Pago (Customer)
            $table->string('mp_customer_id', 100)->nullable(); // ID del customer en MP
            $table->string('mp_card_id', 100)->nullable(); // ID de la tarjeta guardada
            $table->string('tarjeta_ultimos4', 4)->nullable(); // Solo para mostrar
            $table->string('tarjeta_marca', 50)->nullable(); // Visa, Mastercard, etc.
            
            // ============================================================
            // FRECUENCIA DE PAGO
            // ============================================================
            $table->enum('frecuencia_pago', ['Semanal', 'Quincenal', 'Mensual'])
                  ->default('Mensual');
            
            // ============================================================
            // REGLAS DE ACCESO
            // ============================================================
            $table->boolean('solo_suscriptores')->default(true);
            $table->boolean('aprobar_manualmente')->default(true);
            $table->boolean('permitir_mensajes_premium')->default(true);
            $table->boolean('mostrar_vista_previa')->default(true);
            $table->boolean('permitir_compra_individual')->default(false);
            
            // ============================================================
            // ESTADO
            // ============================================================
            $table->enum('estado', ['activo', 'inactivo', 'pendiente'])
                  ->default('pendiente');
            
            $table->timestamps();
            
            // Índices - ya no indexamos mp_user_id para evitar el error
            $table->index('creador_id');
            $table->index('estado');
            // $table->index('mp_user_id'); // <-- COMENTADO o ELIMINADO
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_monetizacion');
    }
};