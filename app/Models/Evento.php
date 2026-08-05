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
        return $this->belongsTo(User::class, 'organizador_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'evento_id');
    }

    // ========== NUEVAS RELACIONES CON FotosEvento ==========
    public function fotos()
    {
        return $this->hasMany(FotosEvento::class, 'evento_id');
    }

    public function fotosRecientes()
    {
        return $this->hasMany(FotosEvento::class, 'evento_id')
                    ->orderBy('fecha_subida', 'desc');
    }

    public function fotoPrincipal()
    {
        return $this->hasOne(FotosEvento::class, 'evento_id')
                    ->orderBy('fecha_subida', 'asc');
    }

    public function fotosAprobadas()
    {
        return $this->hasMany(FotosEvento::class, 'evento_id')
                    ->where('estado', 'aprobada');
    }
    // ========== FIN NUEVAS RELACIONES ==========

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

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopeConCapacidadDisponible($query)
    {
        return $query->where(function($q) {
            $q->whereNull('capacidad')
              ->orWhere('capacidad', '>', 0);
        });
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

    // ========== NUEVOS ACCESORS PARA FOTOS ==========
    public function getCantidadFotosAttribute()
    {
        return $this->fotos()->count();
    }

    public function getTieneFotosAttribute()
    {
        return $this->fotos()->exists();
    }

    public function getUrlFotoPrincipalAttribute()
    {
        $fotoPrincipal = $this->fotoPrincipal;
        if ($fotoPrincipal) {
            return asset('storage/' . $fotoPrincipal->ruta);
        }
        return null;
    }

    public function getFotosRecientesUrlAttribute()
    {
        return $this->fotosRecientes->map(function($foto) {
            return [
                'id' => $foto->id,
                'url' => asset('storage/' . $foto->ruta),
                'nombre' => $foto->nombre_imagen,
                'fecha_subida' => $foto->fecha_subida_formateada
            ];
        });
    }
    // ========== FIN NUEVOS ACCESORS ==========

    // ========== MUTATORS (opcionales) ==========
    public function setMetadatosAttribute($value)
    {
        $this->attributes['metadatos'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setPrecioAttribute($value)
    {
        $this->attributes['precio'] = str_replace(',', '.', str_replace('.', '', $value));
    }
    // ========== FIN MUTATORS ==========

    // ========== MÉTODOS ADICIONALES PARA FOTOS ==========
    public function agregarFoto($nombreImagen, $ruta, $usuarioId)
    {
        return $this->fotos()->create([
            'nombre_imagen' => $nombreImagen,
            'ruta' => $ruta,
            'usuario_subio' => $usuarioId,
            'fecha_subida' => now(),
        ]);
    }

    public function eliminarFoto($fotoId)
    {
        $foto = $this->fotos()->find($fotoId);
        if ($foto) {
            // Eliminar archivo físico si existe
            if (file_exists(storage_path('app/public/' . $foto->ruta))) {
                unlink(storage_path('app/public/' . $foto->ruta));
            }
            return $foto->delete();
        }
        return false;
    }

    public function eliminarTodasFotos()
    {
        foreach ($this->fotos as $foto) {
            // Eliminar archivos físicos
            if (file_exists(storage_path('app/public/' . $foto->ruta))) {
                unlink(storage_path('app/public/' . $foto->ruta));
            }
        }
        return $this->fotos()->delete();
    }
    // ========== FIN MÉTODOS ADICIONALES ==========
}