<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_color',
        'bio',
        'crisis_contact_name',
        'crisis_contact_phone',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function moodLogs(): HasMany
    {
        return $this->hasMany(MoodLog::class)->orderBy('logged_date', 'desc');
    }

    public function safetyPlan(): HasOne
    {
        return $this->hasOne(SafetyPlan::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserResourceFavorite::class);
    }

    public function favoriteResources(): BelongsToMany
    {
        return $this->belongsToMany(Resource::class, 'user_resource_favorites')
            ->withPivot('is_completed', 'completed_at', 'personal_note')
            ->withTimestamps();
    }

    public function getTodayMoodLogAttribute()
    {
        return $this->moodLogs()->whereDate('logged_date', Carbon::today())->first();
    }

    /**
     * Calculate consecutive check-in streak in days.
     */
    public function calculateStreak(): int
    {
        $dates = $this->moodLogs()
            ->select('logged_date')
            ->distinct()
            ->orderBy('logged_date', 'desc')
            ->pluck('logged_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        if (empty($dates)) {
            return 0;
        }

        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        $firstDate = $dates[0];
        if ($firstDate !== $today && $firstDate !== $yesterday) {
            return 0;
        }

        $streak = 1;
        $current = Carbon::parse($firstDate);

        for ($i = 1; $i < count($dates); $i++) {
            $expectedPrevious = $current->copy()->subDay()->format('Y-m-d');
            if ($dates[$i] === $expectedPrevious) {
                $streak++;
                $current = Carbon::parse($dates[$i]);
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get weekly mood history for current week (Mon-Sun).
     */
    public function getWeeklySummary(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $logs = $this->moodLogs()
            ->whereBetween('logged_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->logged_date)->format('Y-m-d'));

        $days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $weekData = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $log = $logs->get($dateStr);

            $weekData[] = [
                'day_name' => $days[$i],
                'date' => $dateStr,
                'is_today' => $date->isToday(),
                'is_past' => $date->isPast() && !$date->isToday(),
                'has_log' => $log !== null,
                'score' => $log?->score,
                'primary_emotion' => $log?->primary_emotion,
                'emoji' => $log?->emoji,
            ];
        }

        return $weekData;
    }
}
