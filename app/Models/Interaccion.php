<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interaccion extends Model
{
    use SoftDeletes;

    protected $table = 'interacciones';

    protected $fillable = [
        'contenido_id',
        'usuario_id',
        'tipo',
        'comentario',
        'metadatos',
        'publicacion_id', // Para diferenciar si es de una publicación
    ];

    protected $casts = [
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function publicacion()
    {
        return $this->belongsTo(Publicacion::class, 'publicacion_id');
    }

    // Scopes
    public function scopeLikes($query)
    {
        return $query->where('tipo', 'like');
    }

    public function scopeComentarios($query)
    {
        return $query->where('tipo', 'comentario');
    }

    public function scopeVistas($query)
    {
        return $query->where('tipo', 'vista');
    }

    public function scopeCompartidos($query)
    {
        return $query->where('tipo', 'compartir');
    }

    public function scopeDePublicacion($query, $publicacionId)
    {
        return $query->where('publicacion_id', $publicacionId);
    }

    // Accesors
    public function getTipoNombreAttribute()
    {
        $tipos = [
            'like' => 'Me gusta',
            'comentario' => 'Comentario',
            'compartir' => 'Compartir',
            'vista' => 'Vista',
        ];
        return $tipos[$this->tipo] ?? $this->tipo;
    }

    public function getEsLikeAttribute()
    {
        return $this->tipo === 'like';
    }

    public function getEsComentarioAttribute()
    {
        return $this->tipo === 'comentario';
    }

    public function getEsVistaAttribute()
    {
        return $this->tipo === 'vista';
    }

    public function getEsCompartidoAttribute()
    {
        return $this->tipo === 'compartir';
    }

    // Helpers para el frontend
    public function toArrayForFeed()
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'tipo_nombre' => $this->tipo_nombre,
            'comentario' => $this->comentario,
            'metadatos' => $this->metadatos,
            'created_at' => $this->created_at->toIso8601String(),
            'usuario' => [
                'id' => $this->usuario->id,
                'nombre' => $this->usuario->nombre ?? $this->usuario->name,
                'avatar' => $this->usuario->avatar,
            ]
        ];
    }
}