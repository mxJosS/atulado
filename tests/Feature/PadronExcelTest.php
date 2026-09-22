<?php

namespace Tests\Feature;

use App\Models\CargaPadron;
use App\Models\ContactoEmergencia;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use App\Services\EstructuraInstitucionalService;
use App\Services\PadronExcelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PadronExcelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Institucion $institucion;

    /** @var list<string> */
    private array $temporales = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $this->institucion = Institucion::factory()->create(['nombre_corto' => 'Constructora Maya']);

        $servicio = app(EstructuraInstitucionalService::class);
        $servicio->crearMacroAreas($this->institucion, ['Operaciones', 'Corporativo']);
        $operaciones = Departamento::where('nombre', 'Operaciones')->first();
        Departamento::create([
            'institucion_id' => $this->institucion->id,
            'parent_id' => $operaciones->id,
            'nombre' => 'Cuadrilla nocturna',
            'clave' => 'cuadrilla-nocturna',
            'activo' => true,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->temporales as $ruta) {
            @unlink($ruta);
        }

        parent::tearDown();
    }

    private function encabezados(): array
    {
        return array_map(
            fn ($col) => $col[0] . ($col[1] ? ' *' : ''),
            array_values(PadronExcelService::COLUMNAS)
        );
    }

    private function fila(array $cambios = []): array
    {
        $base = array_fill_keys(array_keys(PadronExcelService::COLUMNAS), '');

        return array_values(array_merge($base, [
            'nombre_completo' => 'Luis Manuel Chan Poot',
            'correo' => 'lchan@maya.mx',
            'numero_empleado' => '1188',
            'departamento' => 'Operaciones › Cuadrilla nocturna',
        ], $cambios));
    }

    private function excel(array $filas, ?array $encabezados = null): UploadedFile
    {
        $libro = new Spreadsheet();
        $hoja = $libro->getActiveSheet();
        $hoja->setTitle(PadronExcelService::HOJA_PADRON);
        $hoja->fromArray($encabezados ?? $this->encabezados(), null, 'A1');
        if ($filas !== []) {
            $hoja->fromArray($filas, null, 'A2', true);
        }

        $ruta = tempnam(sys_get_temp_dir(), 'padron') . '.xlsx';
        (new Xlsx($libro))->save($ruta);
        $this->temporales[] = $ruta;

        return new UploadedFile($ruta, 'padron.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function subir(UploadedFile $archivo)
    {
        return $this->actingAs($this->admin)
            ->post(route('admin.instituciones.padron.importar', $this->institucion), ['archivo' => $archivo]);
    }

    public function test_only_admins_can_use_the_padron(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->get(route('admin.instituciones.padron.plantilla', $this->institucion))->assertRedirect(route('dashboard'));
        $this->actingAs($usuario)
            ->post(route('admin.instituciones.padron.importar', $this->institucion), ['archivo' => $this->excel([$this->fila()])])
            ->assertRedirect(route('dashboard'));
        $this->assertSame(0, Membresia::count());
    }

    public function test_template_is_an_excel_with_the_defined_columns_and_the_institution_areas(): void
    {
        $respuesta = $this->actingAs($this->admin)->get(route('admin.instituciones.padron.plantilla', $this->institucion));

        $respuesta->assertOk();
        $respuesta->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $respuesta->headers->get('Content-Disposition'));

        $ruta = tempnam(sys_get_temp_dir(), 'plantilla');
        $this->temporales[] = $ruta;
        file_put_contents($ruta, $respuesta->streamedContent());
        $libro = IOFactory::load($ruta);

        $padron = $libro->getSheetByName(PadronExcelService::HOJA_PADRON);
        $this->assertSame(0, $libro->getIndex($padron), 'La hoja Padrón debe ser la primera');
        $this->assertSame($this->encabezados(), $padron->rangeToArray('A1:Q1')[0]);
        $this->assertLessThanOrEqual(2, $padron->getHighestDataRow(), 'Sin miles de celdas vacías de formato');
        $this->assertSame('', implode('', array_map('strval', $padron->rangeToArray('A2:Q2')[0])), 'La hoja de captura va vacía, sin filas de ejemplo');
        $this->assertSame('list', $padron->getCell('D2')->getDataValidation()->getType());

        $areas = array_filter(array_column($libro->getSheetByName('Listas')->toArray(), 0));
        $this->assertContains('Operaciones › Cuadrilla nocturna', $areas);
        $this->assertContains('Corporativo', $areas);

        $this->assertNotNull($libro->getSheetByName('Instrucciones'));
    }

    public function test_imports_every_field_of_a_row(): void
    {
        $this->subir($this->excel([$this->fila([
            'puesto' => 'Oficial albañil',
            'turno' => 'nocturno',
            'horario' => '22:00-06:00',
            'fecha_ingreso' => '2024-03-11',
            'sexo' => 'Hombre',
            'anio_nacimiento' => (string) (now()->year - 30),
            'escolaridad' => 'secundaria',
            'lugar_origen' => 'Tekax, Yucatán',
            'tipo_jornada' => 'Tiempo completo',
            'contacto_emergencia_nombre' => 'Rosa Poot Canché',
            'contacto_emergencia_telefono' => '9994128803',
            'contacto_emergencia_relacion' => 'Madre',
            'idioma' => 'Maya',
        ])]))->assertRedirect(route('admin.instituciones.show', $this->institucion) . '#tab-padron')->assertSessionHas('success');

        $usuario = User::where('email', 'lchan@maya.mx')->firstOrFail();
        $this->assertSame('Luis Manuel Chan Poot', $usuario->name);

        $membresia = Membresia::where('user_id', $usuario->id)->firstOrFail();
        $this->assertSame($this->institucion->id, $membresia->institucion_id);
        $this->assertSame(Departamento::where('nombre', 'Cuadrilla nocturna')->value('id'), $membresia->departamento_id);
        $this->assertSame('1188', $membresia->numero_empleado);
        $this->assertSame('nocturno', $membresia->turno);
        $this->assertSame('2024-03-11', $membresia->fecha_ingreso->toDateString());
        $this->assertSame('M', $membresia->sexo);
        $this->assertSame('25-34', $membresia->rango_edad);
        $this->assertSame('Secundaria', $membresia->escolaridad);
        $this->assertSame('myn', $membresia->idioma);
        $this->assertSame('invitado', $membresia->estado);

        $contacto = ContactoEmergencia::where('user_id', $usuario->id)->firstOrFail();
        $this->assertSame('Rosa Poot Canché', $contacto->nombre);
        $this->assertSame('9994128803', $contacto->telefono);

        $carga = CargaPadron::firstOrFail();
        $this->assertSame('completada', $carga->estado);
        $this->assertSame(1, $carga->filas_lista);
    }

    public function test_there_is_no_limit_on_the_number_of_people(): void
    {
        $filas = [];
        for ($i = 1; $i <= 600; $i++) {
            $filas[] = $this->fila([
                'nombre_completo' => "Persona {$i}",
                'correo' => "persona{$i}@maya.mx",
                'numero_empleado' => "EMP-{$i}",
                'departamento' => $i % 2 ? 'Corporativo' : 'Cuadrilla nocturna',
                'contacto_emergencia_nombre' => 'Familiar',
                'contacto_emergencia_telefono' => '9990000000',
            ]);
        }

        $this->subir($this->excel($filas))->assertSessionHasNoErrors();

        $this->assertSame(600, Membresia::where('institucion_id', $this->institucion->id)->count());
        $this->assertSame(600, CargaPadron::firstOrFail()->filas_lista);
    }

    public function test_rows_with_errors_are_skipped_and_the_rest_is_imported(): void
    {
        $otra = User::factory()->create(['email' => 'ocupado@maya.mx']);
        Membresia::factory()->create([
            'institucion_id' => $this->institucion->id,
            'user_id' => $otra->id,
            'numero_empleado' => '5000',
        ]);

        $this->subir($this->excel([
            $this->fila(['correo' => 'bien@maya.mx', 'numero_empleado' => '1']),
            $this->fila(['correo' => 'no-es-correo', 'numero_empleado' => '2']),
            $this->fila(['nombre_completo' => '', 'correo' => 'sinnombre@maya.mx', 'numero_empleado' => '3']),
            $this->fila(['correo' => 'area@maya.mx', 'numero_empleado' => '4', 'departamento' => 'Mantenimiento']),
            $this->fila(['correo' => 'bien@maya.mx', 'numero_empleado' => '6']),
            $this->fila(['correo' => 'nuevo@maya.mx', 'numero_empleado' => '5000']),
            $this->fila(['correo' => 'aviso@maya.mx', 'numero_empleado' => '7', 'turno' => 'Cuando puede', 'anio_nacimiento' => '3000']),
        ]))->assertSessionHas('info');

        $this->assertDatabaseHas('users', ['email' => 'bien@maya.mx']);
        $this->assertDatabaseHas('users', ['email' => 'aviso@maya.mx']);
        foreach (['sinnombre@maya.mx', 'area@maya.mx', 'nuevo@maya.mx'] as $correo) {
            $this->assertDatabaseMissing('users', ['email' => $correo]);
        }
        $this->assertSame(3, Membresia::where('institucion_id', $this->institucion->id)->count());

        $carga = CargaPadron::firstOrFail();
        $this->assertSame(5, $carga->filas_error);
        $this->assertSame(2, $carga->filas_advertencia); // «bien» va sin contacto de emergencia

        $aviso = Membresia::whereHas('user', fn ($q) => $q->where('email', 'aviso@maya.mx'))->firstOrFail();
        $this->assertNull($aviso->turno);
        $this->assertNull($aviso->rango_edad);

        $mensajes = $carga->filas()->where('estado', 'error')->get()->flatMap->mensajes->implode(' ');
        $this->assertStringContainsString('no es válido', $mensajes);
        $this->assertStringContainsString('Falta el nombre', $mensajes);
        $this->assertStringContainsString('«Mantenimiento» no existe', $mensajes);
        $this->assertStringContainsString('se repite en la fila 2', $mensajes);
        $this->assertStringContainsString('ya lo tiene otra persona', $mensajes);

        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $this->institucion))
            ->assertOk()
            ->assertSee('No se dio de alta')
            ->assertSee('Descargar filas con error');
    }

    public function test_uploading_again_updates_people_instead_of_duplicating_them(): void
    {
        $existente = User::factory()->create(['email' => 'lchan@maya.mx', 'name' => 'Luis (su nombre)']);
        ContactoEmergencia::create(['user_id' => $existente->id, 'nombre' => 'El suyo', 'telefono' => '111', 'es_principal' => true]);

        $this->subir($this->excel([$this->fila(['puesto' => 'Peón'])]));
        $this->subir($this->excel([$this->fila([
            'puesto' => 'Oficial albañil',
            'departamento' => 'Corporativo',
            'contacto_emergencia_nombre' => 'Otro',
            'contacto_emergencia_telefono' => '222',
        ])]));

        $this->assertSame(1, User::where('email', 'lchan@maya.mx')->count());
        $membresia = Membresia::sole();
        $this->assertSame('Oficial albañil', $membresia->puesto);
        $this->assertSame(Departamento::where('nombre', 'Corporativo')->value('id'), $membresia->departamento_id);
        $this->assertSame('Luis (su nombre)', $existente->fresh()->name, 'No se pisa el nombre de una cuenta existente');
        $this->assertSame(['El suyo'], ContactoEmergencia::where('user_id', $existente->id)->pluck('nombre')->all(), 'El contacto de la persona gana');
        $this->assertSame(1, CargaPadron::latest('id')->first()->filas_duplicado);
    }

    public function test_file_without_required_columns_is_rejected_and_nothing_is_saved(): void
    {
        $this->subir($this->excel([['Ana', 'ana@maya.mx']], ['Nombre', 'Correo electrónico']))
            ->assertSessionHasErrors('archivo');

        $this->assertSame(0, CargaPadron::count());
        $this->assertDatabaseMissing('users', ['email' => 'ana@maya.mx']);
    }

    public function test_non_excel_files_are_rejected(): void
    {
        $csv = UploadedFile::fake()->createWithContent('padron.csv', "nombre,correo\nAna,ana@maya.mx");

        $this->subir($csv)->assertSessionHasErrors('archivo');
        $this->assertSame(0, CargaPadron::count());
    }

    public function test_extra_columns_like_curp_are_ignored_and_not_stored(): void
    {
        $encabezados = [...$this->encabezados(), 'CURP'];
        $fila = [...$this->fila(), 'CAPL910101HYNHNS09'];

        $this->subir($this->excel([$fila], $encabezados))->assertSessionHasNoErrors();

        $carga = CargaPadron::firstOrFail();
        $this->assertSame(['CURP'], $carga->mapeo['columnas_ignoradas']);
        $this->assertStringNotContainsString('CAPL910101', json_encode($carga->filas()->first()->datos));
        $this->assertSame(1, Membresia::count());
    }

    public function test_error_rows_can_be_downloaded_to_fix_and_reupload(): void
    {
        $this->subir($this->excel([
            $this->fila(['correo' => 'bien@maya.mx', 'numero_empleado' => '1']),
            $this->fila(['correo' => 'mal@maya.mx', 'numero_empleado' => '2', 'departamento' => 'Mantenimiento']),
        ]));
        $carga = CargaPadron::firstOrFail();

        $respuesta = $this->actingAs($this->admin)
            ->get(route('admin.instituciones.padron.problemas', [$this->institucion, $carga]));
        $respuesta->assertOk();

        $ruta = tempnam(sys_get_temp_dir(), 'problemas');
        $this->temporales[] = $ruta;
        file_put_contents($ruta, $respuesta->streamedContent());
        $hoja = IOFactory::load($ruta)->getSheetByName(PadronExcelService::HOJA_PADRON);

        $this->assertSame(2, $hoja->getHighestDataRow());
        $this->assertSame('mal@maya.mx', $hoja->getCell('B2')->getValue());
        $this->assertStringContainsString('Mantenimiento', (string) $hoja->getCell('R2')->getValue());

        // Corregida, la misma hoja se vuelve a subir tal cual (la columna Problema se ignora).
        $hoja->setCellValue('D2', 'Corporativo');
        $corregido = tempnam(sys_get_temp_dir(), 'corregido') . '.xlsx';
        $this->temporales[] = $corregido;
        (new Xlsx($hoja->getParent()))->save($corregido);

        $this->subir(new UploadedFile($corregido, 'corregido.xlsx', null, null, true))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'mal@maya.mx']);
        $this->assertSame([], CargaPadron::latest('id')->first()->mapeo['columnas_ignoradas']);
    }

    public function test_error_download_of_another_institution_is_not_found(): void
    {
        $this->subir($this->excel([$this->fila()]));
        $otra = Institucion::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.instituciones.padron.problemas', [$otra, CargaPadron::firstOrFail()]))
            ->assertNotFound();
    }
}
