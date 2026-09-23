<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Ajustes de la plataforma (clave → valor) que la administración cambia desde
 * el panel. Se leen en cada página, así que van en caché hasta que cambian.
 */
class Ajuste extends Model
{
    public const GOOGLE_HABILITADO = 'acceso_google_habilitado';

    protected $table = 'ajustes';

    protected $fillable = ['clave', 'valor', 'actualizado_por'];

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }

    public static function valor(string $clave, ?string $predeterminado = null): ?string
    {
        try {
            // «__vacio__» guarda en caché también que el ajuste no existe (evita consultar en cada página).
            $guardado = Cache::rememberForever("ajuste:{$clave}", fn () => static::where('clave', $clave)->value('valor') ?? '__vacio__');

            return $guardado === '__vacio__' ? $predeterminado : $guardado;
        } catch (Throwable) {
            // Sin tabla (antes de migrar) o sin caché: el valor predeterminado.
            return $predeterminado;
        }
    }

    public static function guardar(string $clave, ?string $valor, ?User $por = null): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor, 'actualizado_por' => $por?->id]);
        Cache::forget("ajuste:{$clave}");
    }

    /** ¿Se muestra y se permite «Acceder con Google»? Por defecto, sí. */
    public static function googleHabilitado(): bool
    {
        return static::valor(self::GOOGLE_HABILITADO, '1') === '1';
    }
}
