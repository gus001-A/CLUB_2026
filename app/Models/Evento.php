<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Estado de transmisión calculado con fecha + hora (no confundir con
     * la columna 'estado', que es de publicación: borrador/publicado/etc).
     *
     * NOTA: asumo una duración de evento de 3 horas porque no hay columna
     * de duración/fin en la tabla. Si tus eventos duran distinto, ajusta
     * $duracionHoras o agrega una columna 'duracion_horas' / 'fecha_fin'.
     */
    public function getEstadoActualAttribute(): string
    {
        if (!$this->fecha || !$this->hora) {
            return 'programado';
        }

        $duracionHoras = 3;

        $inicio = Carbon::parse($this->fecha->format('Y-m-d') . ' ' . $this->hora->format('H:i'));
        $fin = $inicio->copy()->addHours($duracionHoras);

        return now()->between($inicio, $fin) ? 'en_vivo' : 'programado';
    }

    /**
     * Obtener la URL completa de la imagen del evento
     * 
     * @return string|null
     */
    public function getImagenUrlAttribute(): ?string
    {
        if (!$this->imagen) {
            return null;
        }

        // Si la imagen ya es una URL completa, devolverla
        if (filter_var($this->imagen, FILTER_VALIDATE_URL)) {
            return $this->imagen;
        }

        // Si está en el storage, generar la URL
        if (Storage::disk('public')->exists($this->imagen)) {
            return Storage::url($this->imagen);
        }

        // Si está en un subdirectorio específico de eventos
        if (Storage::disk('public')->exists('eventos/' . $this->imagen)) {
            return Storage::url('eventos/' . $this->imagen);
        }

        return null;
    }

    /**
     * Obtener la ruta completa de la imagen en el storage
     * 
     * @return string|null
     */
    public function getImagenPathAttribute(): ?string
    {
        if (!$this->imagen) {
            return null;
        }

        if (Storage::disk('public')->exists($this->imagen)) {
            return Storage::disk('public')->path($this->imagen);
        }

        if (Storage::disk('public')->exists('eventos/' . $this->imagen)) {
            return Storage::disk('public')->path('eventos/' . $this->imagen);
        }

        return null;
    }

    /**
     * Verificar si el evento tiene una imagen
     * 
     * @return bool
     */
    public function hasImagen(): bool
    {
        return $this->imagen !== null && 
               (Storage::disk('public')->exists($this->imagen) || 
                Storage::disk('public')->exists('eventos/' . $this->imagen));
    }

    /**
     * Eliminar la imagen del storage
     * 
     * @return bool
     */
    public function deleteImagen(): bool
    {
        if (!$this->imagen) {
            return false;
        }

        $deleted = false;

        if (Storage::disk('public')->exists($this->imagen)) {
            $deleted = Storage::disk('public')->delete($this->imagen);
        }

        if (Storage::disk('public')->exists('eventos/' . $this->imagen)) {
            $deleted = Storage::disk('public')->delete('eventos/' . $this->imagen);
        }

        if ($deleted) {
            $this->imagen = null;
            $this->save();
        }

        return $deleted;
    }

    // Mutator: guarda la ciudad siempre en mayúsculas, sin importar cómo la escriban
    public function setCiudadAttribute($value)
    {
        $this->attributes['ciudad'] = $value ? mb_strtoupper($value, 'UTF-8') : $value;
    }

    // Mutator: mismo criterio que ciudad, la zona/lugar exacto también en mayúsculas
    public function setZonaUbicacionAttribute($value)
    {
        $this->attributes['zona_ubicacion'] = $value ? mb_strtoupper($value, 'UTF-8') : $value;
    }

    /**
     * Boot del modelo para manejar eventos de eliminar
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($evento) {
            // Si es eliminación permanente, borrar la imagen
            if ($evento->isForceDeleting()) {
                $evento->deleteImagen();
            }
        });

        static::forceDeleted(function ($evento) {
            // Asegurar que la imagen se borre en force delete
            $evento->deleteImagen();
        });
    }
}