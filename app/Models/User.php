<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'google_id',
        'password',
        'avatar',
        'avatar_color',
        'bio',
        'crisis_contact_name',
        'crisis_contact_phone',
        'is_admin',
        'is_clinico_atulado',
        'role',
        'professional_title',
        'license_number',
        'institution',
        'institution_id',
        'macro_group',
        'department',
        'shift',
        'employee_number',
        'position',
        'email_verified_at',
        'verification_code',
        'verification_code_expires_at',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return str_starts_with($this->avatar, 'http') ? $this->avatar : asset('storage/' . $this->avatar);
        }
        return null;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verification_code_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_clinico_atulado' => 'boolean',
        ];
    }

    /**
     * Pertenencia institucional. Sustituye a las columnas sueltas
     * institution_id / macro_group / department / shift / employee_number.
     */
    public function membresias(): HasMany
    {
        return $this->hasMany(Membresia::class);
    }

    public function membresiaActiva(): HasOne
    {
        return $this->hasOne(Membresia::class)->whereIn('estado', ['invitado', 'activo']);
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function generateVerificationCode(): string
    {
        $code = (string) random_int(100000, 999999);
        try {
            $this->verification_code = $code;
            $this->verification_code_expires_at = Carbon::now()->addMinutes(15);
            $this->save();
        } catch (\Throwable $e) {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'verification_code')) {
                    \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->string('verification_code', 6)->nullable();
                        $table->timestamp('verification_code_expires_at')->nullable();
                    });
                    $this->verification_code = $code;
                    $this->verification_code_expires_at = Carbon::now()->addMinutes(15);
                    $this->save();
                }
            } catch (\Throwable $ignored) {
                // Silenciosamente continuar para evitar error 500
            }
        }

        return $code;
    }

    public function isVerificationCodeValid(?string $code): bool
    {
        try {
            if (empty($code) || empty($this->verification_code) || empty($this->verification_code_expires_at)) {
                return false;
            }

            if (trim((string)$code) !== trim((string)$this->verification_code)) {
                return false;
            }

            return Carbon::now()->lte($this->verification_code_expires_at);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function markEmailAsVerified(): bool
    {
        try {
            return $this->forceFill([
                'email_verified_at' => Carbon::now(),
                'verification_code' => null,
                'verification_code_expires_at' => null,
            ])->save();
        } catch (\Throwable $e) {
            return $this->forceFill([
                'email_verified_at' => Carbon::now(),
            ])->save();
        }
    }

    /**
     * Puede publicar en la revista (perfil «publica» o «ambos», o administración).
     * No da acceso a información clínica: eso es isClinicoAcreditado().
     */
    public function isProfessional(): bool
    {
        return $this->role === 'profesional' || $this->role === 'admin' || $this->is_admin;
    }

    /**
     * Perfil de una cuenta profesional:
     *   publica → role 'profesional', sin acreditación clínica
     *   clinico → role 'clinico', con acreditación clínica (no publica)
     *   ambos   → role 'profesional', con acreditación clínica
     */
    public function getPerfilProfesionalAttribute(): ?string
    {
        return match (true) {
            $this->role === 'clinico' => 'clinico',
            $this->role === 'profesional' && $this->is_clinico_atulado => 'ambos',
            $this->role === 'profesional' => 'publica',
            default => null,
        };
    }

    /**
     * Aplica el rol elegido en la administración de usuarios.
     *
     * @param  'admin'|'profesional'|'usuario'  $rol
     * @param  'publica'|'clinico'|'ambos'|null  $perfil  sólo para profesionales
     * @param  bool  $clinicoAdmin  acreditación clínica para un administrador
     */
    public function asignarRol(string $rol, ?string $perfil = null, bool $clinicoAdmin = false): void
    {
        [$role, $clinico] = match (true) {
            $rol === 'admin' => ['admin', $clinicoAdmin],
            $rol === 'profesional' && $perfil === 'clinico' => ['clinico', true],
            $rol === 'profesional' && $perfil === 'ambos' => ['profesional', true],
            $rol === 'profesional' => ['profesional', false],
            default => ['usuario', false],
        };

        $this->forceFill([
            'role' => $role,
            'is_admin' => $rol === 'admin',
            'is_clinico_atulado' => $clinico,
        ]);
    }

    /** Entra al panel: administración u operación clínica. */
    public function puedeUsarPanel(): bool
    {
        return (bool) $this->is_admin || $this->isClinicoAcreditado();
    }

    /**
     * Determina si el usuario es una cuenta administradora principal protegida
     */
    public function isSuperAdmin(): bool
    {
        $email = strtolower(trim($this->email ?? ''));

        return $email !== '' && in_array($email, config('atulado.superadmin_emails', []), true);
    }

    /**
     * Permiso clínico acreditado: habilita ver información individual.
     * Distinto de is_admin, que sólo da acceso a la operación de plataforma.
     */
    public function isClinicoAcreditado(): bool
    {
        // Sólo la acreditación explícita. Ser profesional que publica artículos no basta.
        return (bool) ($this->is_clinico_atulado ?? false);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->orderBy('published_at', 'desc');
    }

    public function professionalVerifications(): HasMany
    {
        return $this->hasMany(ProfessionalVerification::class)->orderBy('created_at', 'desc');
    }

    public function latestProfessionalVerification(): HasOne
    {
        return $this->hasOne(ProfessionalVerification::class)->latestOfMany();
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

    public function seriesVigilancia(): HasMany
    {
        return $this->hasMany(SerieVigilancia::class)->orderBy('fecha', 'desc');
    }

    public function aplicacionesWho5(): HasMany
    {
        return $this->hasMany(AplicacionWho5::class)->orderBy('fecha', 'desc');
    }

    public function aplicacionesMdi(): HasMany
    {
        return $this->hasMany(AplicacionMdi::class)->orderBy('fecha', 'desc');
    }

    public function aplicacionesAsq(): HasMany
    {
        return $this->hasMany(AplicacionAsq::class)->orderBy('fecha', 'desc');
    }

    public function aplicacionesPuchol(): HasMany
    {
        return $this->hasMany(AplicacionPuchol::class)->orderBy('fecha', 'desc');
    }

    public function clasificaciones(): HasMany
    {
        return $this->hasMany(Clasificacion::class)->orderBy('fecha', 'desc');
    }

    public function eventosCrisis(): HasMany
    {
        return $this->hasMany(EventoCrisis::class)->orderBy('disparado_en', 'desc');
    }

    public function contactosEmergencia(): HasMany
    {
        return $this->hasMany(ContactoEmergencia::class);
    }

    public function getLatestClasificacionAttribute(): ?Clasificacion
    {
        return $this->clasificaciones()->first();
    }

    public function getActiveCrisisEventAttribute(): ?EventoCrisis
    {
        return $this->eventosCrisis()->where('estado', '!=', 'cerrado')->first();
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

    /**
     * Send the password reset notification using the branded A Tu Lado template.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
