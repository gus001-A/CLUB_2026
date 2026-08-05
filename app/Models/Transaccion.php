<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaccion extends Model
{
    use SoftDeletes;

    protected $table = 'transacciones';

    protected $fillable = [
        'usuario_id',
        'creador_id',
        'tipo',
        'monto',
        'moneda',
        'comision',
        'estado',
        'metodo_pago',
        'pago_id',
        'metadatos',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'comision' => 'decimal:2',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function creador()
    {
        return $this->belongsTo(Creador::class, 'creador_id');
    }

    // Scopes
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Accesors
    public function getEstaAprobadaAttribute()
    {
        return $this->estado === 'aprobada';
    }

    public function getMontoNetoAttribute()
    {
        return $this->monto - $this->comision;
    }

    public function getTipoNombreAttribute()
    {
        $tipos = [
            'suscripcion' => 'Suscripción',
            'compra_contenido' => 'Compra de contenido',
            'propina' => 'Propina',
            'retiro' => 'Retiro',
        ];
        return $tipos[$this->tipo] ?? $this->tipo;
    }

    public function getEstadoNombreAttribute()
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'aprobada' => 'Completado',
            'rechazada' => 'Rechazado',
            'reembolsada' => 'Reembolsado',
            'retirada' => 'Retirado',
        ];
        return $estados[$this->estado] ?? $this->estado;
    }

    public function getMetodoPagoNombreAttribute()
    {
        $metodos = [
            'tarjeta_credito' => 'Tarjeta de Crédito',
            'tarjeta_debito' => 'Tarjeta de Débito',
            'paypal' => 'PayPal',
            'transferencia' => 'Transferencia',
            'otro' => 'Otro',
        ];
        return $metodos[$this->metodo_pago] ?? 'Otro';
    }
}