<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un "me gusta" de un usuario sobre un contenido. La combinación
 * (contenido_id, usuario_id) es única a nivel de base de datos — así
 * el conteo de likes de un Contenido siempre es simplemente
 * $contenido->likes()->count(), sin riesgo de likes duplicados.
 */
class Like extends Model
{
    protected $table = 'likes';

    protected $fillable = [
        'contenido_id',
        'usuario_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
