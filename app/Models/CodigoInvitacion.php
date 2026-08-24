<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class CodigoInvitacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'codigos_invitacion';

    protected $fillable = [
        'codigo',
        'email',
        'nombre_destinatario',
        'usado_por_usuario_id',
        'usado_en',
        'expira_en',
        'usos_maximos',
        'contador_usos',
        'creado_por_admin_id',
        'esta_activo',
        'notas',
        'metadata',
        'fecha_envio',
        'fecha_recordatorio',
    ];

    protected $casts = [
        'usado_en' => 'datetime',
        'expira_en' => 'datetime',
        'esta_activo' => 'boolean',
        'usos_maximos' => 'integer',
        'contador_usos' => 'integer',
        'metadata' => 'array',
        'fecha_envio' => 'datetime',
        'fecha_recordatorio' => 'datetime',
    ];

    protected $hidden = [
        'creado_por_admin_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($codigoInvitacion) {
            if (empty($codigoInvitacion->codigo)) {
                $codigoInvitacion->codigo = self::generarCodigoUnico();
            }
            if (empty($codigoInvitacion->expira_en)) {
                $codigoInvitacion->expira_en = Carbon::now()->addDays(30);
            }
            if (empty($codigoInvitacion->usos_maximos)) {
                $codigoInvitacion->usos_maximos = 1;
            }
            if (!isset($codigoInvitacion->esta_activo)) {
                $codigoInvitacion->esta_activo = true;
            }
            // OJO: sin este default, contador_usos nace en NULL (si la
            // migración no le puso ->default(0)) y las comparaciones
            // whereColumn('contador_usos', '<', 'usos_maximos') devuelven
            // NULL en SQL (ni true ni false) — la invitación recién creada
            // queda invisible para el registro público y nadie puede
            // usarla. Por eso lo forzamos aquí, sin depender de la BD.
            if (!isset($codigoInvitacion->contador_usos)) {
                $codigoInvitacion->contador_usos = 0;
            }
        });
    }

    public static function generarCodigoUnico(): string
    {
        $prefijo = 'CF';
        $longitud = 10;

        do {
            $codigo = $prefijo . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $longitud));
        } while (self::where('codigo', $codigo)->exists());

        return $codigo;
    }

    // Relaciones - Compatibles con tu modelo User
    public function usadoPorUsuario()
    {
        return $this->belongsTo(User::class, 'usado_por_usuario_id');
    }

    public function creadoPorAdmin()
    {
        return $this->belongsTo(Administrador::class, 'creado_por_admin_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'codigo_invitacion_id');
    }

    public function historial()
    {
        return $this->hasMany(HistorialCodigoInvitacion::class);
    }

    // Métodos de validación
    public function esValido(): bool
    {
        return $this->esta_activo &&
            !$this->estaExpirado() &&
            !$this->estaUsado() &&
            !$this->haAlcanzadoUsosMaximos();
    }

    public function estaExpirado(): bool
    {
        return $this->expira_en && Carbon::now()->greaterThan($this->expira_en);
    }

    public function estaUsado(): bool
    {
        return $this->usado_en !== null && $this->usos_maximos === 1;
    }

    public function haAlcanzadoUsosMaximos(): bool
    {
        return $this->contador_usos >= $this->usos_maximos;
    }

    public function marcarComoUsado(int $usuarioId): bool
    {
        if (!$this->esValido()) {
            return false;
        }

        $this->contador_usos++;
        $this->usado_por_usuario_id = $usuarioId;
        
        if ($this->contador_usos >= $this->usos_maximos) {
            $this->usado_en = Carbon::now();
            $this->esta_activo = false;
        }

        return $this->save();
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('esta_activo', true)
                     ->where(function ($q) {
                         $q->whereNull('expira_en')
                           ->orWhere('expira_en', '>', Carbon::now());
                     });
    }

    public function scopeDisponibles($query)
    {
        return $query->activos()
                     ->where(function ($q) {
                         $q->whereNull('usado_en')
                           ->orWhereColumn('contador_usos', '<', 'usos_maximos');
                     });
    }

    // Accesores
    public function getDiasRestantesAttribute(): ?int
    {
        if (!$this->expira_en) {
            return null;
        }

        $diff = Carbon::now()->diffInDays($this->expira_en, false);
        return $diff > 0 ? $diff : 0;
    }

    public function getEtiquetaEstadoAttribute(): string
    {
        if (!$this->esta_activo) {
            return 'Inactivo';
        }
        if ($this->estaExpirado()) {
            return 'Expirado';
        }
        if ($this->estaUsado()) {
            return 'Usado';
        }
        if ($this->haAlcanzadoUsosMaximos()) {
            return 'Agotado';
        }
        return 'Disponible';
    }

    public function getPorcentajeUsoAttribute(): float
    {
        if ($this->usos_maximos <= 0) {
            return 0;
        }
        return round(($this->contador_usos / $this->usos_maximos) * 100, 2);
    }

    public function getEstadoColorAttribute(): array
    {
        $estado = $this->etiqueta_estado;
        $colores = [
            'Disponible' => ['color' => 'success', 'icono' => 'pi-check-circle'],
            'Usado' => ['color' => 'info', 'icono' => 'pi-check'],
            'Expirado' => ['color' => 'danger', 'icono' => 'pi-times-circle'],
            'Agotado' => ['color' => 'warning', 'icono' => 'pi-exclamation-triangle'],
            'Inactivo' => ['color' => 'secondary', 'icono' => 'pi-ban'],
        ];

        return $colores[$estado] ?? ['color' => 'secondary', 'icono' => 'pi-circle'];
    }
}