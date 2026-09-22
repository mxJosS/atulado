<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoCrisis extends Model
{
    use HasFactory;

    protected $table = 'eventos_crisis';

    protected $fillable = [
        'user_id',
        'institucion_id',
        'nivel',
        'origen',
        'disparado_en',
        'notificado_en',
        'contactado_en',
        'primer_contacto_por',
        'salida_sin_contacto',
        'estoy_con_alguien',
        'cierre_verificado_por',
        'notas_cierre',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'disparado_en' => 'datetime',
            'notificado_en' => 'datetime',
            'contactado_en' => 'datetime',
            'salida_sin_contacto' => 'boolean',
            'estoy_con_alguien' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cierre_verificado_por');
    }

    public function contactadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primer_contacto_por');
    }

    /**
     * Casos que ve una cuenta del panel: la administración, todos; un clínico,
     * sólo los de sus instituciones asignadas (no los de personas sin institución).
     */
    public function scopeVisiblesPara(Builder $query, User $user): Builder
    {
        $ids = $user->institucionesVisiblesIds();

        return $ids === null ? $query : $query->whereIn('institucion_id', $ids);
    }

    public function scopeAbiertos(Builder $query): Builder
    {
        return $query->where('estado', '!=', 'cerrado');
    }

    /**
     * Orden de la cola de atención: primero lo más grave, y dentro de cada
     * nivel, lo que lleva más tiempo esperando.
     */
    public function scopePorPrioridad(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE WHEN nivel = 'ROJO_AGUDO' THEN 0 WHEN nivel = 'ROJO' THEN 1 ELSE 2 END")
            ->orderBy('disparado_en');
    }

    public function estaCerrado(): bool
    {
        return $this->estado === 'cerrado';
    }

    public function tieneContactoHumano(): bool
    {
        return $this->contactado_en !== null;
    }

    /**
     * Minutos transcurridos hasta el primer contacto, o hasta ahora si
     * todavía no lo hay. Alimenta el cumplimiento del SLA.
     */
    public function minutosHastaContacto(): ?int
    {
        if ($this->disparado_en === null) {
            return null;
        }

        return (int) $this->disparado_en->diffInMinutes($this->contactado_en ?? now());
    }
}
