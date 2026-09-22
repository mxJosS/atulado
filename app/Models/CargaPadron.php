<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargaPadron extends Model
{
    use HasFactory;

    protected $table = 'cargas_padron';

    protected $fillable = [
        'institucion_id',
        'user_id',
        'nombre_original',
        'mapeo',
        'filas_total',
        'filas_lista',
        'filas_advertencia',
        'filas_error',
        'filas_duplicado',
        'estado',
        'ejecutada_en',
    ];

    protected function casts(): array
    {
        return [
            'mapeo' => 'array',
            'filas_total' => 'integer',
            'filas_lista' => 'integer',
            'filas_advertencia' => 'integer',
            'filas_error' => 'integer',
            'filas_duplicado' => 'integer',
            'ejecutada_en' => 'datetime',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function filas(): HasMany
    {
        return $this->hasMany(CargaPadronFila::class, 'carga_id')->orderBy('numero_fila');
    }

    /** Las filas con error no se dan de alta; las de advertencia sí. */
    public function filasEjecutables(): HasMany
    {
        return $this->hasMany(CargaPadronFila::class, 'carga_id')
            ->whereIn('estado', ['lista', 'advertencia'])
            ->orderBy('numero_fila');
    }

    public function puedeEjecutarse(): bool
    {
        return $this->estado === 'validada' && ($this->filas_lista + $this->filas_advertencia) > 0;
    }
}
