<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'short_name',
        'rfc',
        'category',
        'city',
        'contact_name',
        'contact_position',
        'contact_email',
        'contact_phone',
        'professional_name',
        'professional_license',
        'professional_contract_status',
        'plan',
        'users_count',
        'active_count',
        'adoption_rate',
        'alert_level',
        'critical_count',
        'renewal_date',
        'departments_data',
    ];

    protected $casts = [
        'departments_data' => 'array',
        'renewal_date' => 'date',
        'users_count' => 'integer',
        'active_count' => 'integer',
        'critical_count' => 'integer',
        'adoption_rate' => 'float',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function moodLogs()
    {
        return $this->hasManyThrough(MoodLog::class, User::class);
    }

    public function crisisEvents()
    {
        return $this->hasManyThrough(EventoCrisis::class, User::class);
    }

    /**
     * Nombre corto o nombre completo como fallback
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->short_name ?: $this->name;
    }
}
