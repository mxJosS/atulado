<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use App\Support\Suplantacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * «Entrar como»: sólo las cuentas principales abren la sesión de otra cuenta,
 * vuelven a la suya con un clic y no dejan rastro de uso a nombre de la persona.
 */
class SuplantacionTest extends TestCase
{
    use RefreshDatabase;

    private User $principal;

    protected function setUp(): void
    {
        parent::setUp();

        config(['atulado.superadmin_emails' => ['principal@atulado.com.mx', 'otra.principal@atulado.com.mx']]);
        $this->principal = User::factory()->create(['email' => 'principal@atulado.com.mx', 'is_admin' => true, 'role' => 'admin']);
    }

    public function test_a_main_account_enters_as_a_user_and_comes_back(): void
    {
        $persona = User::factory()->create(['name' => 'Ana Uc']);

        $this->actingAs($this->principal)->post(route('admin.users.entrar', $persona))
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($persona);

        $this->get(route('dashboard'))->assertOk()->assertSee('Estás dentro de la cuenta de')->assertSee('Volver a mi cuenta');

        $this->post(route('logout'))->assertRedirect(route('admin.users.index'));
        $this->assertAuthenticatedAs($this->principal);
        $this->assertFalse(session()->has(Suplantacion::CLAVE));

        $this->assertDatabaseHas('bitacora_admin', ['accion' => 'suplantacion_inicio', 'actor_id' => $this->principal->id, 'objeto_id' => $persona->id]);
        $this->assertDatabaseHas('bitacora_admin', ['accion' => 'suplantacion_fin', 'actor_id' => $this->principal->id, 'objeto_id' => $persona->id]);
    }

    public function test_a_main_account_enters_as_a_clinician(): void
    {
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);

        $this->actingAs($this->principal)->post(route('admin.users.entrar', $clinico));

        $this->assertAuthenticatedAs($clinico);
        $this->get(route('admin.cola.index'))->assertOk();
    }

    public function test_only_main_accounts_can_enter_and_never_into_another_main_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $persona = User::factory()->create();
        $otraPrincipal = User::factory()->create(['email' => 'otra.principal@atulado.com.mx', 'is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.entrar', $persona))->assertSessionHas('error');
        $this->assertAuthenticatedAs($admin);

        $this->actingAs($this->principal)->post(route('admin.users.entrar', $otraPrincipal))->assertSessionHas('error');
        $this->assertAuthenticatedAs($this->principal);

        $this->actingAs($persona)->post(route('admin.users.entrar', $admin))->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($persona);

        $this->assertDatabaseMissing('bitacora_admin', ['accion' => 'suplantacion_inicio']);
    }

    public function test_it_can_be_turned_off(): void
    {
        config(['atulado.suplantacion' => false]);
        $persona = User::factory()->create();

        $this->actingAs($this->principal)->post(route('admin.users.entrar', $persona))->assertSessionHas('error');
        $this->assertAuthenticatedAs($this->principal);
    }

    public function test_the_button_shows_only_to_main_accounts(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $persona = User::factory()->create();

        $this->actingAs($this->principal)->get(route('admin.users.index'))
            ->assertSee(route('admin.users.entrar', $persona))
            ->assertDontSee(route('admin.users.entrar', $this->principal));

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertDontSee('Entrar como');
    }

    public function test_entering_does_not_count_as_the_person_using_the_app(): void
    {
        $institucion = Institucion::factory()->create();
        $persona = User::factory()->create();
        $membresia = Membresia::factory()->invitada()->en($institucion)->create(['user_id' => $persona->id]);

        $this->actingAs($this->principal)->post(route('admin.users.entrar', $persona));
        $this->get(route('tools.respiracion'))->assertOk();
        $this->post(route('logout'));

        $this->assertSame('invitado', $membresia->fresh()->estado);
        $this->assertDatabaseCount('interacciones', 0);
    }

    public function test_consent_data_choices_and_account_deletion_stay_with_the_owner(): void
    {
        $sinConsentimiento = User::factory()->sinConsentimiento()->create();
        $persona = User::factory()->create();

        $this->actingAs($this->principal)->post(route('admin.users.entrar', $sinConsentimiento));
        $this->post(route('consentimiento.guardar'), [
            'acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1',
        ])->assertSessionHas('error');
        $this->assertNull($sinConsentimiento->fresh()->consentimiento_en);
        $this->post(route('logout'));

        $this->post(route('admin.users.entrar', $persona));
        $this->put(route('profile.datos'), ['acepta_boletin' => '1'])->assertSessionHas('error');
        $this->assertFalse($persona->fresh()->acepta_boletin);
        $this->delete(route('profile.destroy'), ['confirmacion' => 'ELIMINAR', 'password_eliminar' => 'password'])->assertSessionHas('error');
        $this->assertModelExists($persona);
    }
}
