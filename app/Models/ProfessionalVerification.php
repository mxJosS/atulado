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
            'especialidad' => 'Especialidad Clínica',
            'maestria'     => 'Maestría',
            'doctorado'    => 'Doctorado',
            default        => ucfirst($this->education_level ?? ''),
        };
    }

    /**
     * Retorna el título profesional predeterminado para el perfil
     */
    public function getComputedProfessionalTitleAttribute(): string
    {
        return match($this->education_level) {
            'licenciatura' => 'Licenciado(a) en Salud Mental',
            'especialidad' => 'Especialista Clínico(a)',
            'maestria'     => 'Maestro(a) en Salud Mental',
            'doctorado'    => 'Doctor(a) en Psicología / Salud',
            default        => 'Profesional de la Salud',
        };
    }
}
