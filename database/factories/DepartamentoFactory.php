<?php

namespace Database\Factories;

use App\Models\Departamento;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Departamento>
 */
class DepartamentoFactory extends Factory
{
    protected $model = Departamento::class;

    public function definition(): array
    {
        $nombre = $this->faker->randomElement([
            'Operaciones', 'Corporativo', 'Producción', 'Mantenimiento',
            'Ventas', 'Administración', 'Logística', 'Calidad',
        ]) . ' ' . $this->faker->unique()->numberBetween(1, 9999);

        return [
            'institucion_id' => Institucion::factory(),
            'parent_id' => null,
            'nombre' => $nombre,
            'clave' => Str::slug($nombre),
            'turno_predominante' => $this->faker->randomElement(['matutino', 'vespertino', 'nocturno', 'mixto']),
            'responsable_nombre' => $this->faker->name(),
            'responsable_email' => $this->faker->unique()->safeEmail(),
            'personas_esperadas' => $this->faker->numberBetween(5, 80),
            'activo' => true,
        ];
    }

    public function para(Institucion $institucion): static
    {
        return $this->state(fn () => ['institucion_id' => $institucion->id]);
    }
}
