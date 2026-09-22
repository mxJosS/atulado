<?php

namespace Tests\Feature;

use App\Models\AuditoriaClinica;
use App\Models\Clasificacion;
use App\Models\EntregaResumen;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\SerieVigilancia;
use App\Models\User;
use App\Services\ClinicalEngineService;
use App\Services\ExpedienteClinicoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Cambios pedidos tras la prueba del panel institucional: casos sin
 * duplicados, registro diario único, doble auditoría, reapertura de casos,
 * orden por prioridad, activación al primer ingreso, archivado de
 * instituciones y resumen clínico imprimible.
 */
class CambiosPanelInstitucionalTest extends TestCase
{
    use RefreshDatabase;

    private ClinicalEngineService $motor;

    private Institucion $institucion;

    private User $clinico;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->motor = app(ClinicalEngineService::class);
        $this->institucion = Institucion::factory()->create(['nombre_corto' => 'UTM']);
        $this->clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true, 'name' => 'Efraín Herrera']);
        $this->clinico->institucionesAsignadas()->attach($this->institucion);
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
    }

    private function persona(string $nombre, array $membresia = [], ?Institucion $institucion = null): Membresia
    {
        return Membresia::factory()->create(array_merge([
            'institucion_id' => ($institucion ?? $this->institucion)->id,
            'user_id' => User::factory()->create(['name' => $nombre])->id,
        ], $membresia));
    }

    private function caso(Membresia $m, array $datos = []): EventoCrisis
    {
        return EventoCrisis::create(array_merge([
            'user_id' => $m->user_id, 'institucion_id' => $m->institucion_id, 'nivel' => 'ROJO',
            'origen' => 'mdi', 'disparado_en' => now()->subMinutes(30), 'estado' => 'abierto',
        ], $datos));
    }

    /* ─────────── #2 · Un solo caso abierto por persona ─────────── */

    public function test_a_second_trigger_reuses_the_open_case_and_escalates_its_level(): void
    {
        $persona = User::factory()->create();

        $primero = $this->motor->registrarEventoCrisis($persona, ['nivel' => 'ROJO', 'origen' => 'mdi']);
        $primero->update(['notificado_en' => now()]);

        $segundo = $this->motor->registrarEventoCrisis($persona, ['nivel' => 'ROJO', 'origen' => 'mdi']);
        $this->assertSame($primero->id, $segundo->id);
        $this->assertSame(1, EventoCrisis::where('user_id', $persona->id)->count());

        $agudo = $this->motor->registrarEventoCrisis($persona, ['nivel' => 'ROJO_AGUDO', 'origen' => 'asq']);
        $this->assertSame($primero->id, $agudo->id);
        $this->assertSame('ROJO_AGUDO', $primero->fresh()->nivel);
        $this->assertNull($primero->fresh()->notificado_en, 'Al subir de nivel vuelve a aparecer como no visto en la cola');

        // Un nivel menor no baja el caso.
        $this->motor->registrarEventoCrisis($persona, ['nivel' => 'ROJO']);
        $this->assertSame('ROJO_AGUDO', $primero->fresh()->nivel);
    }

    public function test_after_closing_a_new_trigger_opens_a_new_case(): void
    {
        $persona = User::factory()->create();
        $caso = $this->motor->registrarEventoCrisis($persona);
        $this->motor->registrarContactoHumano($caso, $this->clinico);
        $this->motor->verificarCierreCaso($caso, $this->clinico, 'Contacto verificado, a salvo.');

        $nuevo = $this->motor->registrarEventoCrisis($persona);
        $this->assertNotSame($caso->id, $nuevo->id);
    }

    /* ─────────── #6 · Un registro diario por día ─────────── */

    public function test_submitting_the_daily_log_twice_keeps_one_row_and_one_series_per_day(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/mood/checkin', ['score' => 4, 'primary_emotion' => 'Calma'])->assertOk();
        $this->actingAs($user)->postJson('/mood/checkin', ['score' => 2, 'primary_emotion' => 'Tristeza'])->assertOk();

        $this->assertSame(1, MoodLog::where('user_id', $user->id)->count());
        $this->assertSame(2, MoodLog::where('user_id', $user->id)->value('score'), 'El segundo envío actualiza el día');
        $this->assertSame(1, SerieVigilancia::where('user_id', $user->id)->count());
    }

    /* ─────────── #15 · Doble auditoría ─────────── */

    public function test_two_clinicians_on_the_same_case_are_both_audited_without_overwriting_the_first(): void
    {
        $otro = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true, 'name' => 'Rosa Pech']);
        $m = $this->persona('Luis Chan');
        $caso = $this->caso($m);
        $copiaVieja = $caso->replicate()->forceFill(['id' => $caso->id]); // lo que tenía en pantalla el segundo clínico

        $this->assertTrue($this->motor->registrarContactoHumano($caso, $this->clinico, 'Primera llamada.'));
        $primerContacto = $caso->fresh()->contactado_en;

        $this->travel(5)->minutes();
        $this->assertTrue($this->motor->registrarContactoHumano($copiaVieja, $otro, 'Segunda llamada.'));

        $caso->refresh();
        $this->assertSame($this->clinico->id, $caso->primer_contacto_por);
        $this->assertTrue($caso->contactado_en->equalTo($primerContacto));
        $this->assertSame(2, AuditoriaClinica::where('accion', 'registro_contacto')->count());
        $this->assertStringContainsString('Contacto adicional', AuditoriaClinica::where('profesional_id', $otro->id)->value('detalle'));

        // Cierre: el primero cierra; el segundo no lo pisa, pero su nota queda en la bitácora.
        $this->assertTrue($this->motor->verificarCierreCaso($caso, $this->clinico, 'Contacto verificado, está con su familia.'));
        $this->assertTrue($this->motor->verificarCierreCaso($copiaVieja, $otro, 'Yo también hablé con él, sigue a salvo.'));

        $caso->refresh();
        $this->assertSame('cerrado', $caso->estado);
        $this->assertSame($this->clinico->id, $caso->cierre_verificado_por);
        $this->assertSame('Contacto verificado, está con su familia.', $caso->notas_cierre);
        $this->assertSame(2, AuditoriaClinica::where('accion', 'cierre_crisis')->count());
        $this->assertStringContainsString('Nota de cierre adicional', AuditoriaClinica::where('accion', 'cierre_crisis')->where('profesional_id', $otro->id)->value('detalle'));

        // Sobre un caso cerrado ya no se registran contactos.
        $this->assertFalse($this->motor->registrarContactoHumano($caso, $otro));
    }

    /* ─────────── Reabrir casos ─────────── */

    public function test_a_closed_red_case_can_be_reopened_from_the_queue_and_the_record(): void
    {
        $m = $this->persona('Luis Chan');
        $caso = $this->caso($m, ['nivel' => 'ROJO_AGUDO', 'contactado_en' => now()->subMinutes(20), 'primer_contacto_por' => $this->clinico->id,
            'estado' => 'cerrado', 'cierre_verificado_por' => $this->clinico->id, 'notas_cierre' => 'A salvo con su madre.']);

        $this->actingAs($this->admin)->post(route('admin.cola.reabrir', $caso), ['motivo' => 'Volvió a escribir que no quiere vivir.'])->assertForbidden();
        $this->actingAs($this->clinico)->post(route('admin.cola.reabrir', $caso), ['motivo' => 'corto'])->assertSessionHasErrors('motivo');

        $this->actingAs($this->clinico)->post(route('admin.cola.reabrir', $caso), ['motivo' => 'Volvió a escribir que no quiere vivir.'])
            ->assertSessionHas('success');

        $caso->refresh();
        $this->assertSame('en_atencion', $caso->estado);
        $this->assertNull($caso->cierre_verificado_por);
        $this->assertNotNull($caso->contactado_en, 'Se conserva la hora del primer contacto');
        $auditoria = AuditoriaClinica::where('accion', 'reapertura_crisis')->sole();
        $this->assertStringContainsString('A salvo con su madre.', $auditoria->detalle);
        $this->assertSame('Reapertura de caso', $auditoria->accion_legible);

        // Ya abierto: no se reabre otra vez; desde la ficha tampoco.
        $this->actingAs($this->clinico)
            ->post(route('admin.instituciones.caso.reabrir', [$this->institucion, $m, $caso]), ['motivo' => 'Intento de reabrir lo ya abierto.'])
            ->assertSessionHas('error');
    }

    public function test_a_case_is_not_reopened_while_the_person_has_another_open_case(): void
    {
        $m = $this->persona('Luis Chan');
        $cerrado = $this->caso($m, ['estado' => 'cerrado', 'contactado_en' => now()->subDay(), 'cierre_verificado_por' => $this->clinico->id]);
        $this->caso($m);

        $this->assertSame('otro_abierto', $this->motor->reabrirCaso($cerrado, $this->clinico, 'Reapertura de prueba.'));
        $this->assertSame('cerrado', $cerrado->fresh()->estado);
    }

    /* ─────────── Orden por prioridad clínica ─────────── */

    public function test_the_clinical_list_is_sorted_by_priority(): void
    {
        $hoy = Carbon::today();
        $conNivel = function (string $nombre, ?string $nivel, ?int $diasSinRegistro = 0) use ($hoy) {
            $m = $this->persona($nombre);
            if ($diasSinRegistro !== null) {
                MoodLog::create(['user_id' => $m->user_id, 'score' => 3, 'valor_invertido' => 2, 'primary_emotion' => 'Calma', 'logged_date' => $hoy->copy()->subDays($diasSinRegistro)])
                    ->forceFill(['created_at' => $hoy->copy()->subDays($diasSinRegistro)->setTime(9, 0)])->save();
            }
            if ($nivel) {
                Clasificacion::create(['user_id' => $m->user_id, 'fecha' => $hoy, 'nivel' => $nivel, 'origen' => 'mdi', 'banderas' => []]);
            }

            return $m;
        };

        $conNivel('Ana Verde', 'VERDE');
        $conNivel('Beto Sin Datos', null, null);
        $conNivel('Carla Naranja', 'NARANJA');
        $conNivel('Dora Silencio', 'VERDE', 8);
        $conNivel('Eva Amarillo', 'AMARILLO');
        $this->caso($conNivel('Fer Rojo', 'ROJO'));
        $this->caso($conNivel('Gil Agudo', 'NARANJA'), ['nivel' => 'ROJO_AGUDO']);

        $orden = app(ExpedienteClinicoService::class)->colaboradores($this->institucion, [])
            ->getCollection()->map(fn ($f) => $f['membresia']->user->name)->all();

        $this->assertSame(['Gil Agudo', 'Fer Rojo', 'Carla Naranja', 'Eva Amarillo', 'Dora Silencio', 'Ana Verde', 'Beto Sin Datos'], $orden);
    }

    /* ─────────── Activación al primer ingreso y conteos sin personal ─────────── */

    public function test_a_padron_account_becomes_active_the_first_time_the_person_logs_in(): void
    {
        $m = $this->persona('Luis Chan', ['estado' => 'invitado', 'activado_en' => null]);
        $m->user->update(['email' => 'luis@utm.mx']);

        $this->actingAs($this->admin)->get(route('admin.users.index'))->assertSee('Sin activar');
        auth()->logout();

        $this->post('/login', ['email' => 'luis@utm.mx', 'password' => 'password'])->assertRedirect();

        $m->refresh();
        $this->assertSame('activo', $m->estado);
        $this->assertNotNull($m->activado_en);
        $this->assertSame('activo', $m->user->fresh()->estadoCuenta());
    }

    public function test_marking_a_user_active_in_the_users_menu_also_activates_the_padron(): void
    {
        $m = $this->persona('Luis Chan', ['estado' => 'invitado', 'activado_en' => null]);

        $this->actingAs($this->admin)->put(route('admin.users.update', $m->user), [
            'name' => 'Luis Chan', 'email' => $m->user->email, 'role' => 'usuario', 'status' => 'activo',
            'institucion_id' => $this->institucion->id,
        ])->assertSessionHas('success');

        $this->assertSame('activo', $m->fresh()->estado);
    }

    public function test_admins_and_clinicians_in_a_padron_do_not_count(): void
    {
        $this->persona('Colaborador Uno');
        $this->persona('Colaborador Dos', ['estado' => 'invitado', 'activado_en' => null]);
        Membresia::factory()->create(['institucion_id' => $this->institucion->id, 'user_id' => $this->admin->id]);
        Membresia::factory()->create(['institucion_id' => $this->institucion->id, 'user_id' => $this->clinico->id]);

        $inst = collect($this->actingAs($this->admin)->get(route('admin.instituciones.index'))->assertOk()->viewData('instituciones'))->firstWhere('id', $this->institucion->id);
        $this->assertSame(2, $inst->padron_count);
        $this->assertSame(1, $inst->activas_count);

        $resumen = $this->actingAs($this->admin)->get(route('admin.instituciones.show', $this->institucion))->viewData('resumen');
        $this->assertSame(2, $resumen['padron']);
        $this->assertSame(1, $resumen['activas']);
    }

    /* ─────────── Archivar y eliminar instituciones ─────────── */

    public function test_deleting_an_institution_archives_it_and_it_can_be_restored(): void
    {
        $m = $this->persona('Luis Chan');

        $this->actingAs($this->clinico)->post(route('admin.instituciones.archivar', $this->institucion))->assertRedirect(route('dashboard'));
        $this->assertFalse($this->institucion->fresh()->estaArchivada(), 'Sólo la administración archiva');

        $this->actingAs($this->admin)->post(route('admin.instituciones.archivar', $this->institucion))->assertSessionHas('success');

        $this->assertTrue($this->institucion->fresh()->estaArchivada());
        $this->assertDatabaseHas('users', ['id' => $m->user_id]);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.instituciones.index'))->assertOk();
        $this->assertTrue($respuesta->viewData('archivadas')->contains('id', $this->institucion->id));
        $this->assertFalse($respuesta->viewData('instituciones')->contains('id', $this->institucion->id));

        // Se sigue viendo completa, pero no admite altas.
        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $this->institucion))->assertOk()->assertSee('Institución archivada');
        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.store', $this->institucion), [])->assertSessionHas('error');

        $this->actingAs($this->admin)->post(route('admin.instituciones.restaurar', $this->institucion))->assertSessionHas('success');
        $this->assertFalse($this->institucion->fresh()->estaArchivada());
    }

    public function test_permanent_deletion_only_from_archive_with_confirmation_and_without_open_cases(): void
    {
        $solo = $this->persona('Solo Aquí');
        $otra = Institucion::factory()->create();
        $compartida = $this->persona('En Dos Instituciones');
        Membresia::factory()->create(['institucion_id' => $otra->id, 'user_id' => $compartida->user_id]);
        Membresia::factory()->create(['institucion_id' => $this->institucion->id, 'user_id' => $this->admin->id]);
        MoodLog::create(['user_id' => $solo->user_id, 'score' => 3, 'primary_emotion' => 'Calma', 'logged_date' => today()]);

        $borrar = fn (string $confirmacion) => $this->actingAs($this->admin)
            ->delete(route('admin.instituciones.destroy', $this->institucion), ['confirmacion' => $confirmacion]);

        // Sin archivar no se elimina.
        $borrar('UTM')->assertSessionHas('error');
        $this->assertDatabaseHas('instituciones', ['id' => $this->institucion->id]);

        $this->institucion->forceFill(['archivada_en' => now()])->save();

        $borrar('utm')->assertSessionHas('error'); // el nombre debe coincidir

        $caso = $this->caso($solo);
        $borrar('UTM')->assertSessionHas('error'); // hay un caso abierto
        $caso->update(['estado' => 'cerrado', 'contactado_en' => now(), 'cierre_verificado_por' => $this->clinico->id]);

        $borrar('UTM')->assertSessionHas('success');

        $this->assertDatabaseMissing('instituciones', ['id' => $this->institucion->id]);
        $this->assertDatabaseMissing('users', ['id' => $solo->user_id]);
        $this->assertDatabaseMissing('mood_logs', ['user_id' => $solo->user_id]);
        $this->assertDatabaseHas('users', ['id' => $compartida->user_id]);
        $this->assertDatabaseHas('membresias', ['user_id' => $compartida->user_id, 'institucion_id' => $otra->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
        $this->assertDatabaseHas('users', ['id' => $this->clinico->id]);
    }

    /* ─────────── Resumen clínico imprimible ─────────── */

    public function test_summary_scope_changes_the_document_and_opens_the_printable_viewer(): void
    {
        Mail::fake();
        $this->institucion->update(['profesional_email' => 'eherrera@consultorio.mx', 'profesional_nda_hasta' => today()->addYear()]);
        $m = $this->persona('Luis Manuel Chan Poot', ['turno' => 'nocturno', 'horario' => '22:00-06:00']);
        MoodLog::create(['user_id' => $m->user_id, 'score' => 1, 'valor_invertido' => 4, 'primary_emotion' => 'Tristeza',
            'journal_entry' => 'ya no le veo salida a esto', 'bandera_lexica' => true, 'terminos_detectados' => ['sin salida'], 'logged_date' => today()]);
        $this->caso($m, ['nivel' => 'ROJO_AGUDO', 'origen' => 'asq']);

        $pedir = fn (array $alcance) => $this->actingAs($this->clinico)
            ->post(route('admin.instituciones.ficha.resumen', [$this->institucion, $m]), [
                'destinatario' => 'profesional', 'motivo' => 'Protocolo de crisis activo', 'vigencia_horas' => 72, 'alcance' => $alcance,
            ]);

        // Alcance base: 4 páginas, con respuestas y texto libre.
        $pedir(['trayectoria', 'respuestas', 'texto', 'recursos'])->assertRedirect();
        $base = EntregaResumen::latest('id')->first();
        $this->assertSame(4, $base->paginas);
        $this->assertSame(['trayectoria', 'respuestas', 'texto', 'recursos'], $base->alcance);
        $this->assertStringContainsString('Por qué se activó el protocolo', $base->contenido);
        $this->assertStringContainsString('ya no le veo salida a esto', $base->contenido);
        $this->assertStringContainsString('Desesperanza', $base->contenido);
        $this->assertStringContainsString('Página 4 de 4', $base->contenido);
        $this->assertStringContainsString('Céd. ' . $this->institucion->profesional_cedula, $base->contenido);

        // Sin texto libre ni respuestas, esas secciones no aparecen.
        $pedir([])->assertRedirect();
        $minimo = EntregaResumen::latest('id')->first();
        $this->assertStringNotContainsString('ya no le veo salida a esto', $minimo->contenido);
        $this->assertStringNotContainsString('WHO-5, respuestas íntegras', $minimo->contenido);

        // Con 12 meses se agrega el anexo.
        $respuesta = $pedir(['trayectoria', 'respuestas', 'texto', 'recursos', 'historial']);
        $completo = EntregaResumen::latest('id')->first();
        $respuesta->assertRedirect(route('admin.resumenes.ver', $completo));
        $this->assertGreaterThan(4, $completo->paginas);
        $this->assertStringContainsString('Historial completo de 12 meses', $completo->contenido);

        // El visor muestra el documento con «Emitir otro» y «Descargar PDF», y deja huella.
        $this->actingAs($this->clinico)->get(route('admin.resumenes.ver', $completo))->assertOk()
            ->assertSee('Emitir otro')->assertSee('Descargar PDF')->assertSee($completo->folio);
        $this->assertSame('resumen_consultado', AuditoriaClinica::latest('id')->value('accion'));

        // Sin acreditación clínica no se abre.
        $this->actingAs($this->admin)->get(route('admin.resumenes.ver', $completo))->assertForbidden();
    }
}
