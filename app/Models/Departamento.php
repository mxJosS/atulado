<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamentos';

    protected $fillable = [
        'institucion_id',
        'parent_id',
        'nombre',
        'clave',
        'turno_predominante',
        'responsable_nombre',
        'responsable_email',
        'personas_esperadas',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'personas_esperadas' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class);
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'parent_id');
    }

    public function hijos(): HasMany
    {
        return $this->hasMany(Departamento::class, 'parent_id');
    }

    public function membresias(): HasMany
    {
        return $this->hasMany(Membresia::class);
    }

    public static function generarClave(int $institucionId, string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre) ?: 'area';
        $clave = $base;
        $n = 2;

        while (static::where('institucion_id', $institucionId)
            ->where('clave', $clave)
            ->when($ignorarId, fn ($q) => $q->where('id', '!=', $ignorarId))
            ->exists()) {
            $clave = $base . '-' . $n++;
        }

        return $clave;
    }
}
