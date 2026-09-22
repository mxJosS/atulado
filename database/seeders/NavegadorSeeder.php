<?php

namespace Database\Seeders;

use App\Models\AplicacionAsq;
use App\Models\AplicacionMdi;
use App\Models\AplicacionWho5;
use App\Models\Clasificacion;
use App\Models\ContactoEmergencia;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\EstructuraInstitucionalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Datos de ejemplo para la prueba en navegador (scripts/prueba-navegador.php).
 * Sólo para una base local desechable: NUNCA correr en producción.
 */
class NavegadorSeeder extends Seeder
{
    public const PASSWORD = 'Prueba12345';

    public function run(): void
    {
        $clinico = User::factory()->create([
            'name' => 'Said Canul', 'email' => 'admin@prueba.local', 'password' => self::PASSWORD,
            'is_admin' => true, 'role' => 'admin', 'is_clinico_atulado' => true, 'email_verified_at' => now(),
        ]);

        $institucion = Institucion::factory()->create([
            'razon_social' => 'Constructora Maya S.A. de C.V.', 'nombre_corto' => 'Constructora Maya', 'slug' => 'constructora-maya',
            'profesional_nombre' => 'Psic. Rodrigo Ancona', 'profesional_email' => 'rancona@prueba.local', 'profesional_nda_hasta' => now()->addYear(),
        ]);
        app(EstructuraInstitucionalService::class)->crearMacroAreas($institucion, ['Operaciones', 'Corporativo']);
        $area = $institucion->departamentos()->first();

        $persona = function (string $nombre, string $email, string $estado = 'activo') use ($institucion, $area) {
            $user = User::factory()->create(['name' => $nombre, 'email' => $email, 'password' => self::PASSWORD, 'email_verified_at' => now()]);

            return Membresia::factory()->create([
                'institucion_id' => $institucion->id, 'user_id' => $user->id, 'departamento_id' => $area->id,
                'estado' => $estado, 'activado_en' => $estado === 'activo' ? now() : null,
                'turno' => 'nocturno', 'horario' => '22:00-06:00', 'puesto' => 'Oficial albañil',
            ]);
        };

        // Caso agudo con historia completa para la ficha
        $agudo = $persona('Luis Manuel Chan Poot', 'luis@prueba.local');
        foreach (range(20, 0) as $dias) {
            $valor = $dias > 5 ? 1 : ($dias > 2 ? 3 : 4);
            MoodLog::create([
                'user_id' => $agudo->user_id, 'score' => 5 - $valor, 'valor_invertido' => $valor, 'primary_emotion' => 'Calma',
                'journal_entry' => $dias === 0 ? 'ya no quiero vivir así' : null,
                'bandera_lexica' => $dias === 0, 'terminos_detectados' => $dias === 0 ? ['no quiero vivir'] : [],
                'logged_date' => Carbon::today()->subDays($dias),
            ]);
        }
        AplicacionWho5::create(['user_id' => $agudo->user_id, 'fecha' => today()->subDays(28), 'i1' => 3, 'i2' => 3, 'i3' => 3, 'i4' => 2, 'i5' => 3, 'crudo' => 14, 'escala' => 56, 'origen' => 'programada']);
        AplicacionWho5::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'i1' => 1, 'i2' => 1, 'i3' => 0, 'i4' => 0, 'i5' => 4, 'crudo' => 6, 'escala' => 24, 'origen' => 'ruta_b']);
        AplicacionMdi::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'i1' => 5, 'i2' => 5, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'i6' => 4, 'i7' => 4, 'i8a' => 3, 'i8b' => 0, 'i9' => 5, 'i10a' => 0, 'i10b' => 3, 'total' => 38, 'nivel' => 'ROJO']);
        AplicacionAsq::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'p1' => 'si', 'p2' => 'si', 'p3' => 'si', 'p4' => 'no', 'p5' => 'si', 'resultado' => 'POSITIVA_AGUDA', 'nivel' => 'ROJO_AGUDO']);
        Clasificacion::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'nivel' => 'ROJO_AGUDO', 'origen' => 'asq']);
        ContactoEmergencia::create(['user_id' => $agudo->user_id, 'nombre' => 'Rosa Poot', 'telefono' => '9994128803', 'relacion' => 'Madre', 'es_principal' => true]);
        EventoCrisis::create(['user_id' => $agudo->user_id, 'institucion_id' => $institucion->id, 'nivel' => 'ROJO_AGUDO', 'origen' => 'asq', 'disparado_en' => now()->subMinutes(12), 'estado' => 'abierto']);

        // Persona que usa la app (flujo del registro diario y bloques de preguntas)
        $persona('Ana Verde', 'ana@prueba.local');

        // Persona con el WHO-5 al día: tras su registro le toca un bloque de la revisión mensual
        $carla = $persona('Carla Revisión', 'carla@prueba.local');
        AplicacionWho5::create(['user_id' => $carla->user_id, 'fecha' => today()->subDays(2), 'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'crudo' => 20, 'escala' => 80, 'origen' => 'programada']);

        // Persona invitada sin activar (pestaña Invitaciones)
        $persona('Beto Invitado', 'beto@prueba.local', 'invitado');

        $this->command?->info("Listo. Clínico/admin: {$clinico->email} · colaboradora: ana@prueba.local · contraseña: " . self::PASSWORD);
    }
}
