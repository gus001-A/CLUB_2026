<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Mensaje extends Model
{
    use SoftDeletes;

    protected $table = 'mensajes';

    protected $fillable = [
        'chat_id',
        'remitente_id',
        'texto',
        'tipo',
        'archivos_adjuntos',
        'archivo_path',
        'archivo_nombre_original',
        'archivo_tamano_bytes',
        'duracion_segundos',
        'miniatura_path',
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

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------
    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function remitente()
    {
        return $this->belongsTo(User::class, 'remitente_id');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------
    public function scopeNoLeidos($query)
    {
        return $query->where('leido', false);
    }

    public function scopeLeidos($query)
    {
        return $query->where('leido', true);
    }

    public function scopeMultimedia($query)
    {
        return $query->whereIn('tipo', ['imagen', 'video', 'audio']);
    }

    // ---------------------------------------------------------------
    // Accesores
    // ---------------------------------------------------------------
    public function getEsRemitenteAttribute()
    {
        return auth()->check() && $this->remitente_id === auth()->id();
    }

    public function getTiempoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getEsMultimediaAttribute(): bool
    {
        return in_array($this->tipo, ['imagen', 'video', 'audio']);
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo_path ? Storage::disk('public')->url($this->archivo_path) : null;
    }

    public function getMiniaturaUrlAttribute(): ?string
    {
        return $this->miniatura_path ? Storage::disk('public')->url($this->miniatura_path) : null;
    }

    /**
     * Duración formateada "0:12", útil para pintar el reproductor de video
     * sin tener que hacer el cálculo en el frontend.
     */
    public function getDuracionFormateadaAttribute(): ?string
    {
        if ($this->duracion_segundos === null) {
            return null;
        }

        $minutos = intdiv($this->duracion_segundos, 60);
        $segundos = $this->duracion_segundos % 60;

        return sprintf('%d:%02d', $minutos, $segundos);
    }

    /**
     * Forma "plana" lista para mandar por Inertia/JSON al frontend,
     * usada tanto por el controlador como por los eventos de broadcasting
     * para que el payload sea siempre idéntico.
     */
    public function toChatPayload(): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'remitente_id' => $this->remitente_id,
            'texto' => $this->texto,
            'tipo' => $this->tipo,
            'archivo_url' => $this->archivo_url,
            'archivo_nombre_original' => $this->archivo_nombre_original,
            'duracion_segundos' => $this->duracion_segundos,
            'duracion_formateada' => $this->duracion_formateada,
            'miniatura_url' => $this->miniatura_url,
            'leido' => $this->leido,
            'leido_en' => $this->leido_en?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'tiempo' => $this->tiempo,
            'remitente' => [
                'id' => $this->remitente->id,
                'nombre' => $this->remitente->nombre_completo ?? $this->remitente->nombre,
                'avatar' => $this->remitente->avatar,
            ],
        ];
    }
}
