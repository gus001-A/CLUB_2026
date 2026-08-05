<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publicacion extends Model
{
    use SoftDeletes;

    protected $table = 'publicaciones';

    protected $fillable = [
        'usuario_id',
        'texto',
        'imagen',
        'es_premium',
        'likes',
        'comentarios_count',
        'estado',
        'metadatos',
    ];

    protected $casts = [
        'es_premium' => 'boolean',
        'likes' => 'integer',
        'comentarios_count' => 'integer',
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'publicacion_id');
    }

    public function likes()
    {
        return $this->hasMany(LikePublicacion::class, 'publicacion_id');
    }

    // Scopes
    public function scopePublicados($query)
    {
        return $query->where('estado', 'publicado');
    }

    public function scopePremium($query)
    {
        return $query->where('es_premium', true);
    }

    public function scopeDestacados($query)
    {
        return $query->where('destacado', true);
    }

    // Accesor
    public function getTiempoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getUsuarioNombreAttribute()
    {
        return $this->usuario ? $this->usuario->nombre : 'Usuario desconocido';
    }

    public function getUsuarioAvatarAttribute()
    {
        if ($this->usuario && $this->usuario->perfil) {
            $foto = $this->usuario->perfil->fotoPrincipal;
            if ($foto) {
                return $foto->url;
            }
        }
        return '/images/shared/avatar-default.jpg';
    }

    public function getUsuarioVerificadoAttribute()
    {
        return $this->usuario && $this->usuario->estado === 'verificado';
    }

    public function getUsuarioRolAttribute()
    {
        if (!$this->usuario) return 'Usuario';
        return $this->usuario->rol === 'creador' ? 'Creador' : 'Usuario';
    }
}