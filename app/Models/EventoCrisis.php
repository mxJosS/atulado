<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoCrisis extends Model
{
    use HasFactory;

    protected $table = 'eventos_crisis';

    protected $fillable = [
        'user_id',
        'disparado_en',
        'notificado_en',
        'contactado_en',
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

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cierre_verificado_por');
    }
}
