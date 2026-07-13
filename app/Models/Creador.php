<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Creador extends Model
{
    use SoftDeletes;

    protected $table = 'creadores';

    protected $fillable = [
        'usuario_id',
        'biografia',
        'categorias',
        'precios',
        'estado_verificacion',
        'documentos_verificacion',
        'metodo_pago',
        'detalles_pago',
        'es_premium',
        'estadisticas',
    ];

    protected $casts = [
        'categorias' => 'array',
        'precios' => 'array',
        'documentos_verificacion' => 'array',
        'detalles_pago' => 'array',
        'es_premium' => 'boolean',
        'estadisticas' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function contenidos()
    {
        return $this->hasMany(Contenido::class, 'creador_id');
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'creador_id');
    }

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'creador_id');
    }

    // Scopes
    public function scopeVerificado($query)
    {
        return $query->where('estado_verificacion', 'aprobado');
    }

    public function scopePremium($query)
    {
        return $query->where('es_premium', true);
    }

    // Accesors
    public function getEstaVerificadoAttribute()
    {
        return $this->estado_verificacion === 'aprobado';
    }

    public function getTotalSuscriptoresAttribute()
    {
        return $this->suscripciones()->where('estado', 'activa')->count();
    }

    public function getTotalGananciasAttribute()
    {
        return $this->transacciones()->where('estado', 'aprobada')->sum('monto');
    }

    public function getTotalContenidosAttribute()
    {
        return $this->contenidos()->where('estado', 'publicado')->count();
    }

    public function getPrecioSuscripcionAttribute()
    {
        return $this->precios['suscripcion'] ?? null;
    }

    public function getPrecioFotoAttribute()
    {
        return $this->precios['foto'] ?? null;
    }

    public function getPrecioVideoAttribute()
    {
        return $this->precios['video'] ?? null;
    }
}