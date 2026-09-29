<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Estado del juego del árbol de una cuenta. Lo escribe public/js/game-engine.js
 * (POST /jardin) y lo lee el layout de tu espacio al cargar cada página.
 */
class Jardin extends Model
{
    protected $table = 'jardines';

    public const NIVEL_MAXIMO = 50;

    /** Las 8 especies del juego; «tree» es el árbol del logo, la planta principal. */
    public const PLANTAS = ['tree', 'lotus', 'bonsai', 'sunflower', 'cactus', 'bamboo', 'lavender', 'orchid'];

    public const MACETAS = ['terracotta', 'jade', 'kintsugi', 'marble', 'obsidian', 'wood'];

    protected $fillable = ['user_id', 'estado'];

    protected function casts(): array
    {
        return ['estado' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Nivel del árbol del logo (1 si todavía no hay jardín). */
    public function nivelArbol(): int
    {
        return (int) ($this->estado['plantas']['tree']['nivel'] ?? 1);
    }
}
