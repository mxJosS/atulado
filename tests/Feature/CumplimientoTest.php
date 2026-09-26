<?php

namespace Tests\Feature;

use App\Models\AuditoriaClinica;
use App\Models\BitacoraAdmin;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Consentimiento expreso, eliminación de la cuenta, conservación
 * seudonimizada, preferencias de datos y bitácora de administración.
 */
class CumplimientoTest extends TestCase
{
    use RefreshDatabase;

    /* ─────────── Consentimiento ─────────── */

    public function test_register_requires_explicit_consent_and_age_and_records_optional_purposes(): void
    {
        Mail::fake();
        $datos = ['name' => 'Ana', 'email' => 'ana@prueba.mx', 'password' => 'secreto123', 'password_confirmation' => 'secreto123'];

        $this->post('/registro', $datos)->assertSessionHasErrors(['acepto_aviso', 'acepto_sensibles', 'mayor_de_edad']);
        $this->assertDatabaseMissing('users', ['email' => 'ana@prueba.mx']);

        $this->post('/registro', $datos + ['acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1', 'acepta_boletin' => '1'])
            ->assertRedirect('/verificar-codigo');

        $ana = User::where('email', 'ana@prueba.mx')->first();
        $this->assertNotNull($ana->consentimiento_en);
        $this->assertSame(User::VERSION_AVISO, $ana->consentimiento_version);
        $this->assertTrue($ana->acepta_boletin);
        $this->assertFalse($ana->acepta_investigacion);
    }

    public function test_the_register_form_shows_unchecked_consent_boxes(): void
    {
        $this->get('/registro')->assertOk()
            ->assertSee('name="acepto_aviso"', false)
            ->assertSee('name="acepto_sensibles"', false)
            ->assertSee('name="mayor_de_edad"', false)
            ->assertSee('consentimiento expreso')
            ->assertDontSee('name="acepto_aviso" value="1" checked', false);
    }

    public function test_an_account_without_consent_gives_it_once_before_using_the_app(): void
    {
        $user = User::factory()->sinConsentimiento()->create();

        $this->actingAs($user)->get(route('mood.history'))->assertRedirect(route('consentimiento.show'));
        $this->actingAs($user)->get(route('consentimiento.show'))->assertOk()->assertSee('Antes de empezar');

        $this->actingAs($user)->post(route('consentimiento.guardar'), [])->assertSessionHasErrors('acepto_sensibles');
        $this->actingAs($user)->post(route('consentimiento.guardar'), ['acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1'])
            ->assertRedirect(route('mood.history'));

        $this->actingAs($user->fresh())->get(route('mood.history'))->assertOk();
    }

    public function test_secondary_purposes_can_be_changed_from_the_profile(): void
    {
        $user = User::factory()->create(['acepta_investigacion' => true]);

        $this->actingAs($user)->get(route('profile.show'))->assertOk()->assertSee('Tus datos')->assertSee('Eliminar mi cuenta');
        $this->actingAs($user)->put(route('profile.datos'), ['acepta_investigacion' => '0', 'acepta_boletin' => '1'])->assertSessionHas('success');

        $this->assertFalse($user->fresh()->acepta_investigacion);
        $this->assertTrue($user->fresh()->acepta_boletin);
    }

    /* ─────────── Eliminar la cuenta ─────────── */

    public function test_a_person_deletes_the_account_and_crisis_records_stay_pseudonymized(): void
    {
        $inst = Institucion::factory()->create();
        $user = User::factory()->create(['password' => Hash::make('secreto123')]);
        Membresia::factory()->en($inst)->create(['user_id' => $user->id]);
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $caso = EventoCrisis::create(['user_id' => $user->id, 'institucion_id' => $inst->id, 'nivel' => 'ROJO', 'disparado_en' => now()->subMonth(), 'estado' => 'cerrado']);
        $acceso = AuditoriaClinica::create(['profesional_id' => $clinico->id, 'usuario_consultado_id' => $user->id, 'institucion_id' => $inst->id, 'accion' => 'consulta_detalle', 'motivo' => 'Seguimiento']);

        $this->actingAs($user)->delete(route('profile.destroy'), ['confirmacion' => 'ELIMINAR', 'password_eliminar' => 'otra'])
            ->assertSessionHasErrors(['password_eliminar'], null, 'eliminarCuenta');
        $this->assertNotNull($user->fresh());

        $this->actingAs($user)->delete(route('profile.destroy'), ['confirmacion' => 'ELIMINAR', 'password_eliminar' => 'secreto123'])
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertNull(User::find($user->id));
        $this->assertDatabaseMissing('membresias', ['user_id' => $user->id]);
        $this->assertNull($caso->fresh()->user_id);             // se conserva, sin identidad
        $this->assertNull(AuditoriaClinica::find($acceso->id)->usuario_consultado_id);
    }

    public function test_an_open_crisis_case_blocks_self_deletion_and_admins_delete_from_the_panel(): void
    {
        $user = User::factory()->create(['google_id' => 'g-1']); // cuenta de Google: basta la confirmación
        EventoCrisis::create(['user_id' => $user->id, 'nivel' => 'ROJO', 'disparado_en' => now(), 'estado' => 'abierto']);

        $this->actingAs($user)->delete(route('profile.destroy'), ['confirmacion' => 'ELIMINAR'])->assertSessionHas('error');
        $this->assertNotNull($user->fresh());

        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $this->actingAs($admin)->delete(route('profile.destroy'), ['confirmacion' => 'ELIMINAR', 'password_eliminar' => 'password'])->assertSessionHas('error');
        $this->assertNotNull($admin->fresh());
    }

    public function test_pseudonymized_records_are_purged_after_five_years(): void
    {
        $viejo = EventoCrisis::create(['user_id' => null, 'nivel' => 'ROJO', 'disparado_en' => now()->subYears(6), 'estado' => 'cerrado']);
        $reciente = EventoCrisis::create(['user_id' => null, 'nivel' => 'ROJO', 'disparado_en' => now()->subYears(2), 'estado' => 'cerrado']);

        $this->artisan('atulado:purgar-seudonimizados')->assertSuccessful();

        $this->assertNull(EventoCrisis::find($viejo->id));
        $this->assertNotNull(EventoCrisis::find($reciente->id));
    }

    /* ─────────── Bitácora de administración ─────────── */

    public function test_permission_changes_deletions_and_exports_are_logged(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin', 'email' => 'admin@atulado.mx']);
        $persona = User::factory()->create(['email' => 'persona@prueba.mx']);

        $this->actingAs($admin)->put(route('admin.users.update', $persona), [
            'name' => $persona->name, 'email' => $persona->email, 'role' => 'admin', 'status' => 'activo',
        ]);
        $this->actingAs($admin)->get(route('admin.dashboard.exportar-padron'))->assertOk();
        $this->actingAs($admin)->delete(route('admin.users.destroy', User::factory()->create(['email' => 'borrar@prueba.mx'])));

        $this->assertDatabaseHas('bitacora_admin', ['accion' => 'usuario_permisos', 'objeto_id' => $persona->id, 'actor_email' => 'admin@atulado.mx']);
        $this->assertTrue(BitacoraAdmin::where('accion', 'usuario_permisos')->value('detalle') !== null
            && str_contains(BitacoraAdmin::where('accion', 'usuario_permisos')->value('detalle'), 'usuario → admin+admin'));
        $this->assertDatabaseHas('bitacora_admin', ['accion' => 'padron_exportado']);
        $this->assertDatabaseHas('bitacora_admin', ['accion' => 'usuario_eliminado']);
    }

    /* ─────────── Páginas legales ─────────── */

    public function test_legal_pages_list_cookies_third_parties_and_the_current_authority(): void
    {
        $this->get(route('privacidad'))->assertOk()
            ->assertSee('a-tu-lado-session')
            ->assertSee('XSRF-TOKEN')
            ->assertSee('Resend (Estados Unidos)')
            ->assertSee('Google Fonts')
            ->assertSee('Secretaría Anticorrupción y Buen')
            ->assertSee('Datos que nos entrega tu organización')
            ->assertDontSee('Instituto Nacional de')
            ->assertDontSee('class="pend"', false);

        $this->get(route('terminos'))->assertOk()
            ->assertSee('Es gratuito para las personas usuarias')
            ->assertDontSee('Se notifique al profesional designado');

        $this->get('/privacidad.html')->assertRedirect('/privacidad');
    }
}
