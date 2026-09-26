<?php

namespace Tests\Feature;

use App\Models\AplicacionWho5;
use App\Models\Clasificacion;
use App\Models\Departamento;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Interaccion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\AnaliticaService;
use App\Services\PlataformaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Barra del semáforo de las tarjetas, indicadores del detalle de la
 * institución y panel «Analítica e índices».
 */
class IndicadoresYAnaliticaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
    }

    private function persona(Institucion $institucion, string $nombre, ?Departamento $area = null, string $estado = 'activo'): User
    {
        $user = User::factory()->create(['name' => $nombre, 'email' => Str::slug($nombre) . '@prueba.mx']);
        Membresia::factory()->create([
            'institucion_id' => $institucion->id,
            'user_id' => $user->id,
            'departamento_id' => $area?->id,
            'estado' => $estado,
            'activado_en' => $estado === 'activo' ? now()->subDays(60) : null,
        ]);

        return $user;
    }

    private function nivel(User $user, string $nivel, int $diasAtras = 0): void
    {
        Clasificacion::create(['user_id' => $user->id, 'fecha' => today()->subDays($diasAtras), 'nivel' => $nivel, 'origen' => 'who5']);
    }

    private function caso(User $user, Institucion $institucion, string $nivel, array $extra = []): EventoCrisis
    {
        return EventoCrisis::create(array_merge([
            'user_id' => $user->id, 'institucion_id' => $institucion->id, 'nivel' => $nivel, 'origen' => 'asq',
            'disparado_en' => now()->subHour(), 'estado' => 'abierto',
        ], $extra));
    }

    private function who5(User $user, Carbon $fecha, int $escala): void
    {
        AplicacionWho5::create([
            'user_id' => $user->id, 'fecha' => $fecha, 'i1' => 0, 'i2' => 0, 'i3' => 0, 'i4' => 0, 'i5' => 0,
            'crudo' => intdiv($escala, 4), 'escala' => $escala, 'origen' => 'programada',
        ]);
    }

    private function registro(User $user, int $diasAtras): void
    {
        MoodLog::create([
            'user_id' => $user->id, 'score' => 4, 'primary_emotion' => 'Calma',
            'logged_date' => today()->subDays($diasAtras),
        ]);
    }

    /* ─────────── Barra del semáforo en las tarjetas ─────────── */

    public function test_an_open_case_paints_red_and_active_people_without_level_show_in_grey(): void
    {
        // Umbral alto: antes ocultaba la barra completa.
        $itm = Institucion::factory()->create(['nombre_corto' => 'ITM', 'umbral_anonimato' => 10]);
        $ana = $this->persona($itm, 'Ana Uc');
        $beto = $this->persona($itm, 'Beto Pech');
        $this->persona($itm, 'Carla Chi');
        $this->persona($itm, 'Dora May');
        $this->persona($itm, 'Eva Can', null, 'invitado');

        $this->nivel($ana, 'VERDE');
        // Beto no tiene clasificación: su caso vino del botón de crisis.
        $this->caso($beto, $itm, 'ROJO_AGUDO', ['origen' => null]);

        $datos = app(PlataformaService::class)->resumen('7');
        $tarjeta = collect($datos['instituciones'])->firstWhere('nombre', 'ITM');

        $this->assertTrue($tarjeta['semaforo_visible']);
        $this->assertSame(4, $tarjeta['semaforo_base']);
        $this->assertSame(1, $tarjeta['semaforo']['VERDE']);
        $this->assertSame(1, $tarjeta['semaforo']['ROJO_AGUDO']);
        $this->assertSame(2, $tarjeta['semaforo'][PlataformaService::SIN_CLASIFICAR]);
        $this->assertSame(1, $datos['semaforo']['conteos']['ROJO_AGUDO']);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Rojo agudo: 1 (25%)')
            ->assertSee('Sin clasificar: 2 (50%)')
            ->assertSee('Verde: 1 (25%)');
    }

    public function test_one_classified_person_no_longer_fills_the_whole_bar(): void
    {
        $pruebas = Institucion::factory()->create(['nombre_corto' => 'PRUEBAS']);
        $ana = $this->persona($pruebas, 'Ana Uc');
        $this->persona($pruebas, 'Beto Pech');
        $this->nivel($ana, 'ROJO');

        $tarjeta = collect(app(PlataformaService::class)->resumen('7')['instituciones'])->firstWhere('nombre', 'PRUEBAS');

        $this->assertSame(['VERDE' => 0, 'AMARILLO' => 0, 'NARANJA' => 0, 'ROJO' => 1, 'ROJO_AGUDO' => 0, 'SIN_CLASIFICAR' => 1], $tarjeta['semaforo']);
    }

    /* ─────────── Detalle de la institución ─────────── */

    public function test_institution_detail_shows_semaforo_by_area_queue_and_protocol(): void
    {
        $inst = Institucion::factory()->create(['nombre_corto' => 'Mayab', 'profesional_nda_hasta' => today()->addMonths(3)]);
        $obra = Departamento::factory()->para($inst)->create(['nombre' => 'Obra']);
        $nocturna = Departamento::factory()->para($inst)->create(['nombre' => 'Cuadrilla nocturna', 'parent_id' => $obra->id]);
        $oficina = Departamento::factory()->para($inst)->create(['nombre' => 'Oficina']);

        $ana = $this->persona($inst, 'Ana Uc', $nocturna);
        $beto = $this->persona($inst, 'Beto Pech', $nocturna);
        $carla = $this->persona($inst, 'Carla Chi', $oficina);
        $this->persona($inst, 'Dora May', $oficina);

        $this->nivel($ana, 'AMARILLO');
        $this->nivel($carla, 'VERDE');
        $this->who5($ana, today(), 40);
        $this->who5($carla, today(), 72);
        $abierto = $this->caso($beto, $inst, 'ROJO_AGUDO');
        // Caso del trimestre ya cerrado: contacto a los 12 minutos y cierre verificado.
        $this->caso($carla, $inst, 'ROJO', [
            'disparado_en' => now()->subDays(20), 'contactado_en' => now()->subDays(20)->addMinutes(12),
            'estado' => 'cerrado', 'cierre_verificado_por' => $this->admin->id,
        ]);

        // Administración sin acreditación clínica: sólo el protocolo (operativo), nada del semáforo ni de la cola.
        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $inst))->assertOk()
            ->assertSee('Plano clínico acreditado')
            ->assertDontSee('Obra — Cuadrilla nocturna')
            ->assertDontSee(\App\Services\ExpedienteClinicoService::codigoCaso($abierto))
            ->assertSee('Cumplimiento del protocolo')
            ->assertSee('1 / 2')                  // contacto < 30 min y cierre verificado
            ->assertSee('1 en curso')
            ->assertSee('NDA vigente')
            ->assertSee(route('admin.analitica', ['institucion' => $inst->slug]), false);

        // Con acreditación clínica: semáforo por área y cola con el nombre de la persona.
        $this->admin->update(['is_clinico_atulado' => true]);
        $this->actingAs($this->admin->fresh())->get(route('admin.instituciones.show', $inst))->assertOk()
            ->assertSee('Semáforo por departamento')
            ->assertSee('Obra — Cuadrilla nocturna')
            ->assertSee('Obra — Cuadrilla nocturna concentra el 100% de las alertas abiertas con el 50% del padrón.')
            ->assertSee('Cola de atención clínica')
            ->assertSee(\App\Services\ExpedienteClinicoService::codigoCaso($abierto))
            ->assertSee('ASQ positiva · aguda')
            ->assertSee('Beto Pech. Protocolo activo', false)
            ->assertSee('Cumplimiento del protocolo');
    }

    /* ─────────── Analítica e índices ─────────── */

    public function test_only_administration_opens_analytics(): void
    {
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $usuario = User::factory()->create();

        $this->get(route('admin.analitica'))->assertRedirect(route('login'));
        $this->actingAs($usuario)->get(route('admin.analitica'))->assertRedirect(route('dashboard'));
        $this->actingAs($clinico)->get(route('admin.analitica'))->assertRedirect(route('dashboard'));

        $this->actingAs($this->admin)->get(route('admin.analitica'))->assertOk()
            ->assertSee('Indicadores y analítica')
            ->assertSee('Todas las instituciones')
            ->assertSee('Índice de Bienestar Institucional')
            ->assertSee('Índice de Riesgo Concentrado')
            ->assertSee('Índice de Respuesta Operativa');
        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertSee('Analítica e índices');
    }

    public function test_analytics_computes_the_three_indexes_and_charts(): void
    {
        $inst = Institucion::factory()->create(['nombre_corto' => 'Mayab']);
        $otra = Institucion::factory()->create(['nombre_corto' => 'Peninsular']);
        $nocturna = Departamento::factory()->para($inst)->create(['nombre' => 'Nocturna']);
        $oficina = Departamento::factory()->para($inst)->create(['nombre' => 'Oficina']);

        $ana = $this->persona($inst, 'Ana Uc', $nocturna);
        $beto = $this->persona($inst, 'Beto Pech', $nocturna);
        $carla = $this->persona($inst, 'Carla Chi', $oficina);
        $dora = $this->persona($inst, 'Dora May', $oficina);
        $this->persona($otra, 'Eva Can');

        // Semáforo: 2 verdes, 1 amarillo y 1 sin nivel → (2 + 0.5) / 3 = 83
        $this->nivel($ana, 'VERDE');
        $this->nivel($carla, 'VERDE');
        $this->nivel($dora, 'AMARILLO');
        // Hace un mes: Ana estaba en rojo y bajó a verde.
        $this->nivel($ana, 'ROJO', 35);

        // WHO-5 promedio (60 + 80) / 2 = 70
        $this->who5($ana, today()->subDays(3), 60);
        $this->who5($carla, today()->subDays(3), 80);

        // Registro diario de 7 días seguidos de Ana y uno de Carla.
        foreach (range(0, 6) as $d) {
            $this->registro($ana, $d);
        }
        $this->registro($carla, 2);

        // Uso de herramientas: 1 de 4 activos → 25
        Interaccion::create(['user_id' => $ana->id, 'institucion_id' => $inst->id, 'modulo' => 'herramienta', 'ocurrido_en' => now()->setTime(23, 15)]);
        Interaccion::create(['user_id' => $carla->id, 'institucion_id' => $inst->id, 'modulo' => 'revista', 'ocurrido_en' => now()->setTime(10, 0)]);

        // Dos casos, ambos en la cuadrilla nocturna: uno contactado a tiempo y cerrado, otro abierto sin contacto.
        $this->caso($ana, $inst, 'ROJO', [
            'disparado_en' => now()->subDays(10), 'contactado_en' => now()->subDays(10)->addMinutes(20),
            'estado' => 'cerrado', 'cierre_verificado_por' => $this->admin->id,
        ]);
        $this->caso($beto, $inst, 'ROJO_AGUDO', ['disparado_en' => now()->subDays(2)]);

        $datos = app(AnaliticaService::class)->calcular(collect([$inst]), '90');

        $this->assertSame(4, $datos['poblacion']['padron']);
        $this->assertSame(70, $datos['ibi']['componentes']['who5']);
        // Beto cuenta en rojo agudo por su caso abierto: (2 + 0.5) / 4
        $this->assertSame(63, $datos['ibi']['componentes']['semaforo']);
        $this->assertSame(25, $datos['ibi']['componentes']['herramientas']);
        $this->assertNotNull($datos['ibi']['valor']);

        // Todas las alertas en una de dos áreas del mismo tamaño → Gini 0.5
        $this->assertSame(50, $datos['irc']['valor']);
        $this->assertSame([['area' => 'Nocturna', 'pct' => 100]], $datos['irc']['reparto']);

        // Contacto a tiempo 1/2, cierre verificado 1/1, sin salidas sin contacto
        $this->assertSame(['contacto' => 50, 'cierre' => 100, 'salidas' => 100, 'asq' => null], $datos['iro']['componentes']);
        $this->assertSame(78, $datos['iro']['valor']);

        $this->assertSame(100, $datos['transicion']['matriz']['ROJO']['pct']['VERDE']);
        $this->assertSame(1, $datos['embudo']['pasos'][3]['v']);  // registro sostenido 7 días
        $this->assertSame(1, $datos['embudo']['pasos'][7]['v']);  // atendidos por profesional
        $this->assertTrue($datos['evolucion']['semaforo']['con_datos']);
        $this->assertTrue($datos['evolucion']['who5']['con_datos']);
        $this->assertTrue($datos['horas']['con_datos']);
        // 8 registros diarios + 2 WHO-5 + 2 interacciones
        $this->assertSame(12, collect($datos['modulos']['datos'])->sum('v'));

        $this->actingAs($this->admin)->get(route('admin.analitica', ['institucion' => $inst->slug, 'periodo' => '30']))->assertOk()
            ->assertSee('Mayab')
            ->assertSee('Nocturna 100%')
            ->assertSee('De quienes estaban en rojo, el 100% bajó de nivel');
        $this->actingAs($this->admin)->get(route('admin.analitica', ['periodo' => '365']))->assertOk();
    }

    public function test_gini_and_streak_helpers(): void
    {
        $this->assertSame(0.0, AnaliticaService::gini([1, 1, 1]));
        $this->assertEqualsWithDelta(0.5, AnaliticaService::gini([0, 2]), 0.0001);
        $this->assertSame(3, AnaliticaService::rachaMaxima(['2026-09-01', '2026-09-02', '2026-09-03', '2026-09-05']));
    }

    public function test_visiting_tools_records_an_interaction(): void
    {
        $inst = Institucion::factory()->create();
        $ana = $this->persona($inst, 'Ana Uc');

        $this->actingAs($ana)->get(route('tools.respiracion'))->assertOk();
        auth()->logout();
        $this->get(route('tools.stop'))->assertOk(); // sin sesión no se registra

        $this->assertDatabaseCount('interacciones', 1);
        $this->assertDatabaseHas('interacciones', ['user_id' => $ana->id, 'institucion_id' => $inst->id, 'modulo' => 'herramienta']);
    }
}
