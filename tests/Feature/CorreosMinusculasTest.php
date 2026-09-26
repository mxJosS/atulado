<?php

namespace Tests\Feature;

use App\Models\Departamento;
use App\Models\Institucion;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Los correos no distinguen mayúsculas en ninguna parte.
 */
class CorreosMinusculasTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_stores_lowercase_and_rejects_the_same_email_with_other_capitals(): void
    {
        Mail::fake();

        $this->post('/registro', [
            'name' => 'Ana Uc', 'email' => '  Ana.Uc@Correo.COM ',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
            'acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1',
        ])->assertRedirect('/verificar-codigo');

        $this->assertDatabaseHas('users', ['email' => 'ana.uc@correo.com']);
        auth()->logout();

        $this->post('/registro', [
            'name' => 'Ana otra vez', 'email' => 'ANA.UC@correo.com',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
            'acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1',
        ])->assertSessionHasErrors(['email' => 'Este correo electrónico ya está registrado.']);

        $this->assertSame(1, User::count());
    }

    public function test_login_works_with_any_capitalization(): void
    {
        User::factory()->create(['email' => 'luis@utm.mx', 'password' => Hash::make('secreto123')]);

        $this->post('/login', ['email' => 'LUIS@UTM.MX', 'password' => 'secreto123'])->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_the_model_always_saves_lowercase(): void
    {
        $user = User::factory()->create(['email' => 'Carla.Chi@Empresa.MX']);

        $this->assertSame('carla.chi@empresa.mx', $user->fresh()->email);
    }

    public function test_forgot_password_finds_the_account_with_capitals(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'beto@prueba.mx']);

        $this->post(route('password.email'), ['email' => 'Beto@Prueba.MX'])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_profile_and_admin_forms_save_lowercase(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'first_name' => 'Dora', 'last_name' => 'May', 'email' => 'Dora.May@Correo.MX',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
            'role' => 'usuario',
        ])->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'dora.may@correo.mx']);

        $usuario = User::factory()->create(['email' => 'eva@correo.mx']);
        $this->actingAs($usuario)->put(route('profile.update'), ['name' => 'Eva', 'email' => 'EVA.Nueva@Correo.MX'])
            ->assertSessionHasNoErrors();
        $this->assertSame('eva.nueva@correo.mx', $usuario->fresh()->email);
    }

    public function test_padron_finds_an_existing_account_regardless_of_capitals(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $institucion = Institucion::factory()->create();
        $area = Departamento::factory()->para($institucion)->create();
        $existente = User::factory()->create(['email' => 'fer@correo.mx']);

        $this->actingAs($admin)->post(route('admin.instituciones.personas.store', $institucion), [
            'nombre' => 'Fer', 'correo' => 'FER@Correo.mx', 'numero_empleado' => 'E-1', 'departamento_id' => $area->id,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, User::where('email', 'fer@correo.mx')->count());
        $this->assertDatabaseHas('membresias', ['institucion_id' => $institucion->id, 'user_id' => $existente->id]);
    }
}
