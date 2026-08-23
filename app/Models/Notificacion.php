<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notificacion extends Model
{
    use SoftDeletes;

    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_id',
        'emisor_id',
        'tipo',
        'mensaje',
        'contenido_id',
        'link',
        'leida',
        'leida_en',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'leida_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function emisor()
    {
        return $this->belongsTo(User::class, 'emisor_id');
    }

    public function contenido()
    {
        return $this->belongsTo(Contenido::class, 'contenido_id');
    }

    // Scopes
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopeLeidas($query)
    {
        return $query->where('leida', true);
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Forma "plana" lista para JSON — coincide EXACTAMENTE con lo que ya
     * espera AppLayout.vue (id, tipo, mensaje, leida, created_at,
     * contenido_id, y 'usuario_id' representando al EMISOR, no al
     * destinatario — así clickNotificacion() puede hacer
     * router.visit(`/creador/${notificacion.usuario_id}`) para el caso
     * 'seguidor' sin tener que tocar el frontend).
     */
    public function toFeedPayload(): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'mensaje' => $this->mensaje,
            'leida' => $this->leida,
            'created_at' => $this->created_at->toIso8601String(),
            'contenido_id' => $this->contenido_id,
            'usuario_id' => $this->emisor_id,
            'link' => $this->link,
        ];
    }

    /**
     * Punto de entrada único para crear notificaciones desde cualquier
     * controlador — evita duplicar la lógica de "no notificarme a mí
     * mismo" en cada sitio donde se dispara una notificación.
     *
     * Uso típico:
     *   Notificacion::crear(
     *       usuarioId: $publicacion->usuario_id,
     *       emisorId: $user->id,
     *       tipo: 'like',
     *       mensaje: "<strong>{$user->nombre}</strong> le dio like a tu publicación",
     *       contenidoId: $publicacion->id,
     *   );
     */
    public static function crear(
        int $usuarioId,
        ?int $emisorId,
        string $tipo,
        string $mensaje,
        ?int $contenidoId = null,
        ?string $link = null
    ): ?self {
        // Nunca notificarte a ti mismo (ej. dar like a tu propia publicación).
        if ($emisorId !== null && $emisorId === $usuarioId) {
            return null;
        }

        return self::create([
            'usuario_id' => $usuarioId,
            'emisor_id' => $emisorId,
            'tipo' => $tipo,
            'mensaje' => $mensaje,
            'contenido_id' => $contenidoId,
            'link' => $link,
        ]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarComoLeida(): void
    {
        $this->update([
            'leida' => true,
            'leida_en' => now(),
        ]);
    }

    /**
     * Marcar todas las notificaciones de un usuario como leídas
     */
    public static function marcarTodasComoLeidas(int $usuarioId): void
    {
        self::where('usuario_id', $usuarioId)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_en' => now(),
            ]);
    }
}