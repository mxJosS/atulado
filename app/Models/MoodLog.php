<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'score',
        'primary_emotion',
        'tags',
        'journal_entry',
        'gratitude_note',
        'energy_level',
        'sleep_hours',
        'logged_date',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'energy_level' => 'integer',
            'sleep_hours' => 'integer',
            'tags' => 'array',
            'logged_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEmotionLabelAttribute(): string
    {
        return match ($this->score) {
            1 => 'Muy difícil',
            2 => 'Difícil',
            3 => 'En equilibrio',
            4 => 'Bien',
            5 => 'Muy bien',
            default => 'Neutro',
        };
    }

    public function getFaIconAttribute(): string
    {
        return match ($this->score) {
            1 => 'fa-regular fa-face-angry',
            2 => 'fa-regular fa-face-frown',
            3 => 'fa-regular fa-face-meh',
            4 => 'fa-regular fa-face-smile',
            5 => 'fa-regular fa-face-laugh-beam',
            default => 'fa-regular fa-face-smile',
        };
    }

    public function getEmojiAttribute(): string
    {
        return match ($this->score) {
            1 => 'fa-regular fa-face-angry',
            2 => 'fa-regular fa-face-frown',
            3 => 'fa-regular fa-face-meh',
            4 => 'fa-regular fa-face-smile',
            5 => 'fa-regular fa-face-laugh-beam',
            default => 'fa-regular fa-face-smile',
        };
    }

    public function getScoreColorAttribute(): string
    {
        return match ($this->score) {
            1 => '#c0392b',
            2 => '#b86b4a',
            3 => '#4a7fa5',
            4 => '#7a6faa',
            5 => '#4d7c5f',
            default => '#4d7c5f',
        };
    }

    public function getFormattedDateAttribute(): string
    {
        return Carbon::parse($this->logged_date)->translatedFormat('d M, Y');
    }
}
