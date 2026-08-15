<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'color_theme',
        'estimated_time',
        'summary',
        'content',
        'svg_icon',
        'is_featured',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'order_index' => 'integer',
        ];
    }

    public function userFavorites(): HasMany
    {
        return $this->hasMany(UserResourceFavorite::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_resource_favorites')
            ->withPivot('is_completed', 'completed_at', 'personal_note')
            ->withTimestamps();
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'tip' => 'Tip rápido',
            'ejercicio' => 'Ejercicio práctico',
            'reflexion' => 'Reflexión',
            'herramienta' => 'Herramienta DBT',
            'crisis' => 'Apoyo en crisis',
            default => 'Recurso',
        };
    }
}
