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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'email_verificado_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function perfil()
    {
        return $this->hasOne(Perfil::class, 'usuario_id');
    }

    public function creador()
    {
        return $this->hasOne(Creador::class, 'usuario_id');
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

    // Scopes útiles
    public function scopeVerificado($query)
    {
        return $query->where('estado', 'verificado');
    }

    public function scopeCreadores($query)
    {
        return $query->where('rol', 'creador');
    }

    // Accesor
    public function getEdadAttribute()
    {
        return $this->fecha_nacimiento ? $this->fecha_nacimiento->age : null;
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . ' (@' . $this->apodo . ')';
    }
}