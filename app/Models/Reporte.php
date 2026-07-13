<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporte extends Model
{
    use SoftDeletes;

    protected $table = 'reportes';

    protected $fillable = [
        'reporta_id',
        'reportado_id',
        'reportable_type',
        'reportable_id',
        'tipo',
        'descripcion',
        'estado',
        'metadatos',
    ];

    protected $casts = [
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones polimórficas
    public function reportable()
    {
        return $this->morphTo();
    }

    public function reporta()
    {
        return $this->belongsTo(User::class, 'reporta_id');
    }

    public function reportado()
    {
        return $this->belongsTo(User::class, 'reportado_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Accesors
    public function getEstadoNombreAttribute()
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'revisado' => 'Revisado',
            'resuelto' => 'Resuelto',
        ];
        return $estados[$this->estado] ?? $this->estado;
    }

    public function getTipoNombreAttribute()
    {
        $tipos = [
            'spam' => 'Spam',
            'inapropiado' => 'Contenido inapropiado',
            'falso' => 'Perfil falso',
            'acoso' => 'Acoso',
            'otro' => 'Otro',
        ];
        return $tipos[$this->tipo] ?? $this->tipo;
    }

    public function getEstaPendienteAttribute()
    {
        return $this->estado === 'pendiente';
    }
}