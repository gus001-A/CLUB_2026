<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reporte extends Model
{
    use SoftDeletes;

    protected $table = 'reportes';

    protected $fillable = [
        'reporta_id',
        'reportado_id',
        'reportable_type',
        'reportable_id',
        'tipo',
        'descripcion',
        'estado',
        'atendido_por_admin_id',
        'metadatos',
    ];

    protected $casts = [
        'metadatos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones polimórficas
    public function reportable()
    {
        return $this->morphTo();
    }

    public function reporta()
    {
        return $this->belongsTo(User::class, 'reporta_id');
    }

    public function reportado()
    {
        return $this->belongsTo(User::class, 'reportado_id');
    }

    public function atendidoPor()
    {
        return $this->belongsTo(Administrador::class, 'atendido_por_admin_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Accesors
    public function getEstadoNombreAttribute()
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'revisado' => 'Revisado',
            'resuelto' => 'Resuelto',
        ];
        return $estados[$this->estado] ?? $this->estado;
    }

    public function getTipoNombreAttribute()
    {
        $tipos = [
            'spam' => 'Spam o publicidad no deseada',
            'lenguaje_inapropiado' => 'Lenguaje inapropiado u ofensivo',
            'menor_edad' => 'Sospecha de menor de edad',
            'acoso' => 'Acoso o intimidación',
            'perfil_falso' => 'Perfil falso o suplantación de identidad',
            'contenido_no_solicitado' => 'Contenido explícito no solicitado',
            'amenazas' => 'Amenazas o violencia',
            'estafa' => 'Intento de estafa o fraude',
            'informacion_privada' => 'Compartió información privada sin consentimiento',
            'discriminacion' => 'Discurso de odio o discriminación',
            'venta_no_autorizada' => 'Venta de productos o servicios no autorizados',
            // Compatibilidad con motivos previos ya guardados en la BD
            'inapropiado' => 'Contenido inapropiado',
            'falso' => 'Perfil falso',
            'otro' => 'Otro',
        ];
        return $tipos[$this->tipo] ?? $this->tipo;
    }

    public function getEstaPendienteAttribute()
    {
        return $this->estado === 'pendiente';
    }
}