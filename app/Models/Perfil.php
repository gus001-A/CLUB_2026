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

    // Relación con fotos
    public function fotos()
    {
        return $this->hasMany(Fotos::class, 'perfil_id');
    }

    // Obtener la foto principal
    public function fotoPrincipal()
    {
        return $this->hasOne(Fotos::class, 'perfil_id')->where('es_principal', true);
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

    // Accesor para obtener la primera foto (de la relación)
    public function getFotoPrincipalAttribute()
    {
        if ($this->fotos && is_array($this->fotos) && count($this->fotos) > 0) {
            return $this->fotos[0];
        }
        
        // Buscar en la nueva relación
        $foto = $this->fotos()->where('es_principal', true)->first();
        if ($foto) {
            return $foto->url;
        }
        
        return null;
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