<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Llamada extends Model
{
    protected $table = 'llamadas';

    protected $fillable = [
        'chat_id',
        'llamante_id',
        'receptor_id',
        'tipo',
        'estado',
        'iniciada_en',
        'contestada_en',
        'finalizada_en',
        'duracion_segundos',
        'motivo_fin',
    ];

    protected $casts = [
        'iniciada_en' => 'datetime',
        'contestada_en' => 'datetime',
        'finalizada_en' => 'datetime',
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function llamante()
    {
        return $this->belongsTo(User::class, 'llamante_id');
    }

    public function receptor()
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }

    public function getDuracionFormateadaAttribute(): ?string
    {
        if ($this->duracion_segundos === null) {
            return null;
        }

        $minutos = intdiv($this->duracion_segundos, 60);
        $segundos = $this->duracion_segundos % 60;

        return sprintf('%d:%02d', $minutos, $segundos);
    }

    public function toChatPayload(): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'llamante_id' => $this->llamante_id,
            'receptor_id' => $this->receptor_id,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'iniciada_en' => $this->iniciada_en?->toIso8601String(),
            'contestada_en' => $this->contestada_en?->toIso8601String(),
            'finalizada_en' => $this->finalizada_en?->toIso8601String(),
            'duracion_segundos' => $this->duracion_segundos,
            'duracion_formateada' => $this->duracion_formateada,
            'motivo_fin' => $this->motivo_fin,
            'llamante' => [
                'id' => $this->llamante->id,
                'nombre' => $this->llamante->nombre_completo ?? $this->llamante->nombre,
                'avatar' => $this->llamante->avatar,
            ],
        ];
    }
}
