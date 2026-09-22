<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitucionAltaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
    }

    private function datosAlta(array $cambios = []): array
    {
        return array_merge([
            'razon_social' => 'Constructora Maya S.A. de C.V.',
            'nombre_corto' => 'Constructora Maya',
            'sector' => 'Construcción y Obras',
            'rfc' => 'cma210415h21',
            'ciudad' => 'Mérida, Yucatán',
            'contacto_nombre' => 'Lic. Roberto Pech',
            'contacto_puesto' => 'Gerente de Recursos Humanos',
            'contacto_email' => ' RPech@Empresa.com ',
            'contacto_telefono' => '999 412 8830',
            'profesional_nombre' => 'Psic. Rodrigo Ancona Rosado',
            'profesional_cedula' => '9412034',
            'plan' => 'Institucional Anual',
            'padron_estimado' => 120,
            'vigencia_fin' => '2027-09-21',
            'macro_areas' => 'Operaciones y Frente de Obra, Corporativo y Dirección, , Servicios Generales, operaciones y frente de obra',
        ], $cambios);
    }

    /** Lo que envía el modal de edición: los datos actuales, con cambios encima. */
    private function datosEdicion(Institucion $institucion, array $cambios = []): array
    {
        return array_merge([
            'razon_social' => $institucion->razon_social,
            'nombre_corto' => $institucion->nombre_corto,
            'sector' => $institucion->sector,
            'rfc' => $institucion->rfc,
            'ciudad' => $institucion->ciudad,
            'contacto_nombre' => $institucion->contacto_nombre,
            'contacto_puesto' => $institucion->contacto_puesto,
            'contacto_email' => $institucion->contacto_email,
            'contacto_telefono' => $institucion->contacto_telefono,
            'profesional_nombre' => $institucion->profesional_nombre,
            'profesional_cedula' => $institucion->profesional_cedula,
            'plan' => $institucion->plan,
            'padron_estimado' => $institucion->padron_estimado,
            'vigencia_fin' => $institucion->vigencia_fin?->format('Y-m-d'),
        ], $cambios);
    }

    private function crearInstitucion(): Institucion
    {
        $this->actingAs($this->admin)->post(route('admin.instituciones.store'), $this->datosAlta());

        return Institucion::firstOrFail();
    }

    public function test_guest_and_regular_user_cannot_access(): void
    {
        $institucion = Institucion::factory()->create();

        $this->get('/admin/instituciones')->assertRedirect('/login');
        $this->get("/admin/instituciones/{$institucion->slug}")->assertRedirect('/login');

        $usuario = User::factory()->create(['is_admin' => false]);
        $this->actingAs($usuario)->get('/admin/instituciones')->assertRedirect(route('dashboard'));
        $this->actingAs($usuario)->get("/admin/instituciones/{$institucion->slug}")->assertRedirect(route('dashboard'));
        $this->actingAs($usuario)->post('/admin/instituciones', $this->datosAlta())->assertRedirect(route('dashboard'));
        $this->actingAs($usuario)->put("/admin/instituciones/{$institucion->slug}", $this->datosEdicion($institucion))
            ->assertRedirect(route('dashboard'));

        $this->assertSame(1, Institucion::count());
    }

    public function test_index_shows_empty_state_then_the_list(): void
    {
        $this->actingAs($this->admin)->get('/admin/instituciones')
            ->assertOk()
            ->assertSee('No hay instituciones registradas aún');

        $institucion = Institucion::factory()->create(['nombre_corto' => 'Hotel Caribe']);

        $this->actingAs($this->admin)->get('/admin/instituciones')
            ->assertOk()
            ->assertSee('Hotel Caribe')
            ->assertSee(route('admin.instituciones.show', $institucion), false);
    }

    public function test_admin_creates_institution_with_every_field_and_its_macro_areas(): void
    {
        $respuesta = $this->actingAs($this->admin)->post(route('admin.instituciones.store'), $this->datosAlta());

        $institucion = Institucion::firstOrFail();
        $respuesta->assertRedirect(route('admin.instituciones.show', $institucion));

        $this->assertSame('constructora-maya', $institucion->slug);
        $this->assertSame('Constructora Maya S.A. de C.V.', $institucion->razon_social);
        $this->assertSame('CMA210415H21', $institucion->rfc, 'El RFC se guarda en mayúsculas');
        $this->assertSame('rpech@empresa.com', $institucion->contacto_email, 'El correo se normaliza');
        $this->assertSame('Psic. Rodrigo Ancona Rosado', $institucion->profesional_nombre);
        $this->assertSame('9412034', $institucion->profesional_cedula);
        $this->assertSame('Institucional Anual', $institucion->plan);
        $this->assertSame(120, $institucion->padron_estimado);
        $this->assertSame('2027-09-21', $institucion->vigencia_fin->format('Y-m-d'));
        $this->assertSame('onboarding', $institucion->estado);
        $this->assertSame('CM', $institucion->iniciales);

        // Vacíos y repetidos de la lista se descartan: quedan 3, no 5
        $this->assertSame(
            ['Corporativo y Dirección', 'Operaciones y Frente de Obra', 'Servicios Generales'],
            $institucion->departamentos()->pluck('nombre')->all()
        );
        $this->assertTrue($institucion->departamentos()->whereNotNull('parent_id')->doesntExist());
    }

    public function test_alta_rejects_missing_or_invalid_data(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.instituciones.index'))
            ->post(route('admin.instituciones.store'), $this->datosAlta([
                'razon_social' => '',
                'contacto_email' => 'no-es-correo',
                'profesional_nombre' => '',
                'rfc' => 'RFC-INVENTADO',
                'macro_areas' => ' , , ',
            ]))
            ->assertRedirect(route('admin.instituciones.index'))
            ->assertSessionHasErrors(['razon_social', 'contacto_email', 'profesional_nombre', 'rfc', 'macro_areas']);

        $this->assertSame(0, Institucion::count());
    }

    public function test_rfc_must_be_unique(): void
    {
        Institucion::factory()->create(['rfc' => 'CMA210415H21']);

        $this->actingAs($this->admin)
            ->post(route('admin.instituciones.store'), $this->datosAlta())
            ->assertSessionHasErrors('rfc');
    }

    public function test_show_page_has_the_edit_button_and_the_prefilled_form(): void
    {
        $institucion = $this->crearInstitucion();

        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $institucion))
            ->assertOk()
            ->assertSee('Editar empresa')
            ->assertSee('value="Constructora Maya S.A. de C.V."', false)
            ->assertSee('value="rpech@empresa.com"', false)
            ->assertSee('value="Psic. Rodrigo Ancona Rosado"', false)
            ->assertSee('Operaciones y Frente de Obra');
    }

    public function test_admin_can_edit_every_field(): void
    {
        $institucion = $this->crearInstitucion();

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, [
                'razon_social' => 'Constructora Peninsular S.A.P.I.',
                'nombre_corto' => 'Grupo Peninsular',
                'sector' => 'Manufactura e Industria',
                'rfc' => 'GPE990101AB1',
                'ciudad' => 'Cancún, Quintana Roo',
                'contacto_nombre' => 'Mónica Rivas',
                'contacto_puesto' => 'Directora de Talento',
                'contacto_email' => 'monica@peninsular.mx',
                'contacto_telefono' => '998 100 2030',
                'profesional_nombre' => 'Psic. Daniela Mena',
                'profesional_cedula' => '8004112',
                'plan' => 'Piloto 90 días',
                'padron_estimado' => 300,
                'vigencia_fin' => '2028-01-15',
            ]))
            ->assertRedirect(route('admin.instituciones.show', $institucion))
            ->assertSessionHasNoErrors();

        $institucion->refresh();
        $this->assertSame('Constructora Peninsular S.A.P.I.', $institucion->razon_social);
        $this->assertSame('Grupo Peninsular', $institucion->nombre_corto);
        $this->assertSame('GP', $institucion->iniciales, 'Las iniciales siguen al nombre corto');
        $this->assertSame('Manufactura e Industria', $institucion->sector);
        $this->assertSame('GPE990101AB1', $institucion->rfc);
        $this->assertSame('Cancún, Quintana Roo', $institucion->ciudad);
        $this->assertSame('Mónica Rivas', $institucion->contacto_nombre);
        $this->assertSame('Directora de Talento', $institucion->contacto_puesto);
        $this->assertSame('monica@peninsular.mx', $institucion->contacto_email);
        $this->assertSame('998 100 2030', $institucion->contacto_telefono);
        $this->assertSame('Psic. Daniela Mena', $institucion->profesional_nombre);
        $this->assertSame('8004112', $institucion->profesional_cedula);
        $this->assertSame('Piloto 90 días', $institucion->plan);
        $this->assertSame(300, $institucion->padron_estimado);
        $this->assertSame('2028-01-15', $institucion->vigencia_fin->format('Y-m-d'));
        $this->assertSame('constructora-maya', $institucion->slug, 'El slug no cambia: las URLs siguen funcionando');
    }

    public function test_optional_fields_can_be_cleared(): void
    {
        $institucion = $this->crearInstitucion();

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, [
                'rfc' => '',
                'contacto_telefono' => '',
                'profesional_cedula' => '',
                'vigencia_fin' => '',
            ]))
            ->assertSessionHasNoErrors();

        $institucion->refresh();
        $this->assertNull($institucion->rfc);
        $this->assertNull($institucion->contacto_telefono);
        $this->assertNull($institucion->profesional_cedula);
        $this->assertNull($institucion->vigencia_fin);
    }

    public function test_rfc_uniqueness_ignores_the_institution_being_edited(): void
    {
        $institucion = $this->crearInstitucion();
        Institucion::factory()->create(['rfc' => 'OTR010101AA1']);

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, ['rfc' => 'OTR010101AA1']))
            ->assertSessionHasErrors('rfc');
    }

    public function test_edit_renames_adds_and_removes_areas(): void
    {
        $institucion = $this->crearInstitucion();
        $operaciones = Departamento::where('nombre', 'Operaciones y Frente de Obra')->firstOrFail();
        $servicios = Departamento::where('nombre', 'Servicios Generales')->firstOrFail();
        $corporativo = Departamento::where('nombre', 'Corporativo y Dirección')->firstOrFail();

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, [
                'areas' => [
                    $operaciones->id => ['nombre' => 'Operaciones en Obra', 'nuevas' => 'Cuadrilla Nocturna, Cuadrilla Matutina'],
                    $servicios->id => ['nombre' => 'Servicios Generales', 'quitar' => '1'],
                    $corporativo->id => ['nombre' => 'Corporativo y Dirección'],
                ],
                'nuevas_macro' => 'Logística',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame('Operaciones en Obra', $operaciones->fresh()->nombre);
        $this->assertSame($operaciones->clave, $operaciones->fresh()->clave, 'La clave interna no cambia al renombrar');
        $this->assertNull($servicios->fresh(), 'El área sin personas se quita');
        $this->assertSame(
            ['Cuadrilla Matutina', 'Cuadrilla Nocturna'],
            Departamento::where('parent_id', $operaciones->id)->orderBy('nombre')->pluck('nombre')->all()
        );
        $this->assertDatabaseHas('departamentos', [
            'institucion_id' => $institucion->id,
            'nombre' => 'Logística',
            'parent_id' => null,
        ]);
    }

    public function test_an_area_with_people_is_not_removed(): void
    {
        $institucion = $this->crearInstitucion();
        $operaciones = Departamento::where('nombre', 'Operaciones y Frente de Obra')->firstOrFail();
        Membresia::factory()->en($institucion, $operaciones)->create();

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, [
                'areas' => [$operaciones->id => ['nombre' => $operaciones->nombre, 'quitar' => '1']],
            ]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('info', fn ($aviso) => str_contains($aviso, 'Operaciones y Frente de Obra'));

        $this->assertNotNull($operaciones->fresh());
    }

    public function test_a_macro_area_is_protected_by_the_people_in_its_sub_areas(): void
    {
        $institucion = $this->crearInstitucion();
        $operaciones = Departamento::where('nombre', 'Operaciones y Frente de Obra')->firstOrFail();
        $cuadrilla = Departamento::create([
            'institucion_id' => $institucion->id,
            'parent_id' => $operaciones->id,
            'nombre' => 'Cuadrilla Nocturna',
            'clave' => 'cuadrilla-nocturna',
        ]);
        Membresia::factory()->en($institucion, $cuadrilla)->create();

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, [
                'areas' => [$operaciones->id => ['nombre' => $operaciones->nombre, 'quitar' => '1']],
            ]));

        $this->assertNotNull($operaciones->fresh());
        $this->assertNotNull($cuadrilla->fresh());
    }

    public function test_duplicate_area_names_are_rejected_and_nothing_is_saved(): void
    {
        $institucion = $this->crearInstitucion();
        $operaciones = Departamento::where('nombre', 'Operaciones y Frente de Obra')->firstOrFail();

        $this->actingAs($this->admin)
            ->from(route('admin.instituciones.show', $institucion))
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, [
                'razon_social' => 'Nombre que no debe guardarse',
                'areas' => [$operaciones->id => ['nombre' => 'Servicios Generales']],
            ]))
            ->assertRedirect(route('admin.instituciones.show', $institucion))
            ->assertSessionHasErrors('areas');

        $this->assertSame('Constructora Maya S.A. de C.V.', $institucion->fresh()->razon_social);
        $this->assertSame('Operaciones y Frente de Obra', $operaciones->fresh()->nombre);
    }

    public function test_an_institution_keeps_at_least_one_area(): void
    {
        $institucion = $this->crearInstitucion();
        $areas = $institucion->departamentos()->get()
            ->mapWithKeys(fn ($d) => [$d->id => ['nombre' => $d->nombre, 'quitar' => '1']])
            ->all();

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, ['areas' => $areas]))
            ->assertSessionHasErrors('areas');

        $this->assertSame(3, $institucion->departamentos()->count());
    }

    public function test_adoption_goal_is_editable_and_validated(): void
    {
        $institucion = $this->crearInstitucion();
        $this->assertSame(75, $institucion->fresh()->meta_adopcion, 'La meta contractual por defecto es 75%');

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, ['meta_adopcion' => 90]))
            ->assertSessionHasNoErrors();
        $this->assertSame(90, $institucion->fresh()->meta_adopcion);

        $this->actingAs($this->admin)
            ->put(route('admin.instituciones.update', $institucion), $this->datosEdicion($institucion, ['meta_adopcion' => 150]))
            ->assertSessionHasErrors('meta_adopcion');
        $this->assertSame(90, $institucion->fresh()->meta_adopcion);
    }
}
