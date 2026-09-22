<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * El puente de datos del esquema viejo (institutions + departments_data JSON)
 * al nuevo (instituciones + departamentos + membresias).
 *
 * Lo importante que cubre: el JSON viejo tiene DOS formatos incompatibles
 * conviviendo, porque unos métodos escribían la clave 'name' y otro
 * 'macro_group'. La migración tiene que digerir ambos.
 */
class MigracionInstitucionesTest extends TestCase
{
    use RefreshDatabase;

    private function correrPuente(): void
    {
        $migracion = require database_path('migrations/2026_09_21_000010_migrate_institutions_to_instituciones.php');
        $migracion->up();
    }

    private function sembrarInstitucionVieja(array $overrides = []): int
    {
        return (int) DB::table('institutions')->insertGetId(array_merge([
            'slug' => 'empresa-demo',
            'name' => 'Empresa Demo S.A. de C.V.',
            'short_name' => 'Empresa Demo',
            'rfc' => 'EDE010203AB1',
            'category' => 'manufactura',
            'city' => 'Mérida',
            'contact_name' => 'Mónica Rivas',
            'contact_position' => 'Gerente de RH',
            'contact_email' => 'monica@demo.mx',
            'professional_name' => 'Psic. Roberto Ancona',
            'professional_license' => '7712045',
            'plan' => 'Institucional Anual',
            'users_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_migra_ambos_formatos_de_departments_data(): void
    {
        // Formato A: el que escribían storeArea / importCsv (clave 'name')
        $this->sembrarInstitucionVieja([
            'slug' => 'formato-name',
            'name' => 'Formato Name S.A.',
            'rfc' => 'FNA010203AB1',
            'contact_email' => 'a@demo.mx',
            'departments_data' => json_encode([[
                'name' => 'Operaciones y Frente de Obra',
                'departments' => [
                    ['name' => 'Cuadrilla Nocturna', 'shift' => 'Nocturno'],
                    ['name' => 'Soporte en Sitio', 'shift' => 'Matutino'],
                ],
            ]]),
        ]);

        // Formato B: el que escribía storeCollaborator (clave 'macro_group', sin 'name')
        $this->sembrarInstitucionVieja([
            'slug' => 'formato-macro',
            'name' => 'Formato Macro S.A.',
            'rfc' => 'FMA010203AB1',
            'contact_email' => 'b@demo.mx',
            'departments_data' => json_encode([[
                'macro_group' => 'Tecnología e Innovación',
                'departments' => [
                    ['name' => 'Desarrollo'],
                ],
            ]]),
        ]);

        $this->correrPuente();

        $this->assertSame(2, Institucion::count());

        $conName = Institucion::where('slug', 'formato-name')->firstOrFail();
        $conMacro = Institucion::where('slug', 'formato-macro')->firstOrFail();

        // Cada macro-grupo entra como padre y cada área como hija: 3 y 2.
        $this->assertSame(3, $conName->departamentos()->count());
        $this->assertSame(2, $conMacro->departamentos()->count());

        // El grupo del formato B conserva su nombre en vez de caer a 'General'
        $this->assertDatabaseHas('departamentos', [
            'institucion_id' => $conMacro->id,
            'nombre' => 'Tecnología e Innovación',
            'parent_id' => null,
        ]);

        // La jerarquía del JSON no se pierde
        $padre = Departamento::where('institucion_id', $conName->id)
            ->where('nombre', 'Operaciones y Frente de Obra')->firstOrFail();
        $this->assertSame(2, Departamento::where('parent_id', $padre->id)->count());
        $this->assertSame('nocturno', Departamento::where('nombre', 'Cuadrilla Nocturna')->value('turno_predominante'));
    }

    public function test_convierte_usuarios_vinculados_en_membresias_sin_tocar_al_usuario(): void
    {
        $institucionVieja = $this->sembrarInstitucionVieja([
            'departments_data' => json_encode([[
                'name' => 'Operaciones',
                'departments' => [['name' => 'Cuadrilla Nocturna']],
            ]]),
        ]);

        $usuario = User::factory()->create(['email' => 'colaborador@demo.mx']);
        DB::table('users')->where('id', $usuario->id)->update([
            'institution_id' => $institucionVieja,
            'macro_group' => 'Operaciones',
            'department' => 'Cuadrilla Nocturna',
            'shift' => 'Nocturno',
            'employee_number' => 'COL-0412',
            'position' => 'Oficial albañil',
        ]);

        $this->correrPuente();

        $membresia = Membresia::where('user_id', $usuario->id)->firstOrFail();

        $this->assertSame('COL-0412', $membresia->numero_empleado);
        $this->assertSame('Oficial albañil', $membresia->puesto);
        $this->assertSame('nocturno', $membresia->turno);
        $this->assertSame('activo', $membresia->estado);
        $this->assertSame('Cuadrilla Nocturna', $membresia->departamento->nombre);

        // El usuario conserva su cuenta y su id: la migración es aditiva
        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'email' => 'colaborador@demo.mx']);
        $this->assertSame(1, DB::table('institutions')->count(), 'La tabla vieja no se toca');
    }

    public function test_es_idempotente_porque_el_procfile_migra_en_cada_arranque(): void
    {
        $this->sembrarInstitucionVieja();

        $this->correrPuente();
        $this->correrPuente();
        $this->correrPuente();

        $this->assertSame(1, Institucion::count());
    }

    public function test_no_falla_con_rfc_invalido_o_repetido(): void
    {
        $this->sembrarInstitucionVieja(['slug' => 'uno', 'rfc' => 'ABC010203XYZ', 'contact_email' => 'uno@demo.mx']);
        $this->sembrarInstitucionVieja(['slug' => 'dos', 'rfc' => 'ABC010203XYZ', 'contact_email' => 'dos@demo.mx']);
        $this->sembrarInstitucionVieja(['slug' => 'tres', 'rfc' => 'RFC-DEMASIADO-LARGO-123', 'contact_email' => 'tres@demo.mx']);

        $this->correrPuente();

        $this->assertSame(3, Institucion::count());
        $this->assertSame('ABC010203XYZ', Institucion::where('slug', 'uno')->value('rfc'));
        $this->assertNull(Institucion::where('slug', 'dos')->value('rfc'), 'El RFC repetido se deja vacío');
        $this->assertNull(Institucion::where('slug', 'tres')->value('rfc'), 'El RFC fuera de formato se deja vacío');
    }

    public function test_institucion_sin_departamentos_recibe_area_sin_asignar(): void
    {
        $institucionVieja = $this->sembrarInstitucionVieja(['departments_data' => null]);

        $usuario = User::factory()->create();
        DB::table('users')->where('id', $usuario->id)->update(['institution_id' => $institucionVieja]);

        $this->correrPuente();

        $membresia = Membresia::where('user_id', $usuario->id)->firstOrFail();
        $this->assertSame('Sin asignar', $membresia->departamento->nombre);
    }
}
