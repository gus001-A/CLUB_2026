<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mensaje extends Model
{
    use SoftDeletes;

    protected $table = 'mensajes';

    protected $fillable = [
        'chat_id',
        'remitente_id',
        'texto',
        'archivos_adjuntos',
        'leido',
        'leido_en',
        'estado',
    ];

    protected $casts = [
        'archivos_adjuntos' => 'array',
        'leido' => 'boolean',
        'leido_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function remitente()
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    // Scopes
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    public function scopeLeidos($query)
    {
        return $query->where('leido', true);
    }

    // Accesor
    public function getEsRemitenteAttribute()
    {
        return auth()->check() && $this->remitente_id === auth()->id();
    }

    public function getTiempoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}