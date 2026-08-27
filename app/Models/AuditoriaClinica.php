<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaClinica extends Model
{
    use HasFactory;

    protected $table = 'auditoria_clinica';

    protected $fillable = [
        'profesional_id',
        'usuario_consultado_id',
        'accion',
        'detalle',
    ];

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }

    public function usuarioConsultado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_consultado_id');
    }
}
