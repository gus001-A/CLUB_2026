<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suscripcion extends Model
{
    use SoftDeletes;

    protected $table = 'suscripciones';

    protected $fillable = [
        'creador_id',
        'usuario_id',
        'plan',
        'precio',
        'fecha_inicio',
        'fecha_renovacion',
        'estado',
        'pago_id',
        'metadatos',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_inicio' => 'datetime',
        'fecha_renovacion' => 'datetime',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function creador()
    {
        return $this->belongsTo(Creador::class, 'creador_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    public function scopePorVencer($query, $dias = 7)
    {
        return $query->where('estado', 'activa')
                    ->where('fecha_renovacion', '<=', now()->addDays($dias));
    }

    // Accesors
    public function getEstaActivaAttribute()
    {
        return $this->estado === 'activa';
    }

    public function getDiasRestantesAttribute()
    {
        if (!$this->fecha_renovacion) return null;
        return now()->diffInDays($this->fecha_renovacion, false);
    }

    public function getVenceProntoAttribute()
    {
        $dias = $this->dias_restantes;
        return $dias !== null && $dias <= 7 && $dias > 0;
    }

    public function getPlanNombreAttribute()
    {
        $planes = [
            'mensual' => 'Mensual',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual',
        ];
        return $planes[$this->plan] ?? $this->plan;
    }
}