<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perfil extends Model
{
    use SoftDeletes;

    protected $table = 'perfiles';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'descripcion',
        'intereses',
        'pasatiempos',
        'fotos',
        'privacidad_fotos',
        'estado_verificacion',
        'esta_verificado',
        'puntuacion_compatibilidad',
        'ubicacion_lat',
        'ubicacion_lng',
        'ubicacion_ciudad',
        'metadatos',
    ];

    protected $casts = [
        'intereses' => 'array',
        'pasatiempos' => 'array',
        'fotos' => 'array',
        'esta_verificado' => 'boolean',
        'puntuacion_compatibilidad' => 'integer',
        'ubicacion_lat' => 'decimal:8',
        'ubicacion_lng' => 'decimal:8',
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

    // Scopes
    public function scopeVerificado($query)
    {
        return $query->where('esta_verificado', true);
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Accesor para obtener la primera foto
    public function getFotoPrincipalAttribute()
    {
        return $this->fotos ? $this->fotos[0] : null;
    }

    // Accesor para obtener ubicación formateada
    public function getUbicacionFormateadaAttribute()
    {
        if ($this->ubicacion_ciudad) {
            return $this->ubicacion_ciudad;
        }
        
        if ($this->ubicacion_lat && $this->ubicacion_lng) {
            return $this->ubicacion_lat . ', ' . $this->ubicacion_lng;
        }
        
        return 'Ubicación no especificada';
    }
}