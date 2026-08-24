<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'topic_area_id',
        'title',
        'slug',
        'author_name',
        'author_credentials',
        'author_role',
        'visual_theme',
        'color_tag',
        'publication_type',
        'target_audience',
        'summary',
        'excerpt',
        'content',
        'cover_image_path',
        'references',
        'references_list',
        'discussion_prompt',
        'reading_time_min',
        'read_time',
        'allow_comments',
        'is_disclaimer_accepted',
        'status',
        'is_featured',
        'is_peer_reviewed',
        'category',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'allow_comments' => 'boolean',
            'is_disclaimer_accepted' => 'boolean',
            'is_featured' => 'boolean',
            'is_peer_reviewed' => 'boolean',
            'reading_time_min' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAuthorAvatarUrlAttribute(): ?string
    {
        return $this->user?->avatar_url;
    }

    public function topicArea(): BelongsTo
    {
        return $this->belongsTo(TopicArea::class, 'topic_area_id');
    }

    // Compatibility Accessors
    public function getCategoryAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->topicArea?->name ?? 'Psicoeducación';
    }

    public function getExcerptAttribute($value)
    {
        return $value ?: $this->attributes['summary'] ?? '';
    }

    public function getSummaryAttribute($value)
    {
        return $value ?: $this->attributes['excerpt'] ?? '';
    }

    public function getAuthorRoleAttribute($value)
    {
        return $value ?: $this->attributes['author_credentials'] ?? 'Psicólogo Clínico · Terapeuta DBT';
    }

    public function getAuthorCredentialsAttribute($value)
    {
        return $value ?: $this->attributes['author_role'] ?? 'Psicólogo Clínico · Terapeuta DBT';
    }

    public function getReferencesListAttribute($value)
    {
        return $value ?: $this->attributes['references'] ?? '';
    }

    public function getReferencesAttribute($value)
    {
        return $value ?: $this->attributes['references_list'] ?? '';
    }

    public function getReadTimeAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        $mins = $this->attributes['reading_time_min'] ?? 1;
        return "{$mins} min";
    }

    public function getColorTagAttribute($value)
    {
        return $value ?: $this->attributes['visual_theme'] ?? 'salvia';
    }

    public function getVisualThemeAttribute($value)
    {
        return $value ?: $this->attributes['color_tag'] ?? 'salvia';
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->published_at ? Carbon::parse($this->published_at)->translatedFormat('d M, Y') : '';
    }

    // Publication Type Label helper
    public function getPublicationTypeLabelAttribute(): string
    {
        return match ($this->publication_type) {
            'revision' => 'Revisión Científica',
            'caso_estudio' => 'Caso de Estudio',
            'guia' => 'Guía Clínica',
            default => 'Divulgación Basada en Evidencia',
        };
    }

    // Target Audience Label helper
    public function getTargetAudienceLabelAttribute(): string
    {
        return match ($this->target_audience) {
            'estudiantes' => 'Estudiantes de Psicología / Salud',
            'profesionales' => 'Profesionales y Terapeutas',
            default => 'Público General',
        };
    }
}
