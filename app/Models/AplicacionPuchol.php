<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un ciclo del test breve del estado de ánimo (Puchol en el motor): 22 ítems
 * en cuatro secciones, respondidos por bloques a lo largo de varios días.
 */
class AplicacionPuchol extends Model
{
    use HasFactory;

    protected $table = 'aplicaciones_puchol';

    protected $fillable = [
        'user_id',
        'fecha',
        'estado',
        'origen',
        'disponible_desde',
        'respuestas',
        'bloques',
        'ansiedad',
        'fisica',
        'depresion',
        'suicidas',
        'ofrecido_pendiente',
        'ofrecido_en',
        'intentos',
        'ultima_respuesta',
        'completado_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'disponible_desde' => 'date',
            'ofrecido_en' => 'date',
            'ultima_respuesta' => 'date',
            'completado_en' => 'datetime',
            'respuestas' => 'array',
            'bloques' => 'array',
            'ansiedad' => 'integer',
            'fisica' => 'integer',
            'depresion' => 'integer',
            'suicidas' => 'integer',
            'intentos' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return list<string> */
    public function bloquesRespondidos(): array
    {
        return $this->bloques ?? [];
    }

    public function tieneAlgunaSeccion(): bool
    {
        return $this->ansiedad !== null || $this->fisica !== null || $this->depresion !== null || $this->suicidas !== null;
    }
}
