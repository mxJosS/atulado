<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'license_number',
        'education_level',
        'specialty',
        'document_path',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Retorna la etiqueta amigable del grado escolar
     */
    public function getEducationLevelLabelAttribute(): string
    {
        return match($this->education_level) {
            'licenciatura' => 'Licenciatura / Pregrado',
            'especialidad' => 'Especialidad',
            'maestria'     => 'Maestría',
            'doctorado'    => 'Doctorado',
            default        => ucfirst($this->education_level ?? ''),
        };
    }

    /**
     * Retorna el título profesional predeterminado o basado en la especialidad
     */
    public function getComputedProfessionalTitleAttribute(): string
    {
        if (!empty($this->specialty)) {
            $prefix = match($this->education_level) {
                'licenciatura' => 'Lic. en',
                'especialidad' => 'Esp. en',
                'maestria'     => 'Mtr. en',
                'doctorado'    => 'Dr(a). en',
                default        => '',
            };
            return trim("{$prefix} {$this->specialty}");
        }

        return match($this->education_level) {
            'licenciatura' => 'Licenciado(a) en Salud / Psicología',
            'especialidad' => 'Especialista Clínico(a)',
            'maestria'     => 'Maestro(a) en Salud / Psicología',
            'doctorado'    => 'Doctor(a) en Salud / Psicología',
            default        => 'Profesional de la Salud',
        };
    }
}
