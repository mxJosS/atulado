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
        'trusted_contacts',
        'professional_contacts',
        'safe_environment_steps',
        'reasons_for_living',
    ];

    protected function casts(): array
    {
        return [
            'warning_signs' => 'array',
            'internal_coping' => 'array',
            'distraction_activities' => 'array',
            'trusted_contacts' => 'array',
            'professional_contacts' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
