<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contenido extends Model
{
    use SoftDeletes;

    protected $table = 'contenidos';

    protected $fillable = [
        'creador_id',
        'tipo',
        'titulo',
        'descripcion',
        'archivos',
        'precio',
        'visibilidad',
        'estado',
        'etiquetas',
        'programado_en',
        'es_premium',
        'metadatos',
    ];

    protected $casts = [
        'archivos' => 'array',
        'precio' => 'decimal:2',
        'etiquetas' => 'array',
        'es_premium' => 'boolean',
        'programado_en' => 'datetime',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function creador()
    {
        return $this->belongsTo(Creador::class, 'creador_id');
    }

    public function interacciones()
    {
        return $this->hasMany(Interaccion::class, 'contenido_id');
    }

    // Scopes
    public function scopePublicados($query)
    {
        return $query->where('estado', 'publicado');
    }

    public function scopePremium($query)
    {
        return $query->where('es_premium', true);
    }

    public function scopePublico($query)
    {
        return $query->where('visibilidad', 'publico');
    }

    public function scopeParaSuscriptores($query)
    {
        return $query->where('visibilidad', 'suscriptores');
    }

    // Accesors
    public function getEstaPublicadoAttribute()
    {
        return $this->estado === 'publicado';
    }

    public function getEsGratisAttribute()
    {
        return $this->precio == 0;
    }

    public function getTotalLikesAttribute()
    {
        return $this->interacciones()->where('tipo', 'like')->count();
    }

    public function getTotalComentariosAttribute()
    {
        return $this->interacciones()->where('tipo', 'comentario')->count();
    }

    public function getTotalVistasAttribute()
    {
        return $this->interacciones()->where('tipo', 'vista')->count();
    }
}