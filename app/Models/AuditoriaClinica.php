<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * Bitácora de accesos al plano clínico.
 *
 * Es de SÓLO INSERCIÓN. Una bitácora que se puede editar o borrar no prueba
 * nada, así que el modelo bloquea update y delete a nivel de aplicación.
 */
class AuditoriaClinica extends Model
{
    use HasFactory;

    protected $table = 'auditoria_clinica';

    protected $fillable = [
        'profesional_id',
        'usuario_consultado_id',
        'institucion_id',
        'accion',
        'motivo',
        'detalle',
        'ip',
    ];

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new RuntimeException('La bitácora de accesos clínicos es inmutable: no admite modificaciones.');
        });

        static::deleting(function (): never {
            throw new RuntimeException('La bitácora de accesos clínicos es inmutable: no admite eliminaciones.');
        });
    }

    public function profesional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profesional_id');
    }

    public function usuarioConsultado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_consultado_id');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function getAccionLegibleAttribute(): string
    {
        return match ($this->accion) {
            'consulta_detalle' => 'Apertura de ficha',
            'elevacion_nivel' => 'Elevación de nivel',
            'cierre_crisis' => 'Cierre de caso',
            'registro_contacto' => 'Registro de contacto',
            'verificacion_asq' => 'Verificación ASQ',
            'resumen_generado' => 'Resumen clínico generado',
            default => ucfirst(str_replace('_', ' ', (string) $this->accion)),
        };
    }
}
