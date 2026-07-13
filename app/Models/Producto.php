<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'sku',
        'nombre',
        'descripcion',
        'categoria',
        'marca',
        'precio',
        'variantes',
        'stock',
        'imagenes',
        'calificacion',
        'esta_activo',
        'etiquetas',
        'metadatos',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'variantes' => 'array',
        'imagenes' => 'array',
        'calificacion' => 'decimal:2',
        'esta_activo' => 'boolean',
        'etiquetas' => 'array',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function itemsPedido()
    {
        return $this->hasMany(ItemPedido::class, 'producto_id');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('esta_activo', true);
    }

    public function scopeConStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    // Accesors
    public function getEstaEnStockAttribute()
    {
        return $this->stock > 0;
    }

    public function getPrecioFormateadoAttribute()
    {
        return '$' . number_format($this->precio, 2);
    }

    public function getCalificacionTextoAttribute()
    {
        if ($this->calificacion >= 4.5) return 'Excelente';
        if ($this->calificacion >= 4) return 'Muy bueno';
        if ($this->calificacion >= 3) return 'Bueno';
        if ($this->calificacion >= 2) return 'Regular';
        return 'Malo';
    }

    public function getImagenPrincipalAttribute()
    {
        return $this->imagenes ? $this->imagenes[0] : null;
    }

    public function getVariantesFormateadasAttribute()
    {
        if (!$this->variantes) return [];
        
        $variantesFormateadas = [];
        foreach ($this->variantes as $key => $valores) {
            $variantesFormateadas[] = ucfirst($key) . ': ' . implode(', ', $valores);
        }
        return $variantesFormateadas;
    }

    // Método para verificar stock de variante
    public function verificarStockVariante($variante)
    {
        // Implementar lógica según cómo guardes las variantes
        return $this->stock > 0;
    }
}