<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comentario extends Model
{
    use SoftDeletes;

    protected $table = 'comentarios';

    protected $fillable = [
        'contenido_id',
        'usuario_id',
        'texto',
        'parent_id', // Para comentarios anidados si lo necesitas
    ];

    protected $casts = [
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

    public function respuestas()
    {
        return $this->hasMany(Comentario::class, 'parent_id');
    }

    public function padre()
    {
        return $this->belongsTo(Comentario::class, 'parent_id');
    }

    // Accesors
    public function getTiempoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // Scopes
    public function scopePrincipales($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Formato para el frontend
     */
    public function toFeedPayload(): array
    {
        $data = [
            'id' => $this->id,
            'texto' => $this->texto,
            'tiempo' => $this->tiempo,
            'created_at' => $this->created_at->toIso8601String(),
            'usuario' => [
                'id' => $this->usuario->id,
                'nombre' => $this->usuario->nombre ?? $this->usuario->name,
                'avatar' => $this->usuario->avatar,
            ],
        ];

        if ($this->respuestas && $this->respuestas->count() > 0) {
            $data['respuestas'] = $this->respuestas->map(function ($respuesta) {
                return $respuesta->toFeedPayload();
            });
        }

        return $data;
    }
}