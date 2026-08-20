<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Soporte extends Model
{
    use SoftDeletes;

    protected $table = 'soportes';

    protected $fillable = [
        'usuario_id',
        'administrador_id',
        'reporte_id',
        'asunto',
        'origen',
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
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function administrador()
    {
        return $this->belongsTo(Administrador::class, 'administrador_id');
    }

    public function reporte()
    {
        return $this->belongsTo(Reporte::class, 'reporte_id');
    }

    public function mensajes()
    {
        return $this->hasMany(MensajeSoporte::class, 'soporte_id');
    }

    public function getUltimoMensajeAttribute()
    {
        return $this->mensajes()->latest()->first();
    }

    public function getMensajesNoLeidosAttribute()
    {
        return $this->mensajes()->where('leido', false)->whereNotNull('usuario_id')->count();
    }

    // Scopes
    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'abierto');
    }

    public function scopeDeReporte($query)
    {
        return $query->where('origen', 'reporte');
    }
}