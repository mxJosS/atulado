<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Una visita a un módulo de la app (herramienta, recurso, revista, plan de
 * seguridad o botón de crisis). Sólo guarda quién, dónde y cuándo: nunca el
 * contenido.
 */
class Interaccion extends Model
{
    protected $table = 'interacciones';

    public $timestamps = false;

    public const MODULOS = ['herramienta', 'recurso', 'revista', 'plan', 'crisis'];

    protected $fillable = ['user_id', 'institucion_id', 'modulo', 'ocurrido_en'];

    protected function casts(): array
    {
        return ['ocurrido_en' => 'datetime'];
    }

    /**
     * Registra la visita de la persona con sesión. Sin sesión no hace nada, y
     * un fallo nunca rompe la página que se está mostrando.
     */
    public static function registrar(string $modulo): void
    {
        $user = Auth::user();
        // Con «Entrar como» la visita es de administración, no de la persona.
        if (!$user || !in_array($modulo, self::MODULOS, true) || \App\Support\Suplantacion::activa()) {
            return;
        }

        try {
            self::create([
                'user_id' => $user->id,
                'institucion_id' => $user->membresiaActiva()->value('institucion_id'),
                'modulo' => $modulo,
                'ocurrido_en' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar la interacción: ' . $e->getMessage());
        }
    }
}
