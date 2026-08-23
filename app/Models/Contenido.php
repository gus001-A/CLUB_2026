<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contenido extends Model
{
    use SoftDeletes;

    protected $table = 'contenidos';

    protected $fillable = [
        'creador_id',
        'tipo',
        'titulo',
        'categoria',
        'descripcion',
        'archivos',
        'precio',
        'visibilidad',
        'estado',
        'etiquetas',
        'programado_en',
        'es_premium',
        'metadatos',
    ];

    protected $casts = [
        'archivos' => 'array',
        'precio' => 'decimal:2',
        'etiquetas' => 'array',
        'es_premium' => 'boolean',
        'programado_en' => 'datetime',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------
    public function creador()
    {
        return $this->belongsTo(Creador::class, 'creador_id');
    }

    // Comentarios específicos del contenido (para creadores)
    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'contenido_id')
            ->whereNull('parent_id') // Solo comentarios principales
            ->orderBy('created_at', 'desc');
    }

    // Todas las interacciones (likes, comentarios de publicaciones, vistas, etc)
    public function interacciones()
    {
        return $this->hasMany(Interaccion::class, 'contenido_id');
    }

    // Likes (desde Interaccion)
    public function likes()
    {
        return $this->hasMany(Interaccion::class, 'contenido_id')
            ->where('tipo', 'like');
    }

    // Vistas (desde Interaccion)
    public function vistas()
    {
        return $this->hasMany(Interaccion::class, 'contenido_id')
            ->where('tipo', 'vista');
    }

    // Comentarios de publicaciones (desde Interaccion)
    public function interaccionesComentarios()
    {
        return $this->hasMany(Interaccion::class, 'contenido_id')
            ->where('tipo', 'comentario');
    }

    // ---------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------
    public function scopePublicados($query)
    {
        return $query->where('estado', 'publicado');
    }

    public function scopePremium($query)
    {
        return $query->where('es_premium', true);
    }

    public function scopePublico($query)
    {
        return $query->where('visibilidad', 'publico');
    }

    public function scopeParaSuscriptores($query)
    {
        return $query->where('visibilidad', 'suscriptores');
    }

    // ---------------------------------------------------------------
    // Accesores
    // ---------------------------------------------------------------
    public function getEstaPublicadoAttribute()
    {
        return $this->estado === 'publicado';
    }

    public function getEsGratisAttribute()
    {
        return $this->precio == 0;
    }

    // Total de likes (desde Interaccion)
    public function getTotalLikesAttribute()
    {
        return $this->likes()->count();
    }

    // Total de comentarios (desde Comentario - modelo dedicado)
    public function getTotalComentariosAttribute()
    {
        return $this->comentarios()->count();
    }

    // Total de vistas (desde Interaccion)
    public function getTotalVistasAttribute()
    {
        return $this->interacciones()->where('tipo', 'vista')->count();
    }

    // Total de compartidos (desde Interaccion)
    public function getTotalCompartidosAttribute()
    {
        return $this->interacciones()->where('tipo', 'compartir')->count();
    }

    /**
     * ¿El usuario dado ya le dio like a este contenido?
     */
    public function tieneLikeDe(?int $usuarioId): bool
    {
        if (!$usuarioId) {
            return false;
        }

        return $this->likes()->where('usuario_id', $usuarioId)->exists();
    }

    /**
     * Regla central de acceso a contenido premium
     */
    public function usuarioTieneAcceso(?User $usuario): bool
    {
        if (!$this->es_premium) {
            return true;
        }

        if (!$usuario) {
            return false;
        }

        if ($this->creador && $this->creador->usuario_id === $usuario->id) {
            return true;
        }

        return Suscripcion::where('usuario_id', $usuario->id)
            ->where('creador_id', $this->creador_id)
            ->where('estado', 'activa')
            ->exists();
    }
}