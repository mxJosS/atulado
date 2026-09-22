<?php

namespace Tests\Feature;

use App\Models\AplicacionWho5;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\SerieVigilancia;
use App\Models\User;
use App\Services\ClinicalEngineService;
use App\Support\AvisoErrores;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MotorYColaTest extends TestCase
{
    use RefreshDatabase;

    private function registros(User $user, array $dias, int $valor = 1): void
    {
        foreach ($dias as $d) {
            MoodLog::create([
                'user_id' => $user->id, 'score' => 5 - $valor, 'valor_invertido' => $valor,
                'primary_emotion' => 'Calma', 'logged_date' => Carbon::today()->subDays($d),
            ]);
        }
    }

    private function checkin(User $user, int $carita = 5)
    {
        return $this->actingAs($user)->postJson('/mood/checkin', ['score' => $carita, 'primary_emotion' => 'Calma']);
    }

    /* ─────────── WHO-5 programado cada 14 días ─────────── */

    public function test_who5_is_scheduled_every_14_days_even_on_good_days(): void
    {
        $user = User::factory()->create();
        // Revisión mensual al día: no le toca ningún bloque.
        \App\Models\AplicacionPuchol::create(['user_id' => $user->id, 'fecha' => today()->subDays(2), 'estado' => 'completado', 'bloques' => ['A', 'B', 'C', 'D']]);
        AplicacionWho5::create(['user_id' => $user->id, 'fecha' => today()->subDays(15), 'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'crudo' => 20, 'escala' => 80, 'origen' => 'programada']);

        $this->checkin($user)->assertJsonPath('siguiente', 'bloque_1');
        $this->assertSame('programada', session(\App\Http\Controllers\AssessmentController::SESION_ORIGEN));

        AplicacionWho5::query()->update(['fecha' => today()->subDays(5)]);
        $this->checkin($user)->assertJsonMissingPath('siguiente');
    }

    /* ─────────── R4: silencio tras patrón regular ─────────── */

    public function test_silence_after_a_regular_pattern_is_detected_once(): void
    {
        $constante = User::factory()->create();
        $this->registros($constante, range(6, 16)); // 11 días seguidos y luego 6 sin registrar

        $esporadico = User::factory()->create();
        $this->registros($esporadico, [6, 12]); // sin patrón regular

        $reciente = User::factory()->create();
        $this->registros($reciente, range(1, 12)); // aún no llega a 5 días

        $motor = app(ClinicalEngineService::class);
        $this->assertSame(1, $motor->detectarSilencios());
        $this->assertSame(0, $motor->detectarSilencios(), 'No se marca dos veces el mismo silencio');

        $marca = SerieVigilancia::where('ultima_senal', 'R4_SILENCIO')->sole();
        $this->assertSame($constante->id, $marca->user_id);
        $this->assertSame(6, $marca->dias_silencio);
    }

    public function test_returning_after_silence_brings_forward_the_who5(): void
    {
        $user = User::factory()->create();
        $this->registros($user, range(6, 16));
        AplicacionWho5::create(['user_id' => $user->id, 'fecha' => today()->subDays(3), 'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'crudo' => 20, 'escala' => 80, 'origen' => 'programada']);
        app(ClinicalEngineService::class)->detectarSilencios();

        $this->checkin($user, 5)->assertJsonPath('siguiente', 'bloque_1');
        $this->assertSame('adelantada', session(\App\Http\Controllers\AssessmentController::SESION_ORIGEN));
    }

    public function test_silence_command_runs(): void
    {
        $this->artisan('atulado:vigilancia-silencio')->assertSuccessful();
    }

    /* ─────────── Pregunta adicional del bloque 3 (la decide el servidor) ─────────── */

    public function test_block_3_asks_for_the_extra_question_only_when_needed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/preguntas/3', ['p1' => 'si', 'p2' => 'no', 'p3' => 'no', 'p4' => 'no'])
            ->assertJsonPath('siguiente', 'pregunta_extra');
        $this->assertDatabaseCount('aplicaciones_asq', 0);

        $this->actingAs($user)->postJson('/preguntas/3', ['p1' => 'si', 'p2' => 'no', 'p3' => 'no', 'p4' => 'no', 'p5' => 'no'])
            ->assertJsonPath('siguiente', 'apoyo');
        $this->assertDatabaseHas('aplicaciones_asq', ['user_id' => $user->id, 'resultado' => 'POSITIVA_NO_AGUDA']);

        // Todas negativas: una p5 enviada se descarta.
        $this->actingAs($user)->postJson('/preguntas/3', ['p1' => 'no', 'p2' => 'no', 'p3' => 'no', 'p4' => 'no', 'p5' => 'si'])
            ->assertJsonMissingPath('siguiente');
        $this->assertDatabaseHas('aplicaciones_asq', ['user_id' => $user->id, 'resultado' => 'NEGATIVA', 'p5' => null]);
    }

    /* ─────────── Cola de atención manual ─────────── */

    public function test_cases_are_not_marked_notified_until_a_clinician_sees_them(): void
    {
        $persona = User::factory()->create(['name' => 'Luis Chan']);
        $caso = app(ClinicalEngineService::class)->registrarEventoCrisis($persona, ['nivel' => 'ROJO_AGUDO', 'origen' => 'asq']);
        $this->assertNull($caso->notificado_en, 'Sin avisos automáticos no se marca como notificado');

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->get(route('admin.cola.index'))->assertOk()->assertDontSee('Luis Chan');
        $this->assertNull($caso->fresh()->notificado_en, 'Un admin sin acreditación no cuenta como visto');

        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $this->actingAs($clinico)->get(route('admin.cola.index'))->assertOk()->assertSee('Luis Chan')->assertSee('Sin contacto');
        $this->assertNotNull($caso->fresh()->notificado_en);
    }

    public function test_clinician_registers_contact_and_closes_from_the_queue(): void
    {
        $caso = EventoCrisis::create(['user_id' => User::factory()->create()->id, 'nivel' => 'ROJO', 'origen' => 'mdi', 'disparado_en' => now(), 'estado' => 'abierto']);
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.cola.contacto', $caso))->assertForbidden();

        $this->actingAs($clinico)->post(route('admin.cola.cerrar', $caso), ['notas' => 'Intento sin contacto previo.'])->assertSessionHas('error');
        $this->actingAs($clinico)->post(route('admin.cola.contacto', $caso), ['nota' => 'Llamada.'])->assertSessionHas('success');
        $this->actingAs($clinico)->post(route('admin.cola.cerrar', $caso), ['notas' => 'Contacto verificado, a salvo.'])->assertSessionHas('success');

        $this->assertSame('cerrado', $caso->fresh()->estado);
    }

    /* ─────────── Umbral de anonimato ─────────── */

    public function test_reports_work_from_one_person_or_the_threshold_each_institution_chooses(): void
    {
        $inst = Institucion::factory()->create(['umbral_anonimato' => 1]);
        Membresia::factory()->create(['institucion_id' => $inst->id]);
        $motor = app(ClinicalEngineService::class);

        $this->assertTrue($motor->obtenerVistaGerencialAgregada($inst->id)['disponible']);

        $inst->update(['umbral_anonimato' => 5]);
        $this->assertFalse($motor->obtenerVistaGerencialAgregada($inst->id)['disponible']);
    }

    /* ─────────── Avisos de errores ─────────── */

    public function test_production_errors_email_admins_once_per_error(): void
    {
        Mail::fake();
        config(['atulado.alertas_errores' => ['soporte@atulado.com.mx']]);
        $this->app['env'] = 'production';

        $error = new \RuntimeException('Falla de prueba');
        AvisoErrores::avisar($error);
        AvisoErrores::avisar($error);

        Mail::assertSentCount(1);
    }

    public function test_no_error_emails_outside_production(): void
    {
        Mail::fake();
        config(['atulado.alertas_errores' => ['soporte@atulado.com.mx']]);

        AvisoErrores::avisar(new \RuntimeException('Local'));

        Mail::assertNothingSent();
    }
}
