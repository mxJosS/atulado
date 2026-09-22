<?php

namespace Tests\Feature;

use App\Models\AplicacionPuchol;
use App\Models\AplicacionWho5;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\SafetyPlan;
use App\Models\User;
use App\Services\PucholService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PucholTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        // WHO-5 reciente: el registro diario no abre otras preguntas y le toca al Puchol.
        AplicacionWho5::create(['user_id' => $this->user->id, 'fecha' => today()->subDays(2), 'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'crudo' => 20, 'escala' => 80, 'origen' => 'programada']);
    }

    private function checkin(int $carita = 5)
    {
        return $this->actingAs($this->user)->postJson('/mood/checkin', ['score' => $carita, 'primary_emotion' => 'Calma']);
    }

    private function responder(string $bloque, int $valor = 1, array $cambios = [])
    {
        $datos = array_fill_keys(PucholService::BLOQUES[$bloque], $valor);

        return $this->actingAs($this->user)->postJson('/preguntas/4', ['bloque' => $bloque] + $cambios + $datos);
    }

    public function test_the_cycle_is_spread_one_short_block_per_day(): void
    {
        foreach (['A', 'B', 'C', 'D'] as $dia => $bloque) {
            $this->checkin()->assertJsonPath('siguiente', 'bloque_4')->assertJsonPath('seccion', $bloque);
            // Sin impulsos suicidas (s1, s2 en 0): el bloque D no abre el protocolo.
            $this->responder($bloque, 1, ['s1' => 0, 's2' => 0])->assertOk()->assertJsonMissingPath('siguiente');

            // El mismo día no se ofrece otro bloque.
            $this->checkin()->assertJsonMissingPath('siguiente');
            $this->travel(1)->days();
        }

        $ciclo = AplicacionPuchol::sole();
        $this->assertSame('completado', $ciclo->estado);
        $this->assertSame(5, $ciclo->ansiedad);
        $this->assertSame(10, $ciclo->fisica);
        $this->assertSame(5, $ciclo->depresion);
        $this->assertSame(0, $ciclo->suicidas);
    }

    public function test_never_on_a_day_with_other_questions(): void
    {
        AplicacionWho5::query()->delete(); // toca el WHO-5 programado

        $this->checkin()->assertJsonPath('siguiente', 'bloque_1')->assertJsonMissingPath('seccion');
        $this->assertSame(0, AplicacionPuchol::count());
    }

    public function test_unanswered_block_is_offered_up_to_three_times_then_waits_for_the_next_cycle(): void
    {
        foreach (range(1, 3) as $vez) {
            $this->checkin()->assertJsonPath('seccion', 'A');
            $this->travel(1)->days();
        }

        $this->checkin()->assertJsonMissingPath('siguiente');
        $this->assertSame('abandonado', AplicacionPuchol::sole()->estado);

        // A los 30 días empieza un ciclo nuevo (con el WHO-5 al día, que si no tendría prioridad).
        $this->travel(30)->days();
        AplicacionWho5::query()->update(['fecha' => today()->subDay()]);
        $this->checkin()->assertJsonPath('seccion', 'A');
        $this->assertSame(2, AplicacionPuchol::count());
    }

    public function test_a_new_cycle_starts_every_30_days(): void
    {
        AplicacionPuchol::create(['user_id' => $this->user->id, 'fecha' => today()->subDays(10), 'estado' => 'completado', 'bloques' => ['A', 'B', 'C', 'D']]);
        $this->checkin()->assertJsonMissingPath('siguiente');

        AplicacionPuchol::query()->update(['fecha' => today()->subDays(30)]);
        $this->checkin()->assertJsonPath('seccion', 'A');
    }

    public function test_high_stress_or_sleep_in_block_2_brings_the_cycle_forward_to_tomorrow(): void
    {
        AplicacionPuchol::create(['user_id' => $this->user->id, 'fecha' => today()->subDays(12), 'estado' => 'completado', 'bloques' => ['A', 'B', 'C', 'D']]);
        $mdi = ['i1' => 1, 'i2' => 1, 'i3' => 1, 'i4' => 1, 'i5' => 1, 'i6' => 0, 'i7' => 1, 'i8a' => 1, 'i8b' => 0, 'i9' => 5, 'i10a' => 1, 'i10b' => 0];

        $this->actingAs($this->user)->postJson('/preguntas/2', $mdi)->assertOk();
        $this->assertSame('adelantado', AplicacionPuchol::latest('id')->first()->origen);

        $this->checkin()->assertJsonMissingPath('siguiente'); // hoy no: mañana
        $this->travel(1)->days();
        $this->checkin()->assertJsonPath('seccion', 'A');
    }

    public function test_suicidal_impulses_open_a_red_case_immediately(): void
    {
        $this->responder('D', 0, ['s2' => 1])->assertJsonPath('siguiente', 'apoyo');

        $this->assertDatabaseHas('eventos_crisis', ['user_id' => $this->user->id, 'nivel' => 'ROJO', 'origen' => 'puchol', 'estado' => 'abierto']);
        $this->assertDatabaseHas('clasificaciones', ['user_id' => $this->user->id, 'nivel' => 'ROJO', 'origen' => 'puchol']);
        $this->assertSame(1, AplicacionPuchol::sole()->suicidas);

        // Con un caso abierto ya no se ofrecen bloques: lo atiende el clínico.
        $this->travel(1)->days();
        $this->checkin()->assertJsonMissingPath('siguiente');
    }

    public function test_the_rest_is_informative_and_does_not_change_the_traffic_light(): void
    {
        $this->responder('A', 4)->assertOk();

        $this->assertSame(20, AplicacionPuchol::sole()->ansiedad);
        $this->assertDatabaseCount('clasificaciones', 0);
        $this->assertSame(0, EventoCrisis::count());
    }

    public function test_answers_are_validated_and_blocks_cannot_repeat(): void
    {
        $this->actingAs($this->user)->postJson('/preguntas/4', ['bloque' => 'A', 'a1' => 9])->assertUnprocessable();
        $this->actingAs($this->user)->postJson('/preguntas/4', ['bloque' => 'Z'])->assertStatus(422);

        $this->responder('A')->assertOk();
        $this->responder('A')->assertUnprocessable();
    }

    public function test_interpretation_keys(): void
    {
        $this->assertSame('Ansiedad marginal', PucholService::interpretar('ansiedad', 3));
        $this->assertSame('Ansiedad extrema', PucholService::interpretar('ansiedad', 20));
        $this->assertSame('Algunos síntomas físicos de ansiedad', PucholService::interpretar('fisica', 5));
        $this->assertSame('Síntomas físicos de ansiedad fuertes', PucholService::interpretar('fisica', 25));
        $this->assertNull(PucholService::interpretar('depresion', 12), 'Depresión no tiene clave');
        $this->assertSame('Sin impulsos referidos', PucholService::interpretar('suicidas', 0));
        $this->assertStringContainsString('protocolo', PucholService::interpretar('suicidas', 1));
    }

    /* ─────────── Plan de seguridad ─────────── */

    public function test_safety_plan_lists_pending_parts_and_answering_there_counts(): void
    {
        $this->actingAs($this->user)->get('/plan-de-seguridad')
            ->assertOk()->assertSee('Revisión del mes')->assertSee('Parte 1')->assertSee('Parte 4');

        // Puede hacer varias partes el mismo día desde el plan.
        $this->responder('A')->assertOk();
        $this->responder('B')->assertOk();

        $this->actingAs($this->user)->get('/plan-de-seguridad')->assertDontSee('Parte 1 ')->assertSee('Parte 3');
        // Y el registro diario ya no insiste hoy.
        $this->checkin()->assertJsonMissingPath('siguiente');
    }

    public function test_plan_suggests_tools_without_showing_scores_and_can_add_them(): void
    {
        $this->responder('B', 2)->assertOk();
        $this->responder('C', 2)->assertOk(); // física = 20 → sugiere respiración y anclaje

        $this->actingAs($this->user)->get('/plan-de-seguridad')
            ->assertSee('Respiración 4-7-8')->assertSee('Anclaje 5-4-3-2-1')
            ->assertDontSee('20 / 40')->assertDontSee('moderados');

        $this->actingAs($this->user)->post(route('safety-plan.sugerencia'), ['clave' => 'grounding'])->assertRedirect(route('safety-plan.show'));
        $this->assertContains('Anclaje 5-4-3-2-1 (herramienta de A Tu Lado)', SafetyPlan::where('user_id', $this->user->id)->value('internal_coping'));

        // Ya agregada, deja de sugerirse (usuario recién cargado, como en una petición real).
        $this->actingAs($this->user->fresh())->get('/plan-de-seguridad')->assertDontSee('Anclaje 5-4-3-2-1</b>', false);
    }

    /* ─────────── Plano clínico ─────────── */

    public function test_clinical_record_shows_the_four_sections_with_their_keys(): void
    {
        $institucion = Institucion::factory()->create();
        $membresia = Membresia::factory()->create(['institucion_id' => $institucion->id, 'user_id' => $this->user->id]);
        $this->responder('A', 2)->assertOk();
        $this->responder('B', 3)->assertOk();
        $this->responder('C', 2)->assertOk();

        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $this->actingAs($clinico)
            ->postJson(route('admin.instituciones.ficha', [$institucion, $membresia]), ['motivo' => 'Seguimiento clínico programado'])
            ->assertOk()
            ->assertSee('Puchol · test breve del estado de ánimo')
            ->assertSee('10 / 20')->assertSee('Ansiedad moderada')
            ->assertSee('25 / 40')->assertSee('Síntomas físicos de ansiedad fuertes')
            ->assertSee('Aún sin responder en este ciclo.');
    }
}
