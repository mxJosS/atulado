<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Membresia extends Model
{
    use HasFactory;

    protected $table = 'membresias';

    protected $fillable = [
        'institucion_id',
        'user_id',
        'departamento_id',
        'carga_id',
        'numero_empleado',
        'puesto',
        'turno',
        'horario',
        'fecha_ingreso',
        'tipo_jornada',
        'sexo',
        'rango_edad',
        'escolaridad',
        'lugar_origen',
        'idioma',
        'rol_institucional',
        'estado',
        'codigo_acceso',
        'invitado_en',
        'activado_en',
        'baja_en',
        'baja_motivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_ingreso' => 'date',
            'invitado_en' => 'datetime',
            'activado_en' => 'datetime',
            'baja_en' => 'datetime',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function carga(): BelongsTo
    {
        return $this->belongsTo(CargaPadron::class, 'carga_id');
    }

    /**
     * Identificador que se muestra a quien NO tiene acreditación clínica.
     *
     * Deriva del id de la membresía a propósito: el número de empleado
     * identifica a la persona dentro de su organización, así que no sirve
     * como seudónimo.
     */
    public function getFolioAttribute(): string
    {
        return 'COL-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('estado', 'activo');
    }

    /** Cuenta para los agregados quien no está dado de baja. */
    public function scopeEnPadron(Builder $query): Builder
    {
        return $query->whereIn('estado', ['invitado', 'activo', 'suspendido']);
    }

    /**
     * Sólo colaboradores: deja fuera al personal de la plataforma
     * (administración y clínicos), que puede estar en un padrón pero no
     * cuenta en padrón, activaciones ni adopción.
     */
    public function scopeSoloColaboradores(Builder $query): Builder
    {
        return $query->whereHas('user', fn (Builder $u) => $u
            ->where(fn ($q) => $q->where('is_admin', false)->orWhereNull('is_admin'))
            ->where(fn ($q) => $q->where('is_clinico_atulado', false)->orWhereNull('is_clinico_atulado'))
            ->where(fn ($q) => $q->whereNotIn('role', ['admin', 'clinico'])->orWhereNull('role')));
    }

    public function scopePendientesDeInvitar(Builder $query): Builder
    {
        return $query->where('estado', 'invitado')->whereNull('invitado_en');
    }

    public function estaDeBaja(): bool
    {
        return $this->estado === 'baja';
    }

    /** La reactivación sólo es posible dentro de los 30 días siguientes a la baja. */
    public function puedeReactivarse(): bool
    {
        return $this->estaDeBaja()
            && $this->baja_en !== null
            && $this->baja_en->diffInDays(now()) <= 30;
    }
}
