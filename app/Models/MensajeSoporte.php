<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MensajeSoporte extends Model
{
    use SoftDeletes;

    protected $table = 'mensajes_soporte';

    protected $fillable = [
        'soporte_id',
        'usuario_id',
        'administrador_id',
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
    public function soporte()
    {
        return $this->belongsTo(Soporte::class, 'soporte_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'administrador_id');
    }

    // Scopes
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    public function scopeDeUsuario($query)
    {
        return $query->whereNotNull('usuario_id');
    }

    public function scopeDeAdmin($query)
    {
        return $query->whereNotNull('administrador_id');
    }

    // Accesores
    public function getEsAdminAttribute()
    {
        return ! is_null($this->administrador_id);
    }

    public function getTiempoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}