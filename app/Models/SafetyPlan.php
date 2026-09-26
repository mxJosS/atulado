<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'warning_signs',
        'internal_coping',
        'distraction_activities',
        'social_distractions',
        'safe_places',
        'trusted_contacts',
        'professional_contacts',
        'safe_environment_steps',
        'reasons_for_living',
        'reasons_to_live',
    ];

    protected function casts(): array
    {
        // Todo el plan va cifrado en reposo (APP_KEY).
        return [
            'warning_signs' => 'encrypted:array',
            'internal_coping' => 'encrypted:array',
            'distraction_activities' => 'encrypted:array',
            'social_distractions' => 'encrypted:array',
            'safe_places' => 'encrypted:array',
            'trusted_contacts' => 'encrypted:array',
            'professional_contacts' => 'encrypted:array',
            'reasons_to_live' => 'encrypted:array',
            'safe_environment_steps' => 'encrypted',
            'reasons_for_living' => 'encrypted',
        ];
    }

    public function getReasonsToLiveAttribute($value): ?array
    {
        if (!is_null($value)) {
            return is_string($value) ? json_decode($value, true) : $value;
        }
        if (!empty($this->reasons_for_living)) {
            $lines = array_filter(array_map('trim', explode("\n", $this->reasons_for_living)));
            return !empty($lines) ? array_values($lines) : null;
        }
        return null;
    }

    public function getSocialDistractionsAttribute($value): ?array
    {
        if (!is_null($value)) {
            return is_string($value) ? json_decode($value, true) : $value;
        }
        if (!empty($this->distraction_activities)) {
            return is_array($this->distraction_activities) ? $this->distraction_activities : json_decode($this->distraction_activities, true);
        }
        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
