<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;

    protected $table = 'chats';

    protected $fillable = [
        'coincidencia_id',
        'estado',
        'ultimo_mensaje_en',
    ];

    protected $casts = [
        'ultimo_mensaje_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function coincidencia()
    {
        return $this->belongsTo(Coincidencia::class, 'coincidencia_id');
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'chat_id');
    }

    public function getUltimoMensajeAttribute()
    {
        return $this->mensajes()->latest()->first();
    }

    public function getMensajesNoLeidosAttribute()
    {
        return $this->mensajes()->where('leido', false)->where('remitente_id', '!=', auth()->id())->count();
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeConMensajes($query)
    {
        return $query->has('mensajes');
    }
}