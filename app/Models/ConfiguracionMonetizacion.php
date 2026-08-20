<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionMonetizacion extends Model
{
    protected $table = 'configuracion_monetizacion';

    protected $fillable = [
        'creador_id', 
        
        // Datos de Mercado Pago
        'mp_access_token',
        'mp_refresh_token',
        'mp_token_expires_at',
        'mp_user_id',
        'mp_public_key',
        
        // Configuración de pagos
        'modelo_ingresos',
        'precio_personalizado',
        'comision_plataforma',
        
        // Promociones
        'prueba_gratuita',
        'descuento_lanzamiento',
        'paquete_vip',
        
        // Método de pago (referencias seguras)
        'mp_customer_id',
        'mp_card_id',
        'tarjeta_ultimos4',
        'tarjeta_marca',
        
        // Frecuencia
        'frecuencia_pago',
        
        // Reglas de acceso
        'solo_suscriptores',
        'aprobar_manualmente',
        'permitir_mensajes_premium',
        'mostrar_vista_previa',
        'permitir_compra_individual',
        
        // Estado
        'estado',
    ];

    protected $casts = [
        'mp_token_expires_at' => 'datetime',
        'prueba_gratuita' => 'boolean',
        'descuento_lanzamiento' => 'boolean',
        'paquete_vip' => 'boolean',
        'solo_suscriptores' => 'boolean',
        'aprobar_manualmente' => 'boolean',
        'permitir_mensajes_premium' => 'boolean',
        'mostrar_vista_previa' => 'boolean',
        'permitir_compra_individual' => 'boolean',
        'precio_personalizado' => 'decimal:2',
        'comision_plataforma' => 'decimal:2',
    ];

    /**
     * Relación con el creador
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(Creador::class);
    }

    /**
     * Verifica si el token de Mercado Pago es válido
     */
    public function getTokenValidoAttribute(): bool
    {
        if (!$this->mp_access_token) {
            return false;
        }

        if (!$this->mp_token_expires_at) {
            return true;
        }

        return $this->mp_token_expires_at->isFuture();
    }

    /**
     * Obtiene el precio actual según el modelo seleccionado
     */
    public function getPrecioActualAttribute(): float
    {
        // Si es exclusivo y tiene precio personalizado
        if ($this->modelo_ingresos === 'exclusivo' && $this->precio_personalizado) {
            return (float) $this->precio_personalizado;
        }

        // Precios base en MXN
        $precios = [
            'suscripcion' => 199.99,
            'foto' => 299.99,
            'video' => 499.99,
        ];

        return $precios[$this->modelo_ingresos] ?? 199.99;
    }

    /**
     * Obtiene el título del modelo de ingresos
     */
    public function getModeloTituloAttribute(): string
    {
        $modelos = [
            'suscripcion' => 'Suscripción mensual',
            'foto' => 'Pago por foto',
            'video' => 'Pago por video',
            'exclusivo' => 'Contenido exclusivo',
        ];

        return $modelos[$this->modelo_ingresos] ?? 'No seleccionado';
    }

    /**
     * Obtiene la comisión que recibe el creador (100% - comisión plataforma)
     */
    public function getComisionCreadorAttribute(): float
    {
        return 100 - $this->comision_plataforma;
    }

    /**
     * Obtiene el monto que recibe el creador por una venta
     */
    public function calcularMontoCreador(float $montoVenta): float
    {
        return $montoVenta * ($this->comision_creador / 100);
    }

    /**
     * Obtiene el monto de la comisión de la plataforma
     */
    public function calcularComisionPlataforma(float $montoVenta): float
    {
        return $montoVenta * ($this->comision_plataforma / 100);
    }

    /**
     * Verifica si tiene una tarjeta guardada
     */
    public function getTieneTarjetaAttribute(): bool
    {
        return !is_null($this->mp_customer_id) && !is_null($this->mp_card_id);
    }

    /**
     * Obtiene la tarjeta formateada para mostrar
     */
    public function getTarjetaDisplayAttribute(): string
    {
        if (!$this->tiene_tarjeta || !$this->tarjeta_ultimos4) {
            return 'Sin tarjeta registrada';
        }

        $marca = $this->tarjeta_marca ?? 'Tarjeta';
        return "{$marca} terminación **** {$this->tarjeta_ultimos4}";
    }

    /**
     * Obtiene el método de cobro actual
     */
    public function getMetodoCobroAttribute(): string
    {
        if ($this->tiene_tarjeta) {
            return $this->tarjeta_display;
        }
        return 'Sin método de cobro configurado';
    }

    /**
     * Scopes
     */
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeConTarjeta($query)
    {
        return $query->whereNotNull('mp_customer_id')
                    ->whereNotNull('mp_card_id');
    }

    public function scopeConTokenValido($query)
    {
        return $query->whereNotNull('mp_access_token')
                    ->where(function ($q) {
                        $q->whereNull('mp_token_expires_at')
                          ->orWhere('mp_token_expires_at', '>', now());
                    });
    }

    /**
     * Actualiza el estado de la tarjeta desde Mercado Pago
     */
    public function actualizarTarjeta(string $customerId, string $cardId, string $ultimos4, string $marca = null): void
    {
        $this->update([
            'mp_customer_id' => $customerId,
            'mp_card_id' => $cardId,
            'tarjeta_ultimos4' => $ultimos4,
            'tarjeta_marca' => $marca,
        ]);
    }

    /**
     * Elimina la referencia de la tarjeta
     */
    public function eliminarTarjeta(): void
    {
        $this->update([
            'mp_customer_id' => null,
            'mp_card_id' => null,
            'tarjeta_ultimos4' => null,
            'tarjeta_marca' => null,
        ]);
    }

    /**
     * Actualiza los tokens de Mercado Pago
     */
    public function actualizarTokensMercadoPago(array $tokens): void
    {
        $this->update([
            'mp_access_token' => $tokens['access_token'] ?? $this->mp_access_token,
            'mp_refresh_token' => $tokens['refresh_token'] ?? $this->mp_refresh_token,
            'mp_token_expires_at' => isset($tokens['expires_in']) 
                ? now()->addSeconds($tokens['expires_in'])
                : $this->mp_token_expires_at,
            'mp_user_id' => $tokens['user_id'] ?? $this->mp_user_id,
            'mp_public_key' => $tokens['public_key'] ?? $this->mp_public_key,
        ]);
    }
}