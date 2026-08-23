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

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------
    public function coincidencia()
    {
        return $this->belongsTo(Coincidencia::class, 'coincidencia_id');
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'chat_id');
    }

    public function llamadas()
    {
        return $this->hasMany(Llamada::class, 'chat_id');
    }

    public function getUltimoMensajeAttribute()
    {
        return $this->mensajes()->latest()->first();
    }

    public function getMensajesNoLeidosAttribute()
    {
        return $this->mensajes()->where('leido', false)->where('remitente_id', '!=', auth()->id())->count();
    }

    /**
     * Devuelve al otro participante del chat (el que no es $userId),
     * a partir de la coincidencia (match) que originó este chat.
     * Se usa mucho tanto en el controlador como en la autorización del
     * canal privado de broadcasting.
     */
    public function otroParticipante(int $userId): ?User
    {
        $coincidencia = $this->coincidencia;
        if (!$coincidencia) {
            return null;
        }

        $otroId = $coincidencia->usuario_a_id === $userId
            ? $coincidencia->usuario_b_id
            : $coincidencia->usuario_a_id;

        return User::find($otroId);
    }

    public function tieneParticipante(int $userId): bool
    {
        $coincidencia = $this->coincidencia;
        if (!$coincidencia) {
            return false;
        }

        return $coincidencia->usuario_a_id === $userId || $coincidencia->usuario_b_id === $userId;
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------
    public function scopeActivo($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeConMensajes($query)
    {
        return $query->has('mensajes');
    }

    /**
     * Chats donde el usuario dado participa (vía su coincidencia).
     */
    public function scopeDeUsuario($query, int $userId)
    {
        return $query->whereHas('coincidencia', function ($q) use ($userId) {
            $q->where('usuario_a_id', $userId)->orWhere('usuario_b_id', $userId);
        });
    }
}
