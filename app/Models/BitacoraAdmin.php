<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Una acción de administración: permisos, eliminaciones, exportaciones,
 * acreditaciones, ajustes y reinicios. Sólo se agregan filas; no hay pantalla
 * para editarlas ni borrarlas.
 */
class BitacoraAdmin extends Model
{
    protected $table = 'bitacora_admin';

    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'actor_email', 'accion', 'objeto_tipo', 'objeto_id', 'detalle', 'ip'];

    public static function registrar(string $accion, ?Model $objeto = null, ?string $detalle = null): void
    {
        // Con «Entrar como» actúa la cuenta principal, no la persona.
        $actor = \App\Support\Suplantacion::adminOriginal() ?? Auth::user();

        try {
            self::create([
                'actor_id' => $actor?->id,
                'actor_email' => $actor?->email,
                'accion' => $accion,
                'objeto_tipo' => $objeto ? class_basename($objeto) : null,
                'objeto_id' => $objeto?->getKey(),
                'detalle' => $detalle !== null ? mb_substr($detalle, 0, 2000) : null,
                'ip' => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            // Nunca tumba la acción; al menos queda en el log.
            Log::warning("Bitácora de administración no disponible ({$accion}): " . $e->getMessage());
        }
    }
}
