<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'nombre',
        'apodo',
        'email',
        'password',
        'telefono',
        'ciudad',
        'fecha_nacimiento',
        'rol',
        'estado',
        'codigo_invitacion',
        'email_verificado_en',
        'last_activity_at',
        'foto_principal',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'email_verificado_en' => 'datetime',
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ============================================================
    // RELACIONES
    // ============================================================

    public function perfil()
    {
        return $this->hasOne(Perfil::class, 'usuario_id');
    }

    public function creador()
    {
        return $this->hasOne(Creador::class, 'usuario_id');
    }

    public function publicaciones()
    {
        return $this->hasMany(Publicacion::class, 'usuario_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'usuario_id');
    }

    public function likes()
    {
        return $this->hasMany(LikePublicacion::class, 'usuario_id');
    }

    public function coincidenciasComoA()
    {
        return $this->hasMany(Coincidencia::class, 'usuario_a_id');
    }

    public function coincidenciasComoB()
    {
        return $this->hasMany(Coincidencia::class, 'usuario_b_id');
    }

    public function mensajesEnviados()
    {
        return $this->hasMany(Mensaje::class, 'remitente_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'usuario_id');
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'usuario_id');
    }

    public function suscriptores()
    {
        return $this->belongsToMany(User::class, 'suscripciones', 'usuario_id', 'creador_id')
            ->wherePivot('estado', 'activa');
    }

    public function suscriptoresDeMi()
    {
        return $this->belongsToMany(User::class, 'suscripciones', 'creador_id', 'usuario_id')
            ->wherePivot('estado', 'activa');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'usuario_id');
    }

    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'usuario_id');
    }

    public function reportesHechos()
    {
        return $this->hasMany(Reporte::class, 'reporta_id');
    }

    public function reportesRecibidos()
    {
        return $this->hasMany(Reporte::class, 'reportado_id');
    }

    public function interacciones()
    {
        return $this->hasMany(Interaccion::class, 'usuario_id');
    }

    public function eventos()
    {
        return $this->hasMany(Evento::class, 'organizador_id');
    }

    public function reservasEventos()
    {
        return $this->hasMany(Reserva::class, 'usuario_id');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeVerificado($query)
    {
        return $query->where('estado', 'verificado');
    }

    public function scopeCreadores($query)
    {
        return $query->where('rol', 'creador');
    }

    public function scopeActivos($query)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes(15));
    }

    // ============================================================
    // ACCESORS
    // ============================================================

    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' (@' . $this->apodo . ')';
    }

    public function getEstaActivoAttribute()
    {
        if (!$this->last_activity_at) {
            return false;
        }
        return $this->last_activity_at->diffInMinutes(now()) < 15;
    }

    /**
     * 🔥 ACCESOR PARA OBTENER EL AVATAR
     * Primero intenta usar foto_principal, si no, busca en el perfil
     */
    public function getAvatarAttribute()
    {
        // 1. Si tiene foto_principal guardada, usarla
        if ($this->foto_principal && !empty($this->foto_principal)) {
            return $this->foto_principal;
        }

        // 2. Si no, buscar en el perfil
        if ($this->perfil) {
            $fotoPrincipal = $this->perfil->fotoPrincipal;
            if ($fotoPrincipal) {
                if (is_object($fotoPrincipal) && isset($fotoPrincipal->url)) {
                    // Guardar en foto_principal para futuras consultas
                    $this->update(['foto_principal' => $fotoPrincipal->url]);
                    return $fotoPrincipal->url;
                }
                if (is_array($fotoPrincipal) && isset($fotoPrincipal['url'])) {
                    $this->update(['foto_principal' => $fotoPrincipal['url']]);
                    return $fotoPrincipal['url'];
                }
                if (is_string($fotoPrincipal)) {
                    $this->update(['foto_principal' => $fotoPrincipal]);
                    return $fotoPrincipal;
                }
            }
        }

        return '/images/shared/avatar-default.jpg';
    }
}