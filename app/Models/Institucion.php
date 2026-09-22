<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Institucion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'instituciones';

    protected $fillable = [
        'slug',
        'razon_social',
        'nombre_corto',
        'rfc',
        'sector',
        'tipo',
        'ciudad',
        'estado_republica',
        'zona_horaria',
        'padron_estimado',
        'meta_adopcion',
        'contacto_nombre',
        'contacto_puesto',
        'contacto_email',
        'contacto_telefono',
        'profesional_nombre',
        'profesional_cedula',
        'profesional_email',
        'profesional_nda_hasta',
        'plan',
        'vigencia_inicio',
        'vigencia_fin',
        'estado',
        'etiqueta_nivel_1',
        'etiqueta_nivel_2',
        'aporta_perfil_estadistico',
        'guardia_nocturna',
        'redondear_porcentajes',
        'umbral_anonimato',
        'color',
        'iniciales',
    ];

    protected function casts(): array
    {
        return [
            'vigencia_inicio' => 'date',
            'vigencia_fin' => 'date',
            'profesional_nda_hasta' => 'date',
            'umbral_anonimato' => 'integer',
            'padron_estimado' => 'integer',
            'meta_adopcion' => 'integer',
            'aporta_perfil_estadistico' => 'boolean',
            'guardia_nocturna' => 'boolean',
            'redondear_porcentajes' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class)->orderBy('nombre');
    }

    /** Profesionales clínicos que la atienden (no son parte del padrón). */
    public function profesionalesAsignados(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'asignaciones_clinicas')->withTimestamps();
    }

    public function membresias(): HasMany
    {
        return $this->hasMany(Membresia::class);
    }

    public function membresiasActivas(): HasMany
    {
        return $this->hasMany(Membresia::class)->where('estado', 'activo');
    }

    public function cargas(): HasMany
    {
        return $this->hasMany(CargaPadron::class, 'institucion_id');
    }

    public function eventosCrisis(): HasMany
    {
        return $this->hasMany(EventoCrisis::class, 'institucion_id');
    }

    /**
     * Cómo se llama el nivel organizativo en la interfaz de esta institución.
     * Un colegio habla de salones; una obra, de cuadrillas.
     */
    public function getEtiquetaAreaAttribute(): string
    {
        return match ($this->etiqueta_nivel_1) {
            'salon' => 'Salón',
            'carrera' => 'Carrera',
            'sucursal' => 'Sucursal',
            'turno' => 'Turno',
            default => 'Departamento',
        };
    }

    public function getEtiquetaAreaPluralAttribute(): string
    {
        return match ($this->etiqueta_nivel_1) {
            'salon' => 'Salones',
            'carrera' => 'Carreras',
            'sucursal' => 'Sucursales',
            'turno' => 'Turnos',
            default => 'Departamentos',
        };
    }

    /**
     * Una institución no opera hasta tener estructura y al menos una persona.
     */
    public function puedeActivarse(): bool
    {
        return $this->departamentos()->where('activo', true)->exists()
            && $this->membresias()->whereIn('estado', ['invitado', 'activo'])->exists();
    }

    /**
     * "IT Soporte Cancún" → "IS". Si el nombre es una sola palabra, sus dos primeras letras.
     */
    public static function calcularIniciales(string $nombre): string
    {
        $palabras = array_values(array_filter(
            preg_split('/\s+/u', trim($nombre)) ?: [],
            fn ($p) => mb_strlen($p) > 2 || ctype_upper(Str::ascii($p))
        ));

        $iniciales = count($palabras) >= 2
            ? mb_substr($palabras[0], 0, 1) . mb_substr($palabras[1], 0, 1)
            : mb_substr(trim($nombre), 0, 2);

        return Str::upper($iniciales) ?: 'AT';
    }

    /** Días que faltan para la renovación; negativo si ya venció. */
    public function diasParaRenovar(): ?int
    {
        return $this->vigencia_fin
            ? (int) now()->startOfDay()->diffInDays($this->vigencia_fin->startOfDay(), false)
            : null;
    }

    public function getEstadoLegibleAttribute(): string
    {
        return match ($this->estado) {
            'activa' => 'Activa',
            'onboarding' => 'En alta',
            'por_renovar' => 'Por renovar',
            'suspendida' => 'Suspendida',
            'baja' => 'Baja',
            default => ucfirst((string) $this->estado),
        };
    }

    public static function generarSlug(string $nombre): string
    {
        $base = Str::slug($nombre) ?: 'institucion';
        $slug = $base;
        $n = 2;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }
}
