<?php

namespace Tests\Feature;

use App\Models\AplicacionAsq;
use App\Models\AplicacionMdi;
use App\Models\AplicacionWho5;
use App\Models\AuditoriaClinica;
use App\Models\Clasificacion;
use App\Models\EntregaResumen;
use App\Mail\ResumenClinicoMail;
use App\Models\ContactoEmergencia;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\ClinicalEngineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExpedienteClinicoTest extends TestCase
{
    use RefreshDatabase;

    private User $clinico;

    private User $admin;

    private Institucion $institucion;

    protected function setUp(): void
    {
        parent::setUp();

        // Profesional sólo clínico: entra al panel sin ser administrador.
        $this->clinico = User::factory()->create(['is_admin' => false, 'role' => 'clinico', 'is_clinico_atulado' => true, 'name' => 'Said Canul']);
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $this->institucion = Institucion::factory()->create(['profesional_nombre' => 'Psic. Rodrigo Ancona']);
    }

    private function persona(string $nombre, array $membresia = []): Membresia
    {
        return Membresia::factory()->create(array_merge([
            'institucion_id' => $this->institucion->id,
            'user_id' => User::factory()->create(['name' => $nombre])->id,
        ], $membresia));
    }

    private function registros(Membresia $m, array $valores, ?string $texto = null): void
    {
        // $valores[0] es hoy, $valores[1] ayer…
        foreach ($valores as $dias => $valor) {
            $fecha = Carbon::today()->subDays($dias);
            MoodLog::create([
                'user_id' => $m->user_id,
                'score' => 5 - $valor,
                'valor_invertido' => $valor,
                'primary_emotion' => 'Calma',
                'journal_entry' => $dias === 0 ? $texto : null,
                'bandera_lexica' => $dias === 0 && $texto !== null,
                'terminos_detectados' => $dias === 0 && $texto !== null ? ['no quiero vivir'] : [],
                'logged_date' => $fecha,
            ])->forceFill(['created_at' => $fecha->copy()->setTime(8, 30)])->save();
        }
    }

    private function abrirFicha(Membresia $m, string $motivo = 'Revisión de caso rojo abierto')
    {
        return $this->actingAs($this->clinico)
            ->postJson(route('admin.instituciones.ficha', [$this->institucion, $m]), ['motivo' => $motivo]);
    }

    public function test_admin_without_clinical_accreditation_sees_no_clinical_data_and_cannot_open_records(): void
    {
        $m = $this->persona('Luis Manuel Chan Poot');
        Clasificacion::create(['user_id' => $m->user_id, 'fecha' => today(), 'nivel' => 'ROJO_AGUDO', 'origen' => 'asq']);
        AplicacionAsq::create(['user_id' => $m->user_id, 'fecha' => today(), 'p1' => 'si', 'p2' => 'si', 'p3' => 'si', 'p4' => 'no', 'p5' => 'si', 'resultado' => 'POSITIVA_AGUDA', 'nivel' => 'ROJO_AGUDO']);

        // Ve el padrón (operación), nunca el semáforo ni los resultados.
        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $this->institucion))
            ->assertOk()
            ->assertSee('acreditación clínica')
            ->assertSee('Luis Manuel Chan Poot')
            ->assertDontSee('Rojo agudo')
            ->assertDontSee('Positiva aguda')
            ->assertDontSee('data-ficha=', false);

        $this->actingAs($this->admin)
            ->postJson(route('admin.instituciones.ficha', [$this->institucion, $m]), ['motivo' => 'Revisión de caso'])
            ->assertForbidden();
        $this->assertSame(0, AuditoriaClinica::count());
    }

    public function test_list_is_ordered_by_clinical_priority_with_real_scores(): void
    {
        $verde = $this->persona('Ana Verde');
        Clasificacion::create(['user_id' => $verde->user_id, 'fecha' => today(), 'nivel' => 'VERDE', 'origen' => 'who5']);
        $this->registros($verde, [1, 1, 1]);

        $agudo = $this->persona('Zoe Aguda', ['puesto' => 'Oficial albañil', 'horario' => '22:00-06:00']);
        Clasificacion::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'nivel' => 'ROJO_AGUDO', 'origen' => 'asq']);
        AplicacionWho5::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'i1' => 1, 'i2' => 1, 'i3' => 0, 'i4' => 0, 'i5' => 4, 'crudo' => 6, 'escala' => 24, 'origen' => 'ruta_b']);
        AplicacionMdi::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'i1' => 5, 'i2' => 5, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'i6' => 4, 'i7' => 4, 'i8a' => 3, 'i8b' => 0, 'i9' => 5, 'i10a' => 0, 'i10b' => 0, 'total' => 38, 'nivel' => 'ROJO']);
        AplicacionAsq::create(['user_id' => $agudo->user_id, 'fecha' => today(), 'p1' => 'si', 'p2' => 'si', 'p3' => 'si', 'p4' => 'no', 'p5' => 'si', 'resultado' => 'POSITIVA_AGUDA', 'nivel' => 'ROJO_AGUDO']);
        ContactoEmergencia::create(['user_id' => $agudo->user_id, 'nombre' => 'Rosa Poot', 'telefono' => '9994128803', 'relacion' => 'Madre', 'es_principal' => true]);
        $this->registros($agudo, [4, 4, 3], 'ya no quiero vivir así');

        $naranja = $this->persona('Beto Naranja');
        Clasificacion::create(['user_id' => $naranja->user_id, 'fecha' => today(), 'nivel' => 'NARANJA', 'origen' => 'mdi']);

        $respuesta = $this->actingAs($this->clinico)->get(route('admin.instituciones.show', $this->institucion));

        $respuesta->assertOk()->assertSeeInOrder(['Zoe Aguda', 'Beto Naranja', 'Ana Verde']);
        $respuesta->assertSee('Rojo agudo')
            ->assertSee('Positiva aguda')
            ->assertSee('Rosa Poot')
            ->assertSee('bandera léxica')
            ->assertSee('22:00-06:00')
            ->assertSee('3 d'); // racha
    }

    public function test_filters_by_traffic_light_search_and_lexical_flag(): void
    {
        $rojo = $this->persona('Rodrigo Rojo', ['numero_empleado' => '1188']);
        Clasificacion::create(['user_id' => $rojo->user_id, 'fecha' => today(), 'nivel' => 'ROJO', 'origen' => 'mdi']);
        $this->registros($rojo, [3], 'no quiero vivir');
        $verde = $this->persona('Valeria Verde');
        Clasificacion::create(['user_id' => $verde->user_id, 'fecha' => today(), 'nivel' => 'VERDE', 'origen' => 'who5']);

        $url = route('admin.instituciones.show', $this->institucion);

        $this->actingAs($this->clinico)->get($url . '?semaforo=ROJO')->assertSee('Rodrigo Rojo')->assertDontSee('Valeria Verde');
        $this->actingAs($this->clinico)->get($url . '?semaforo=VERDE')->assertSee('Valeria Verde')->assertDontSee('Rodrigo Rojo');
        $this->actingAs($this->clinico)->get($url . '?q=1188')->assertSee('Rodrigo Rojo')->assertDontSee('Valeria Verde');
        $this->actingAs($this->clinico)->get($url . '?q=' . $verde->folio)->assertSee('Valeria Verde')->assertDontSee('Rodrigo Rojo');
        $this->actingAs($this->clinico)->get($url . '?bandera=1')->assertSee('Rodrigo Rojo')->assertDontSee('Valeria Verde');
    }

    public function test_silence_and_open_crisis_drive_the_traffic_light(): void
    {
        $callado = $this->persona('Carlos Callado');
        Clasificacion::create(['user_id' => $callado->user_id, 'fecha' => today()->subDays(20), 'nivel' => 'VERDE', 'origen' => 'who5']);
        $this->registros($callado, [9 => 1]);

        $crisis = $this->persona('Cristina Crisis');
        Clasificacion::create(['user_id' => $crisis->user_id, 'fecha' => today(), 'nivel' => 'AMARILLO', 'origen' => 'who5']);
        EventoCrisis::create(['user_id' => $crisis->user_id, 'institucion_id' => $this->institucion->id, 'nivel' => 'ROJO_AGUDO', 'origen' => 'asq', 'disparado_en' => now(), 'estado' => 'abierto']);

        $this->actingAs($this->clinico)->get(route('admin.instituciones.show', $this->institucion))
            ->assertSeeInOrder(['Cristina Crisis', 'Carlos Callado'])
            ->assertSee('Silencio 9 d')
            ->assertSee('R4 silencio');
    }

    public function test_opening_a_record_requires_a_reason_and_is_logged(): void
    {
        $m = $this->persona('Luis Manuel Chan Poot');

        $this->abrirFicha($m, '')->assertUnprocessable()->assertJsonValidationErrors('motivo');
        $this->assertSame(0, AuditoriaClinica::count());

        $this->abrirFicha($m, 'Revisión de caso rojo agudo abierto')
            ->assertOk()
            ->assertSee('Luis Manuel Chan Poot')
            ->assertSee('Acceso registrado en bitácora')
            ->assertSee('Said Canul')
            ->assertSee('Revisión de caso rojo agudo abierto');

        $this->assertDatabaseHas('auditoria_clinica', [
            'profesional_id' => $this->clinico->id,
            'usuario_consultado_id' => $m->user_id,
            'institucion_id' => $this->institucion->id,
            'accion' => 'consulta_detalle',
            'motivo' => 'Revisión de caso rojo agudo abierto',
        ]);
    }

    public function test_record_shows_every_tab_with_real_answers(): void
    {
        $m = $this->persona('Luis Manuel Chan Poot');
        AplicacionWho5::create(['user_id' => $m->user_id, 'fecha' => today()->subDays(28), 'i1' => 3, 'i2' => 3, 'i3' => 3, 'i4' => 2, 'i5' => 3, 'crudo' => 14, 'escala' => 56, 'origen' => 'programada']);
        AplicacionWho5::create(['user_id' => $m->user_id, 'fecha' => today(), 'i1' => 1, 'i2' => 1, 'i3' => 0, 'i4' => 0, 'i5' => 4, 'crudo' => 6, 'escala' => 24, 'origen' => 'ruta_b']);
        AplicacionMdi::create(['user_id' => $m->user_id, 'fecha' => today(), 'i1' => 5, 'i2' => 5, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'i6' => 4, 'i7' => 4, 'i8a' => 3, 'i8b' => 1, 'i9' => 5, 'i10a' => 0, 'i10b' => 3, 'total' => 38, 'nivel' => 'ROJO']);
        AplicacionAsq::create(['user_id' => $m->user_id, 'fecha' => today(), 'p1' => 'si', 'p2' => 'si', 'p3' => 'si', 'p4' => 'no', 'p5' => 'si', 'resultado' => 'POSITIVA_AGUDA', 'nivel' => 'ROJO_AGUDO']);
        $this->registros($m, [4, 4, 3, 3, 2], 'ya no quiero vivir así');

        $this->abrirFicha($m)->assertOk()
            ->assertSee('-32') // WHO-5 de 56 a 24
            ->assertSee('Crudo 6 / 25 · Escala 24 / 100')
            ->assertSee('Anterior: 56')
            ->assertSee('Total 38 / 50')
            ->assertSee('Ítem 6 (ideación) en 4')
            ->assertSee('POSITIVA AGUDA')
            ->assertSee('¿Está pensando en suicidarse en este momento?')
            ->assertSee('ya no quiero vivir así')
            ->assertSee('«no quiero vivir»', false)
            ->assertSee('Registros en 30 d')
            ->assertSee('ficha-datos');
    }

    public function test_record_of_another_institution_is_not_found(): void
    {
        $ajena = Membresia::factory()->create();

        $this->abrirFicha($ajena)->assertNotFound();
    }

    public function test_escalating_opens_a_crisis_case_and_logs_it(): void
    {
        $m = $this->persona('Luis Manuel Chan Poot');

        $this->actingAs($this->clinico)
            ->post(route('admin.instituciones.ficha.escalar', [$this->institucion, $m]), [
                'nivel' => 'ROJO_AGUDO',
                'justificacion' => 'Refiere plan concreto en la llamada de seguimiento.',
            ])
            ->assertRedirect(route('admin.instituciones.show', $this->institucion) . '#colaboradores')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clasificaciones', ['user_id' => $m->user_id, 'nivel' => 'ROJO_AGUDO', 'origen' => 'manual_clinico']);
        $this->assertDatabaseHas('eventos_crisis', ['user_id' => $m->user_id, 'nivel' => 'ROJO_AGUDO', 'estado' => 'abierto', 'institucion_id' => $this->institucion->id]);
        $this->assertDatabaseHas('auditoria_clinica', ['usuario_consultado_id' => $m->user_id, 'accion' => 'elevacion_nivel']);

        $this->actingAs($this->admin)
            ->post(route('admin.instituciones.ficha.escalar', [$this->institucion, $m]), ['nivel' => 'ROJO', 'justificacion' => 'Sin acreditación clínica.'])
            ->assertForbidden();
    }

    public function test_a_case_closes_only_after_human_contact(): void
    {
        $m = $this->persona('Luis Manuel Chan Poot');
        $caso = EventoCrisis::create(['user_id' => $m->user_id, 'institucion_id' => $this->institucion->id, 'nivel' => 'ROJO', 'origen' => 'mdi', 'disparado_en' => now()->subMinutes(20), 'estado' => 'abierto']);
        $ruta = fn ($accion) => route("admin.instituciones.caso.{$accion}", [$this->institucion, $m, $caso]);

        $this->actingAs($this->clinico)->post($ruta('cerrar'), ['notas' => 'Intento de cierre sin contacto previo.'])
            ->assertSessionHas('error');
        $this->assertSame('abierto', $caso->fresh()->estado);

        $this->actingAs($this->clinico)->post($ruta('contacto'), ['nota' => 'Llamada, está con su madre.'])->assertSessionHas('success');
        $this->assertNotNull($caso->fresh()->contactado_en);
        $this->assertSame('en_atencion', $caso->fresh()->estado);

        $this->actingAs($this->clinico)->post($ruta('cerrar'), ['notas' => 'Contacto verificado, plan de seguridad revisado.'])->assertSessionHas('success');
        $this->assertSame('cerrado', $caso->fresh()->estado);
        $this->assertSame($this->clinico->id, $caso->fresh()->cierre_verificado_por);
    }

    private function pedirResumen(Membresia $m, string $destinatario = 'profesional')
    {
        return $this->actingAs($this->clinico)
            ->post(route('admin.instituciones.ficha.resumen', [$this->institucion, $m]), [
                'destinatario' => $destinatario,
                'motivo' => 'Seguimiento de caso ya abierto',
                'vigencia_horas' => 72,
            ]);
    }

    public function test_summary_is_sent_as_an_expiring_link_to_a_professional_with_valid_nda(): void
    {
        Mail::fake();
        $this->institucion->update(['profesional_email' => 'rancona@consultorio.mx', 'profesional_nda_hasta' => today()->addMonths(6)]);
        $m = $this->persona('Luis Manuel Chan Poot');

        $this->pedirResumen($m)->assertSessionHas('success');

        $entrega = EntregaResumen::sole();
        $this->assertSame('RC-' . now()->format('Y') . '-' . str_pad((string) $m->id, 4, '0', STR_PAD_LEFT) . '-' . $entrega->id, $entrega->folio);
        $this->assertSame('rancona@consultorio.mx', $entrega->destinatario_email);
        $this->assertTrue($entrega->vence_en->between(now()->addHours(71), now()->addHours(73)));
        $this->assertSame('resumen_generado', AuditoriaClinica::latest('id')->first()->accion);

        $url = null;
        Mail::assertQueued(ResumenClinicoMail::class, function ($mail) use (&$url) {
            $url = $mail->url;

            return $mail->hasTo('rancona@consultorio.mx');
        });

        // El correo no lleva datos clínicos; el enlace sí abre el resumen.
        $html = (new ResumenClinicoMail($entrega, $url))->render();
        $this->assertStringNotContainsString('Luis Manuel Chan Poot', $html);

        auth()->logout();
        $this->get($url)->assertOk()
            ->assertSee('Luis Manuel Chan Poot')
            ->assertSee($entrega->folio)
            ->assertSee('Prohibido usar esta información');
        $this->assertSame(1, $entrega->fresh()->aperturas);

        // Vencido, deja de abrir.
        $this->travel(73)->hours();
        $this->get($url)->assertForbidden();
    }

    public function test_summary_is_blocked_without_a_valid_nda(): void
    {
        Mail::fake();
        $this->institucion->update(['profesional_email' => 'rancona@consultorio.mx', 'profesional_nda_hasta' => today()->subDay()]);
        $m = $this->persona('Luis Manuel Chan Poot');

        $this->pedirResumen($m)->assertSessionHas('error');

        $this->assertSame(0, EntregaResumen::count());
        Mail::assertNothingQueued();
    }

    public function test_r1_rule_no_longer_crashes_with_long_history(): void
    {
        $motor = app(ClinicalEngineService::class);

        // 7 días recientes en 3 sobre una base de 1: la desviación dispara R1.
        $serie = array_merge(array_fill(0, 7, 3), array_fill(0, 14, 1));

        $this->assertSame('R1_DESVIACION', $motor->evaluarReglasVigilancia($serie));
    }
}
