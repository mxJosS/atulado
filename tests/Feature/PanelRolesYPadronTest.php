<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use App\Services\EstructuraInstitucionalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelRolesYPadronTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Institucion $institucion;

    private int $areaId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin', 'email' => 'jefa@atulado.com.mx']);
        $this->institucion = Institucion::factory()->create();
        app(EstructuraInstitucionalService::class)->crearMacroAreas($this->institucion, ['Operaciones']);
        $this->areaId = $this->institucion->departamentos()->value('id');
    }

    /* ─────────── Roles profesionales ─────────── */

    public function test_the_three_professional_profiles_map_to_publishing_and_clinical_access(): void
    {
        $casos = [
            'publica' => ['profesional', false, true],
            'clinico' => ['clinico', true, false],
            'ambos' => ['profesional', true, true],
        ];

        foreach ($casos as $perfil => [$role, $clinico, $publica]) {
            $email = "pro-{$perfil}@atulado.com.mx";
            $this->actingAs($this->admin)->post('/admin/usuarios', [
                'first_name' => 'Pro', 'last_name' => ucfirst($perfil), 'email' => $email,
                'password' => 'secreto123', 'password_confirmation' => 'secreto123',
                'role' => 'profesional', 'perfil_profesional' => $perfil,
                'education_level' => 'maestria', 'license_number' => 'CED-1',
            ])->assertRedirect(route('admin.users.index'));

            $u = User::where('email', $email)->sole();
            $this->assertSame($role, $u->role, $perfil);
            $this->assertSame($clinico, $u->isClinicoAcreditado(), $perfil);
            $this->assertSame($publica, $u->isProfessional(), $perfil);
            $this->assertSame($perfil, $u->perfil_profesional);
        }
    }

    public function test_publishing_professional_is_no_longer_clinical(): void
    {
        $pro = User::factory()->create(['role' => 'profesional']);

        $this->assertFalse($pro->isClinicoAcreditado());
        $this->actingAs($pro)->get(route('admin.instituciones.index'))->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_also_get_clinical_accreditation(): void
    {
        $this->actingAs($this->admin)->post('/admin/usuarios', [
            'first_name' => 'Ana', 'last_name' => 'Admin', 'email' => 'ana.admin@atulado.com.mx',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
            'role' => 'admin', 'clinico_admin' => '1',
        ]);

        $u = User::where('email', 'ana.admin@atulado.com.mx')->sole();
        $this->assertTrue($u->is_admin);
        $this->assertTrue($u->isClinicoAcreditado());
    }

    public function test_clinical_only_account_sees_the_clinical_panel_but_not_administration(): void
    {
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);

        $this->actingAs($clinico)->get(route('admin.instituciones.index'))->assertOk();
        $this->actingAs($clinico)->get(route('admin.cola.index'))->assertOk();
        $this->actingAs($clinico)->get(route('admin.instituciones.show', $this->institucion))
            ->assertOk()
            ->assertDontSee('Editar empresa')
            ->assertDontSee('data-pane="padron"', false);

        $this->actingAs($clinico)->get(route('admin.users.index'))->assertRedirect(route('dashboard'));
        $this->actingAs($clinico)->get(route('admin.dashboard'))->assertRedirect(route('dashboard'));
        $this->actingAs($clinico)->post(route('admin.instituciones.personas.store', $this->institucion), [])->assertRedirect(route('dashboard'));
    }

    /* ─────────── Usuarios ↔ instituciones nuevas ─────────── */

    public function test_assigning_an_institution_from_users_creates_a_membership_and_moving_keeps_history(): void
    {
        $otra = Institucion::factory()->create();
        $user = User::factory()->create();
        $datos = fn ($inst) => ['name' => $user->name, 'email' => $user->email, 'role' => 'usuario', 'status' => 'activo', 'institucion_id' => $inst];

        $this->actingAs($this->admin)->put("/admin/usuarios/{$user->id}", $datos($this->institucion->id));
        $this->assertSame($this->institucion->id, $user->membresiaActiva()->value('institucion_id'));

        $this->actingAs($this->admin)->put("/admin/usuarios/{$user->id}", $datos($otra->id));
        $this->assertSame($otra->id, $user->membresiaActiva()->value('institucion_id'));
        $this->assertSame('baja', Membresia::where('user_id', $user->id)->where('institucion_id', $this->institucion->id)->value('estado'));
    }

    public function test_old_structure_panel_redirects_to_institutions(): void
    {
        $this->actingAs($this->admin)->get('/admin/altas-estructura')->assertRedirect('/admin/instituciones');
    }

    /* ─────────── Padrón: una persona a la vez ─────────── */

    private function datosPersona(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Rosa Poot', 'correo' => 'rosa@maya.mx', 'numero_empleado' => '77',
            'departamento_id' => $this->areaId, 'puesto' => 'Cocinera', 'turno' => 'matutino',
        ], $cambios);
    }

    public function test_admin_adds_edits_and_removes_a_person(): void
    {
        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.store', $this->institucion), $this->datosPersona())
            ->assertRedirect(route('admin.instituciones.show', $this->institucion) . '#tab-padron');

        $m = Membresia::whereHas('user', fn ($q) => $q->where('email', 'rosa@maya.mx'))->sole();
        $this->assertSame('invitado', $m->estado);
        $this->assertSame('77', $m->numero_empleado);

        $this->actingAs($this->admin)->put(route('admin.instituciones.personas.update', [$this->institucion, $m]), $this->datosPersona(['puesto' => 'Jefa de cocina', 'nombre' => 'Rosa Poot Canché']));
        $this->assertSame('Jefa de cocina', $m->fresh()->puesto);
        $this->assertSame('Rosa Poot Canché', $m->user->fresh()->name);

        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.baja', [$this->institucion, $m]), ['motivo' => 'Renuncia']);
        $this->assertSame('baja', $m->fresh()->estado);

        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.reactivar', [$this->institucion, $m]));
        $this->assertSame('invitado', $m->fresh()->estado);
    }

    public function test_person_validation_rules(): void
    {
        $ajena = Institucion::factory()->create();
        app(EstructuraInstitucionalService::class)->crearMacroAreas($ajena, ['Ventas']);

        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.store', $this->institucion), $this->datosPersona());

        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.store', $this->institucion), $this->datosPersona(['correo' => 'otro@maya.mx']))
            ->assertSessionHasErrors('numero_empleado');
        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.store', $this->institucion), $this->datosPersona(['numero_empleado' => '78']))
            ->assertSessionHasErrors('correo');
        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.store', $this->institucion), $this->datosPersona(['correo' => 'x@maya.mx', 'numero_empleado' => '79', 'departamento_id' => $ajena->departamentos()->value('id')]))
            ->assertSessionHasErrors('departamento_id');
    }

    public function test_reactivation_only_within_30_days(): void
    {
        $m = Membresia::factory()->create(['institucion_id' => $this->institucion->id, 'estado' => 'baja', 'baja_en' => now()->subDays(40)]);

        $this->actingAs($this->admin)->post(route('admin.instituciones.personas.reactivar', [$this->institucion, $m]))->assertSessionHas('error');
        $this->assertSame('baja', $m->fresh()->estado);
    }

    public function test_institution_page_shows_padron_and_invitation_tabs_to_admins(): void
    {
        Membresia::factory()->create(['institucion_id' => $this->institucion->id, 'user_id' => User::factory()->create(['name' => 'Pedro Chim'])->id, 'estado' => 'invitado']);

        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $this->institucion))
            ->assertOk()
            ->assertSee('Áreas y Macro-Grupos')
            ->assertSee('Padrón de Colaboradores (1)')
            ->assertSee('Enviar invitación a todos (1)')
            ->assertSee('Pedro Chim');
    }
}
