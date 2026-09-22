<?php

namespace Tests\Feature;

use App\Models\AplicacionAsq;
use App\Models\AplicacionMdi;
use App\Models\AplicacionWho5;
use App\Models\Article;
use App\Models\Clasificacion;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\EstructuraInstitucionalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * Dashboard «Estado de la plataforma», lecturas de la revista, exportación
 * del padrón y asignación de profesionales clínicos a instituciones.
 */
class EstadoPlataformaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
    }

    private function persona(Institucion $institucion, string $nombre, string $estado = 'activo', array $membresia = []): User
    {
        $user = User::factory()->create(['name' => $nombre, 'email' => Str::slug($nombre) . '@prueba.mx']);
        Membresia::factory()->create(array_merge([
            'institucion_id' => $institucion->id,
            'user_id' => $user->id,
            'estado' => $estado,
            'activado_en' => $estado === 'activo' ? now()->subDays(60) : null,
        ], $membresia));

        return $user;
    }

    private function who5(User $user, Carbon $fecha, int $crudo): void
    {
        AplicacionWho5::create([
            'user_id' => $user->id, 'fecha' => $fecha, 'i1' => 0, 'i2' => 0, 'i3' => 0, 'i4' => 0, 'i5' => 0,
            'crudo' => $crudo, 'escala' => $crudo * 4, 'origen' => 'programada',
        ]);
    }

    private function registro(User $user, int $diasAtras, bool $lexica = false): void
    {
        MoodLog::create([
            'user_id' => $user->id, 'score' => 4, 'primary_emotion' => 'Calma',
            'logged_date' => today()->subDays($diasAtras), 'bandera_lexica' => $lexica,
        ]);
    }

    private function articulo(?User $autor = null): Article
    {
        return Article::create([
            'user_id' => $autor?->id,
            'title' => 'Dormir mejor',
            'slug' => 'dormir-mejor',
            'author_name' => 'Psic. Ana Pech',
            'summary' => 'Resumen',
            'content' => str_repeat('Contenido de prueba para la revista. ', 5),
            'published_at' => now()->subDay(),
        ]);
    }

    private function sesion(?User $user, int $segundosAtras): void
    {
        DB::table('sessions')->insert([
            'id' => Str::random(40), 'user_id' => $user?->id, 'ip_address' => null, 'user_agent' => null,
            'payload' => '', 'last_activity' => now()->subSeconds($segundosAtras)->getTimestamp(),
        ]);
    }

    /* ─────────── Dashboard ─────────── */

    public function test_dashboard_shows_platform_state_with_real_metrics(): void
    {
        $escuela = Institucion::factory()->create([
            'nombre_corto' => 'Colegio Mayab', 'razon_social' => 'Colegio Mayab A.C.', 'sector' => 'Educación y Colegios',
            'meta_adopcion' => 80, 'profesional_nda_hasta' => today()->addDays(10),
        ]);
        Institucion::factory()->onboarding()->create(['nombre_corto' => 'TRAsystems', 'profesional_nombre' => null]);

        $ana = $this->persona($escuela, 'Ana Uc');
        $beto = $this->persona($escuela, 'Beto Pech');
        $carla = $this->persona($escuela, 'Carla Chi');
        $this->persona($escuela, 'Dora May', 'invitado');

        Clasificacion::create(['user_id' => $ana->id, 'fecha' => today(), 'nivel' => 'VERDE', 'origen' => 'who5']);
        Clasificacion::create(['user_id' => $beto->id, 'fecha' => today(), 'nivel' => 'ROJO_AGUDO', 'origen' => 'asq']);
        Clasificacion::create(['user_id' => $carla->id, 'fecha' => today()->subDays(40), 'nivel' => 'AMARILLO', 'origen' => 'mdi']);
        EventoCrisis::create(['user_id' => $beto->id, 'institucion_id' => $escuela->id, 'nivel' => 'ROJO_AGUDO', 'origen' => 'asq', 'disparado_en' => now()->subMinutes(30), 'estado' => 'abierto']);

        $this->who5($ana, today(), 15);
        $this->who5($beto, today()->subDays(5), 10);
        $this->who5($carla, today()->subDays(20), 20);
        AplicacionMdi::create(['user_id' => $beto->id, 'fecha' => today(), 'i1' => 1, 'i2' => 1, 'i3' => 1, 'i4' => 1, 'i5' => 1, 'i6' => 1, 'i7' => 1, 'i8a' => 1, 'i8b' => 1, 'i9' => 1, 'i10a' => 1, 'i10b' => 1, 'total' => 10, 'nivel' => 'AMARILLO']);
        AplicacionAsq::create(['user_id' => $beto->id, 'fecha' => today(), 'p1' => 'si', 'p2' => 'no', 'p3' => 'no', 'p4' => 'no', 'p5' => 'si', 'resultado' => 'POSITIVA_AGUDA', 'nivel' => 'ROJO_AGUDO']);

        // Psicólogos: uno publica (y ya publicó), uno clínico asignado, uno ambos.
        $publica = User::factory()->create(['role' => 'profesional']);
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        User::factory()->create(['role' => 'profesional', 'is_clinico_atulado' => true]);
        $clinico->institucionesAsignadas()->attach($escuela);

        $articulo = $this->articulo($publica);
        DB::table('lecturas_articulos')->insert([
            ['article_id' => $articulo->id, 'visitante' => 'u:1', 'fecha' => today()->toDateString(), 'created_at' => now()],
            ['article_id' => $articulo->id, 'visitante' => 's:abc', 'fecha' => today()->toDateString(), 'created_at' => now()],
        ]);

        $this->sesion($ana, 60);
        $this->sesion($this->admin, 30);
        $this->sesion($carla, 600);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $respuesta->assertOk()
            ->assertSee('Estado de la plataforma')
            ->assertSee('Modo escolar')
            ->assertSee('Estudiantes')
            ->assertSee('NDA vence en 10 días')
            ->assertSee('1 caso ROJO AGUDO sin cerrar')
            ->assertSee('Contrato de confidencialidad por vencer')
            ->assertSee('Falta designar profesional')
            ->assertSee('Dar de alta usuario')
            ->assertSee('Auditar cédulas')
            ->assertDontSee('Torre de Control');

        $respuesta->assertViewHas('kpis', function ($k) {
            return $k['instituciones']['activas'] === 1
                && $k['instituciones']['onboarding'] === 1
                && $k['cuentas']['activas'] === 3 && $k['cuentas']['padron'] === 4
                && $k['cuentas']['adopcion'] == 75.0 && $k['cuentas']['meta'] === 80
                && $k['casos']['total'] === 1 && $k['casos']['agudos'] === 1 && $k['casos']['instituciones'] === 1
                && $k['instrumentos']['actual'] === ['WHO-5' => 2, 'MDI' => 1, 'ASQ' => 1, 'Puchol' => 0]
                && $k['lecturas']['periodo'] === 2 && $k['lecturas']['total'] === 2
                && $k['en_linea'] === 1
                && $k['psicologos'] === ['publican' => 2, 'ya_publicaron' => 1, 'clinicos' => 2, 'con_institucion' => 1];
        });

        // Semáforo: sólo las clasificaciones de los últimos 30 días.
        $respuesta->assertViewHas('semaforo', fn ($s) => $s['total'] === 2
            && $s['conteos']['VERDE'] === 1 && $s['conteos']['ROJO_AGUDO'] === 1 && $s['conteos']['AMARILLO'] === 0
            && count($s['evolucion']['labels']) === 12);

        $respuesta->assertViewHas('instituciones', function ($filas) {
            $escuela = collect($filas)->firstWhere('nombre', 'Colegio Mayab');

            return $escuela['padron'] === 4 && $escuela['activas'] === 3 && $escuela['adopcion'] === 75
                && $escuela['badge']['texto'] === '1 agudo'
                && $escuela['etiqueta_personas'] === 'Estudiantes'
                && $escuela['who5'] === 60
                && $escuela['casos'] === 1;
        });
    }

    public function test_period_filter_only_changes_period_metrics(): void
    {
        $inst = Institucion::factory()->create();
        $eva = $this->persona($inst, 'Eva Can');
        $this->who5($eva, today()->subDays(20), 15);
        $this->registro($eva, 10, lexica: true);
        Clasificacion::create(['user_id' => $eva->id, 'fecha' => today()->subDays(10), 'nivel' => 'NARANJA', 'origen' => 'mdi']);

        $ver = fn (string $periodo) => $this->actingAs($this->admin)->get(route('admin.dashboard', ['periodo' => $periodo]))->assertOk();

        $ver('7')->assertViewHas('kpis', fn ($k) => $k['instrumentos']['actual']['WHO-5'] === 0)
            ->assertViewHas('semaforo', fn ($s) => $s['conteos']['NARANJA'] === 1)
            ->assertDontSee('Filtro léxico');
        $ver('30')->assertViewHas('kpis', fn ($k) => $k['instrumentos']['actual']['WHO-5'] === 1)
            ->assertViewHas('semaforo', fn ($s) => $s['conteos']['NARANJA'] === 1)
            ->assertSee('Filtro léxico: 1 detección');
        // Ciclo = 14 días: el WHO-5 de hace 20 días cae en el periodo anterior.
        $ver('ciclo')->assertViewHas('kpis', fn ($k) => $k['instrumentos']['actual']['WHO-5'] === 0 && $k['instrumentos']['anterior']['WHO-5'] === 1)
            ->assertViewHas('periodo', fn ($p) => $p['dias'] === 14);
        $ver('cualquiera')->assertViewHas('periodo', fn ($p) => $p['clave'] === '7');
    }

    public function test_usage_drop_and_silence_rules(): void
    {
        $inst = Institucion::factory()->create(['nombre_corto' => 'Grupo Peninsular']);
        Institucion::factory()->create(['nombre_corto' => 'Tranquila SA']);
        [$u1, $u2, $u3, $u4, $u5] = collect(range(1, 5))->map(fn ($n) => $this->persona($inst, "Persona {$n}"))->all();

        // Personas activas por semana, de la más antigua a la actual: 5, 4, 3, 2.
        foreach ([$u1, $u2] as $u) {
            foreach ([0, 7, 14, 21] as $d) {
                $this->registro($u, $d);
            }
        }
        foreach ([7, 14, 21] as $d) {
            $this->registro($u3, $d);
        }
        foreach ([14, 21] as $d) {
            $this->registro($u4, $d);
        }
        $this->registro($u5, 21);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Caída de uso sostenida')
            ->assertSee('Regla R4 — silencio prolongado')
            ->assertSee('−60% en 3 semanas')
            ->assertSee('2 en silencio R4');

        $respuesta->assertViewHas('instituciones', function ($filas) {
            $grupo = collect($filas)->firstWhere('nombre', 'Grupo Peninsular');
            $tranquila = collect($filas)->firstWhere('nombre', 'Tranquila SA');

            return $grupo['caida'] === -60
                && $grupo['silencio'] === 2
                && $grupo['badge']['texto'] === 'Uso a la baja'
                && $grupo['arraigo'] === 0.09   // 14 días-persona ÷ 30 ÷ 5 personas
                && $grupo['adherencia'] === 9   // 14 de 150 días posibles
                && $tranquila['badge']['texto'] === 'Estable';
        });
    }

    public function test_only_admins_see_the_dashboard(): void
    {
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);

        $this->actingAs($clinico)->get(route('admin.dashboard'))->assertRedirect(route('dashboard'));
        $this->actingAs($clinico)->get(route('admin.dashboard.exportar-padron'))->assertRedirect(route('dashboard'));
    }

    /* ─────────── Exportar padrón ─────────── */

    public function test_padron_export_has_no_clinical_data_and_only_the_chosen_institutions(): void
    {
        $mayab = Institucion::factory()->create(['nombre_corto' => 'Constructora Mayab']);
        $hotel = Institucion::factory()->create(['nombre_corto' => 'Hotel Xcanatún']);
        app(EstructuraInstitucionalService::class)->crearMacroAreas($mayab, ['Obra']);

        $rosa = $this->persona($mayab, 'Rosa Poot', 'activo', ['departamento_id' => $mayab->departamentos()->value('id')]);
        $this->persona($mayab, 'Luis Chan', 'invitado');
        $this->persona($hotel, 'Pedro Chim');
        Clasificacion::create(['user_id' => $rosa->id, 'fecha' => today(), 'nivel' => 'ROJO', 'origen' => 'mdi']);

        $respuesta = $this->actingAs($this->admin)
            ->get(route('admin.dashboard.exportar-padron', ['instituciones' => (string) $mayab->id]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $ruta = tempnam(sys_get_temp_dir(), 'padron') . '.xlsx';
        file_put_contents($ruta, $respuesta->streamedContent());
        $filas = IOFactory::load($ruta)->getActiveSheet()->toArray();
        @unlink($ruta);

        $this->assertSame(['Institución', 'Nombre', 'Correo', 'Área', 'Estado de la cuenta'], $filas[0]);
        $this->assertSame(['Constructora Mayab', 'Luis Chan', 'luis-chan@prueba.mx', null, 'Sin activar'], $filas[1]);
        $this->assertSame(['Constructora Mayab', 'Rosa Poot', 'rosa-poot@prueba.mx', 'Obra', 'Cuenta activa'], $filas[2]);
        $this->assertCount(3, $filas, 'La otra institución no se exporta');
        $this->assertStringNotContainsString('ROJO', json_encode($filas));
    }

    /* ─────────── Revista ─────────── */

    public function test_article_reads_count_once_per_visitor_per_article_per_day(): void
    {
        $articulo = $this->articulo();
        $url = route('revista.show', $articulo->slug);
        $sesion = Str::random(40);

        // Visitante sin cuenta: mismo navegador, dos visitas el mismo día.
        $this->withCookie(config('session.cookie'), $sesion)->get($url)->assertOk();
        $this->withCookie(config('session.cookie'), $sesion)->get($url)->assertOk();
        $this->assertDatabaseCount('lecturas_articulos', 1);

        // Otra persona con cuenta, dos veces.
        $lectora = User::factory()->create();
        $this->actingAs($lectora)->get($url)->assertOk();
        $this->actingAs($lectora)->get($url)->assertOk();
        $this->assertDatabaseCount('lecturas_articulos', 2);
        $this->assertDatabaseHas('lecturas_articulos', ['article_id' => $articulo->id, 'visitante' => 'u:' . $lectora->id]);

        // Al día siguiente vuelve a contar.
        Carbon::setTestNow(now()->addDay());
        $this->actingAs($lectora)->get($url)->assertOk();
        $this->assertDatabaseCount('lecturas_articulos', 3);
        Carbon::setTestNow();

        // Los robots no cuentan.
        auth()->logout();
        $this->withHeader('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)')->get($url)->assertOk();
        $this->assertDatabaseCount('lecturas_articulos', 3);
    }

    /* ─────────── Profesionales clínicos asignados ─────────── */

    public function test_assigned_clinician_only_sees_their_institutions_and_cases(): void
    {
        $asignada = Institucion::factory()->create(['nombre_corto' => 'Asignada SA']);
        $ajena = Institucion::factory()->create(['nombre_corto' => 'Ajena SA']);
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $clinico->institucionesAsignadas()->attach($asignada);

        $deAsignada = $this->persona($asignada, 'Caso Asignado');
        $deAjena = $this->persona($ajena, 'Caso Ajeno');
        $sinEmpresa = User::factory()->create(['name' => 'Caso Sin Empresa']);
        $casoAjeno = null;
        foreach ([[$deAsignada, $asignada->id], [$deAjena, $ajena->id], [$sinEmpresa, null]] as [$persona, $institucionId]) {
            $caso = EventoCrisis::create(['user_id' => $persona->id, 'institucion_id' => $institucionId, 'nivel' => 'ROJO', 'origen' => 'mdi', 'disparado_en' => now(), 'estado' => 'abierto']);
            $casoAjeno = $institucionId === $ajena->id ? $caso : $casoAjeno;
        }

        $this->actingAs($clinico)->get(route('admin.instituciones.index'))->assertOk()->assertSee('Asignada SA')->assertDontSee('Ajena SA');
        $this->actingAs($clinico)->get(route('admin.instituciones.show', $asignada))->assertOk()->assertSee('Sólo lectura');
        $this->actingAs($clinico)->get(route('admin.instituciones.show', $ajena))->assertNotFound();
        $this->actingAs($clinico)->postJson(route('admin.instituciones.ficha', [$ajena, Membresia::where('user_id', $deAjena->id)->sole()]), ['motivo' => 'Seguimiento clínico programado'])
            ->assertNotFound();

        $this->actingAs($clinico)->get(route('admin.cola.index'))->assertOk()
            ->assertSee('Caso Asignado')->assertDontSee('Caso Ajeno')->assertDontSee('Caso Sin Empresa');
        $this->actingAs($clinico)->post(route('admin.cola.contacto', $casoAjeno), ['nota' => 'Llamada.'])->assertNotFound();

        // Sin asignaciones no ve ninguna institución.
        $sinAsignar = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $this->actingAs($sinAsignar)->get(route('admin.instituciones.index'))->assertOk()->assertSee('Aún no tienes instituciones asignadas');
        $this->actingAs($sinAsignar)->get(route('admin.instituciones.show', $asignada))->assertNotFound();

        // La administración con acreditación clínica ve todo, incluso lo que no tiene institución.
        $adminClinico = User::factory()->create(['is_admin' => true, 'role' => 'admin', 'is_clinico_atulado' => true]);
        $this->actingAs($adminClinico)->get(route('admin.cola.index'))->assertOk()
            ->assertSee('Caso Asignado')->assertSee('Caso Ajeno')->assertSee('Caso Sin Empresa');
    }

    public function test_users_form_assigns_clinicians_without_adding_them_to_the_padron(): void
    {
        $a = Institucion::factory()->create();
        $b = Institucion::factory()->create();

        $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'first_name' => 'Rodrigo', 'last_name' => 'Ancona', 'email' => 'rodrigo@atulado.mx',
            'password' => 'secreta1', 'password_confirmation' => 'secreta1',
            'role' => 'profesional', 'perfil_profesional' => 'clinico',
            'education_level' => 'maestria', 'license_number' => '1234567',
            'instituciones_asignadas' => [$a->id, $b->id], 'institucion_id' => $a->id,
        ])->assertSessionHasNoErrors();

        $rodrigo = User::where('email', 'rodrigo@atulado.mx')->sole();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $rodrigo->institucionesAsignadas()->pluck('instituciones.id')->all());
        $this->assertSame(0, Membresia::where('user_id', $rodrigo->id)->count(), 'Un clínico no entra al padrón');
        $this->assertTrue($a->profesionalesAsignados()->where('users.id', $rodrigo->id)->exists());
        $this->assertNotSame('Rodrigo Ancona', $a->fresh()->profesional_nombre, 'Asignar no lo vuelve profesional designado');

        // Si deja de ser clínico, pierde las asignaciones y vuelve el vínculo normal al padrón.
        $this->actingAs($this->admin)->put(route('admin.users.update', $rodrigo), [
            'name' => 'Rodrigo Ancona', 'email' => 'rodrigo@atulado.mx', 'role' => 'profesional', 'perfil_profesional' => 'publica',
            'status' => 'activo', 'license_number' => '1234567', 'institucion_id' => $a->id, 'instituciones_asignadas' => [$b->id],
        ])->assertSessionHasNoErrors();

        $this->assertSame(0, $rodrigo->institucionesAsignadas()->count());
        $this->assertSame($a->id, $rodrigo->membresiaActiva()->value('institucion_id'));
    }

    /* ─────────── Instituciones ─────────── */

    public function test_institutions_page_no_longer_shows_the_kpi_cards(): void
    {
        Institucion::factory()->create(['nombre_corto' => 'Constructora Mayab']);

        $this->actingAs($this->admin)->get(route('admin.instituciones.index'))->assertOk()
            ->assertSee('Constructora Mayab')
            ->assertDontSee('Personas en padrón')
            ->assertDontSee('Casos de crisis abiertos');

        $this->actingAs($this->admin)->get(route('admin.instituciones.index', ['alta' => 1]))->assertOk();
    }
}
