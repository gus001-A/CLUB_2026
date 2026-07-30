<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $table = 'pedidos';

    protected $fillable = [
        'usuario_id',
        'numero_pedido',
        'subtotal',
        'envio',
        'total',
        'estado',
        'metodo_pago',
        'pago_id',
        'direccion_envio',
        'numero_seguimiento',
        'metadatos',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'envio' => 'decimal:2',
        'total' => 'decimal:2',
        'direccion_envio' => 'array',
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

    public function items()
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }

    // Scopes
    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    // Accesors
    public function getEstaPagadoAttribute()
    {
        return $this->estado === 'pagado';
    }

    public function getPuedeCancelarAttribute()
    {
        return in_array($this->estado, ['carrito', 'pagado']);
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('cantidad');
    }

    public function getTotalProductosAttribute()
    {
        return $this->items()->count();
    }

    public function getEstadoTextoAttribute()
    {
        $estados = [
            'carrito' => 'Carrito',
            'pagado' => 'Pagado',
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
        ];
        return $estados[$this->estado] ?? $this->estado;
    }

    public function getColorEstadoAttribute()
    {
        $colores = [
            'carrito' => 'gray',
            'pagado' => 'blue',
            'enviado' => 'orange',
            'entregado' => 'green',
            'cancelado' => 'red',
        ];
        return $colores[$this->estado] ?? 'gray';
    }
}