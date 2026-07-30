<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemPedido extends Model
{
    use SoftDeletes;

    protected $table = 'items_pedido';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'variante',
        'cantidad',
        'precio',
        'total',
        'metadatos',
    ];

    protected $casts = [
        'variante' => 'array',
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
        'total' => 'decimal:2',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    // Accesors
    public function getVarianteTextoAttribute()
    {
        if (!$this->variante) return 'Estándar';
        
        $texto = [];
        foreach ($this->variante as $key => $value) {
            $texto[] = ucfirst($key) . ': ' . $value;
        }
        return implode(', ', $texto);
    }

    public function getSubtotalAttribute()
    {
        return $this->precio * $this->cantidad;
    }
}