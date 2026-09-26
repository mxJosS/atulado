<?php

namespace Tests\Feature;

use App\Models\Ajuste;
use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as UsuarioGoogle;
use Mockery;
use Tests\TestCase;

/**
 * «Invitar por link»: un enlace por institución para registrarse (con Google
 * o con correo) y quedar en su padrón. En el enlace Google siempre funciona,
 * aunque esté apagado en el panel.
 */
class InvitarPorLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Institucion $inst;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_id' => 'id-prueba', 'services.google.client_secret' => 'secreto-prueba']);
        Ajuste::guardar(Ajuste::GOOGLE_HABILITADO, '0'); // apagado en el panel
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $this->inst = Institucion::factory()->create(['nombre_corto' => 'ITM']);
    }

    private function generar(?Departamento $area = null): string
    {
        $this->actingAs($this->admin)->post(route('admin.instituciones.enlace.generar', $this->inst))
            ->assertRedirect(route('admin.instituciones.show', $this->inst) . '#tab-enlace');
        if ($area) {
            $this->actingAs($this->admin)->put(route('admin.instituciones.enlace.actualizar', $this->inst), ['activo' => '1', 'departamento_id' => $area->id]);
        }
        auth()->logout();

        return $this->inst->fresh()->enlace_token;
    }

    private function googleDevuelve(string $email): void
    {
        $usuario = (new UsuarioGoogle())->setRaw(['email' => $email, 'email_verified' => true])
            ->map(['id' => 'g-' . $email, 'name' => 'Persona Google', 'email' => $email, 'avatar' => null]);
        $proveedor = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $proveedor->shouldReceive('enablePKCE')->andReturnSelf();
        $proveedor->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        $proveedor->shouldReceive('user')->andReturn($usuario);
        Socialite::shouldReceive('driver')->with('google')->andReturn($proveedor);
    }

    public function test_the_admin_generates_the_link_in_its_own_tab(): void
    {
        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $this->inst))->assertOk()
            ->assertSee('Invitar por link')
            ->assertSee('Generar link de invitación');

        $token = $this->generar();

        $this->actingAs($this->admin)->get(route('admin.instituciones.show', $this->inst))->assertOk()
            ->assertSee(route('unirse.mostrar', $token))
            ->assertSee('Copiar link');
        $this->assertDatabaseHas('bitacora_admin', ['accion' => 'enlace_generado', 'objeto_id' => $this->inst->id]);
    }

    public function test_the_link_page_always_offers_google_even_if_it_is_off_in_the_panel(): void
    {
        $token = $this->generar();

        $this->get(route('login'))->assertDontSee('Acceder con Google');
        $this->get(route('auth.google'))->assertRedirect(route('login'));

        $this->get(route('unirse.mostrar', $token))->assertOk()
            ->assertSee('Únete a ITM')
            ->assertSee('Continuar con Google')
            ->assertSee(route('unirse.google', $token))
            ->assertSee('name="acepto_sensibles"', false);
    }

    public function test_registering_with_email_from_the_link_joins_the_institution(): void
    {
        Mail::fake();
        $area = Departamento::factory()->para($this->inst)->create(['nombre' => 'Sistemas']);
        $token = $this->generar($area);

        $this->get(route('unirse.mostrar', $token))->assertOk();
        $this->post(route('register'), [
            'name' => 'Ana Uc', 'email' => 'ana@alumnos.mx', 'password' => 'secreto123', 'password_confirmation' => 'secreto123',
            'acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1',
        ])->assertRedirect('/verificar-codigo');

        $ana = User::where('email', 'ana@alumnos.mx')->firstOrFail();
        $this->assertDatabaseHas('membresias', [
            'institucion_id' => $this->inst->id, 'user_id' => $ana->id, 'estado' => 'activo',
            'departamento_id' => $area->id, 'origen' => 'enlace',
        ]);
    }

    public function test_google_from_the_link_works_while_google_is_off_and_joins_the_institution(): void
    {
        $token = $this->generar();
        $this->googleDevuelve('beto@gmail.com');

        $this->get(route('unirse.mostrar', $token));
        $this->get(route('unirse.google', $token))->assertRedirect('https://accounts.google.com/o/oauth2/auth');
        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));

        $beto = User::where('email', 'beto@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($beto);
        $this->assertDatabaseHas('membresias', ['institucion_id' => $this->inst->id, 'user_id' => $beto->id, 'origen' => 'enlace']);

        // Primero da su consentimiento; el aviso de que ya se unió sigue a la vista.
        $this->get(route('dashboard'))->assertRedirect(route('consentimiento.show'));
    }

    public function test_the_regular_google_button_still_obeys_the_panel_switch(): void
    {
        $this->googleDevuelve('carla@gmail.com');

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertDatabaseMissing('users', ['email' => 'carla@gmail.com']);
    }

    public function test_an_existing_account_logs_in_from_the_link_or_joins_with_one_click(): void
    {
        $token = $this->generar();
        User::factory()->create(['email' => 'dora@prueba.mx', 'password' => Hash::make('secreto123')]);

        $this->get(route('unirse.mostrar', $token));
        $this->post('/login', ['email' => 'dora@prueba.mx', 'password' => 'secreto123'])->assertRedirect();
        $this->assertDatabaseHas('membresias', ['institucion_id' => $this->inst->id, 'user_id' => User::where('email', 'dora@prueba.mx')->value('id')]);

        $eva = User::factory()->create();
        $this->actingAs($eva)->get(route('unirse.mostrar', $token))->assertOk()->assertSee('Unirme a ITM');
        $this->actingAs($eva)->post(route('unirse.unir', $token))->assertRedirect(route('dashboard'))->assertSessionHas('success');
        $this->assertDatabaseHas('membresias', ['institucion_id' => $this->inst->id, 'user_id' => $eva->id, 'estado' => 'activo']);
    }

    public function test_staff_and_people_of_another_institution_are_not_moved(): void
    {
        $token = $this->generar();
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $otra = Institucion::factory()->create();
        $persona = User::factory()->create();
        Membresia::factory()->en($otra)->create(['user_id' => $persona->id]);

        $this->actingAs($clinico)->post(route('unirse.unir', $token))->assertSessionHas('error');
        $this->actingAs($persona)->post(route('unirse.unir', $token))->assertSessionHas('error');

        $this->assertDatabaseMissing('membresias', ['institucion_id' => $this->inst->id]);
    }

    public function test_a_disabled_or_replaced_link_stops_working(): void
    {
        $viejo = $this->generar();
        $nuevo = $this->generar(); // «Cambiar por un link nuevo»

        $this->assertNotSame($viejo, $nuevo);
        $this->get(route('unirse.mostrar', $viejo))->assertNotFound()->assertSee('Este enlace ya no funciona');
        $this->get(route('unirse.mostrar', $nuevo))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.instituciones.enlace.actualizar', $this->inst), ['activo' => '0']);
        auth()->logout();
        $this->get(route('unirse.mostrar', $nuevo))->assertNotFound();
        $this->get(route('unirse.google', $nuevo))->assertNotFound();
    }

    public function test_only_administration_manages_the_link(): void
    {
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $clinico->institucionesAsignadas()->attach($this->inst);

        $this->actingAs($clinico)->get(route('admin.instituciones.show', $this->inst))->assertOk()->assertDontSee('Generar link de invitación');
        $this->actingAs($clinico)->post(route('admin.instituciones.enlace.generar', $this->inst))->assertRedirect(route('dashboard'));
        $this->assertNull($this->inst->fresh()->enlace_token);
    }
}
