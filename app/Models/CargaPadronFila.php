<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CargaPadronFila extends Model
{
    use HasFactory;

    protected $table = 'cargas_padron_filas';

    protected $fillable = [
        'carga_id',
        'numero_fila',
        'datos',
        'estado',
        'mensajes',
        'membresia_id',
    ];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
            'mensajes' => 'array',
            'numero_fila' => 'integer',
        ];
    }

    public function carga(): BelongsTo
    {
        return $this->belongsTo(CargaPadron::class, 'carga_id');
    }

    public function membresia(): BelongsTo
    {
        return $this->belongsTo(Membresia::class);
    }

    public function dato(string $campo): ?string
    {
        $valor = $this->datos[$campo] ?? null;

        return $valor === null || $valor === '' ? null : (string) $valor;
    }

    public function tieneError(): bool
    {
        return $this->estado === 'error';
    }
}
