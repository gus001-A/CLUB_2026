<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matches extends Model
{
    use SoftDeletes;

    protected $table = 'coincidencias';

    protected $fillable = [
        'usuario_a_id',
        'usuario_b_id',
        'compatibilidad',
        'estado',
        'origen',
        'coincidio_en',
    ];

    protected $casts = [
        'compatibilidad' => 'integer',
        'coincidio_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function usuarioA()
    {
        return $this->belongsTo(User::class, 'usuario_a_id');
    }

    public function usuarioB()
    {
        return $this->belongsTo(User::class, 'usuario_b_id');
    }

    public function chat()
    {
        return $this->hasOne(Chat::class, 'coincidencia_id');
    }

    // Scopes
    public function scopeCoincidenciasActivas($query)
    {
        return $query->where('estado', 'coincidencia');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    // Accesor
    public function getEsCoincidenciaAttribute()
    {
        return $this->estado === 'coincidencia';
    }

    public function getUsuariosAttribute()
    {
        return [
            'usuario_a' => $this->usuarioA,
            'usuario_b' => $this->usuarioB,
        ];
    }
}