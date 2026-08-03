<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use SoftDeletes;

    protected $table = 'eventos';

    protected $fillable = [
        'organizador_id',
        'nombre',
        'descripcion',
        'fecha',
        'hora',
        'ciudad',
        'zona_ubicacion',
        'ubicacion_lat',
        'ubicacion_lng',
        'precio',
        'capacidad',
        'tipo',
        'categoria',
        'codigo_vestimenta',
        'estado',
        'destacado',
        'imagen',
        'metadatos',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
        'precio' => 'decimal:2',
        'capacidad' => 'integer',
        'destacado' => 'boolean',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function organizador()
    {
        return $this->belongsTo(Administrador::class, 'organizador_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'evento_id');
    }

    // Scopes
    public function scopePublicados($query)
    {
        return $query->where('estado', 'publicado');
    }

    public function scopeDestacados($query)
    {
        return $query->where('destacado', true);
    }

    public function scopeProximos($query)
    {
        return $query->where('fecha', '>=', now()->toDateString());
    }

    public function scopeEnCiudad($query, $ciudad)
    {
        return $query->where('ciudad', $ciudad);
    }

    // Accesors
    public function getCuposDisponiblesAttribute()
    {
        if (!$this->capacidad) return 'Ilimitado';
        
        $reservados = $this->reservas()->where('estado', 'aprobada')->sum('asistentes');
        return $this->capacidad - $reservados;
    }

    public function getEstaCompletoAttribute()
    {
        if (!$this->capacidad) return false;
        return $this->cupos_disponibles <= 0;
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecha ? $this->fecha->format('d/m/Y') : null;
    }

    public function getHoraFormateadaAttribute()
    {
        return $this->hora ? $this->hora->format('H:i') : null;
    }

    public function getFechaCompletaAttribute()
    {
        if ($this->fecha && $this->hora) {
            return $this->fecha->format('d/m/Y') . ' ' . $this->hora->format('H:i');
        }
        return null;
    }
}