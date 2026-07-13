<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Administrador extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'administradores';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'esta_activo',
        'ultimo_acceso_en',
        'telefono',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'esta_activo' => 'boolean',
        'ultimo_acceso_en' => 'datetime',
        'email_verificado_en' => 'datetime',
    ];

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($administrador) {
            if (!isset($administrador->esta_activo)) {
                $administrador->esta_activo = true;
            }
        });
    }

    /**
     * Relaciones
     */
    public function codigosInvitacionCreados()
    {
        return $this->hasMany(CodigoInvitacion::class, 'creado_por_admin_id');
    }

    public function registrosAuditoria()
    {
        return $this->hasMany(RegistroAuditoria::class, 'admin_id');
    }

    /**
     * Métodos
     */
    public function registrarAcceso(string $ip): void
    {
        $this->update([
            'ultimo_acceso_en' => now(),
            'ultimo_acceso_ip' => $ip,
        ]);
    }

    public function estaActivo(): bool
    {
        return $this->esta_activo && $this->email_verificado_en !== null;
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('esta_activo', true)
                     ->whereNotNull('email_verificado_en');
    }

    /**
     * Mutador para encriptar contraseña automáticamente
     */
    public function setPasswordAttribute($valor)
    {
        if (!empty($valor)) {
            $this->attributes['password'] = bcrypt($valor);
        }
    }
}