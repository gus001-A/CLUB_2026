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
}