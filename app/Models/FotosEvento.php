<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FotosEvento extends Model
{
    use SoftDeletes;

    protected $table = 'fotos_eventos';

    protected $fillable = [
        'evento_id',
        'nombre_imagen',
        'ruta',
        'usuario_subio',
        'fecha_subida',
    ];

    protected $casts = [
        'fecha_subida' => 'datetime',
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
        return $this->belongsTo(User::class, 'usuario_subio');
    }

    // Scopes
    public function scopeDelEvento($query, $eventoId)
    {
        return $query->where('evento_id', $eventoId);
    }

    public function scopeSubidasPor($query, $usuarioId)
    {
        return $query->where('usuario_subio', $usuarioId);
    }

    public function scopeOrdenadasPorFecha($query, $direccion = 'desc')
    {
        return $query->orderBy('fecha_subida', $direccion);
    }

    // Accesors
    public function getUrlCompletaAttribute()
    {
        return asset('storage/' . $this->ruta);
    }

    public function getNombreImagenConExtensionAttribute()
    {
        return $this->nombre_imagen;
    }

    public function getFechaSubidaFormateadaAttribute()
    {
        return $this->fecha_subida ? $this->fecha_subida->format('d/m/Y H:i') : null;
    }
}  