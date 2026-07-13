<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use SoftDeletes;

    protected $table = 'reservas';

    protected $fillable = [
        'evento_id',
        'usuario_id',
        'folio',
        'asistentes',
        'tipo_acceso',
        'pago_id',
        'codigo_qr',
        'estado',
        'total',
        'metadatos',
    ];

    protected $casts = [
        'asistentes' => 'integer',
        'total' => 'decimal:2',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes
    public function scopeAprobadas($query)
    {
        return $query->where('estado', 'aprobada');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePorEvento($query, $eventoId)
    {
        return $query->where('evento_id', $eventoId);
    }

    // Accesors
    public function getEstaAprobadaAttribute()
    {
        return $this->estado === 'aprobada';
    }

    public function getPuedeUsarAttribute()
    {
        return $this->estado === 'aprobada' && !$this->trashed();
    }
}