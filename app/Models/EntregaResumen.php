<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaResumen extends Model
{
    protected $table = 'entregas_resumen';

    protected $fillable = [
        'folio',
        'membresia_id',
        'generado_por',
        'destinatario_nombre',
        'destinatario_email',
        'motivo',
        'contenido',
        'vence_en',
        'abierto_en',
        'aperturas',
    ];

    protected function casts(): array
    {
        return [
            'vence_en' => 'datetime',
            'abierto_en' => 'datetime',
            'aperturas' => 'integer',
        ];
    }

    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class);
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    public function vigente(): bool
    {
        return $this->vence_en->isFuture();
    }
}
