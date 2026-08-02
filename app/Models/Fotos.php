<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fotos extends Model
{
    use SoftDeletes;

    protected $table = 'fotos';

    protected $fillable = [
        'perfil_id',
        'ruta_foto',
        'fecha_subida',
        'permisos',
        'es_principal',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
        'es_principal' => 'boolean',
        'permisos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

    // Scopes
    public function scopePublicas($query)
    {
        return $query->whereJsonContains('permisos', 'publica');
    }

    public function scopePrivadas($query)
    {
        return $query->whereJsonContains('permisos', 'privada');
    }

    public function scopeSoloMatches($query)
    {
        return $query->whereJsonContains('permisos', 'solo_matches');
    }

    // Accesor para obtener URL completa
    public function getUrlAttribute()
    {
        if (filter_var($this->ruta_foto, FILTER_VALIDATE_URL)) {
            return $this->ruta_foto;
        }
        return asset('storage/' . $this->ruta_foto);
    }
}