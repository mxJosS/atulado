<?php

namespace Database\Factories;

use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membresia>
 */
class MembresiaFactory extends Factory
{
    protected $model = Membresia::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'user_id' => User::factory(),
            'departamento_id' => null,
            'numero_empleado' => $this->faker->unique()->numerify('EMP-####'),
            'puesto' => $this->faker->jobTitle(),
            'turno' => $this->faker->randomElement(['matutino', 'vespertino', 'nocturno', 'mixto']),
            'fecha_ingreso' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            'sexo' => $this->faker->randomElement(['M', 'F', 'prefiere_no_decir']),
            'rango_edad' => $this->faker->randomElement(['18-24', '25-34', '35-44', '45-54', '55+']),
            'idioma' => 'es',
            'rol_institucional' => 'colaborador',
            'estado' => 'activo',
            'activado_en' => now(),
        ];
    }

    public function invitada(): static
    {
        return $this->state(fn () => [
            'estado' => 'invitado',
            'activado_en' => null,
            'invitado_en' => null,
        ]);
    }

    public function deBaja(): static
    {
        return $this->state(fn () => [
            'estado' => 'baja',
            'baja_en' => now(),
            'baja_motivo' => 'Fin de relación laboral',
        ]);
    }

    public function en(Institucion $institucion, ?Departamento $departamento = null): static
    {
        return $this->state(fn () => [
            'institucion_id' => $institucion->id,
            'departamento_id' => $departamento?->id,
        ]);
    }
}
