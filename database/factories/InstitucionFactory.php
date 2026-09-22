<?php

namespace Database\Factories;

use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Institucion>
 */
class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    public function definition(): array
    {
        $nombre = $this->faker->company();
        $corto = Str::limit($nombre, 40, '');

        return [
            'slug' => Str::slug($nombre) . '-' . $this->faker->unique()->numberBetween(1, 99999),
            'razon_social' => $nombre . ' S.A. de C.V.',
            'nombre_corto' => $corto,
            'rfc' => Str::upper($this->faker->unique()->bothify('???######???')),
            'sector' => $this->faker->randomElement(['manufactura', 'hoteleria', 'educacion', 'servicios', 'salud', 'comercio']),
            'tipo' => 'empresa',
            'ciudad' => 'Mérida',
            'estado_republica' => 'Yucatán',
            'zona_horaria' => 'America/Merida',
            'padron_estimado' => $this->faker->numberBetween(20, 500),
            'contacto_nombre' => $this->faker->name(),
            'contacto_puesto' => 'Gerente de Recursos Humanos',
            'contacto_email' => $this->faker->unique()->safeEmail(),
            'profesional_nombre' => 'Psic. ' . $this->faker->name(),
            'profesional_cedula' => $this->faker->numerify('#######'),
            'plan' => 'Institucional Anual',
            'vigencia_inicio' => now()->subMonths(2),
            'vigencia_fin' => now()->addYear(),
            'estado' => 'activa',
            'etiqueta_nivel_1' => 'departamento',
            'color' => '#2E5D4B',
            'iniciales' => Str::upper(Str::substr($corto, 0, 2)),
        ];
    }

    public function onboarding(): static
    {
        return $this->state(fn () => ['estado' => 'onboarding']);
    }

    public function educativa(): static
    {
        return $this->state(fn () => [
            'tipo' => 'educativa_estudiantes',
            'etiqueta_nivel_1' => 'salon',
        ]);
    }
}
