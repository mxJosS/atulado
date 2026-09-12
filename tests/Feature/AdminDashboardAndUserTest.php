<?php

namespace Tests\Feature;

use App\Models\ProfessionalVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardAndUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_accessing_user_dashboard_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_user_can_access_admin_dashboard_and_modules(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/usuarios')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/foros')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/solicitudes-profesionales')->assertStatus(200);
    }

    public function test_admin_can_view_profile_with_admin_layout_and_change_password(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin.test@atulado.com.mx',
            'password' => Hash::make('ActualPass123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/perfil');
        $response->assertStatus(200);
        $response->assertSee('CONSOLA ADMINISTRADOR');
        $response->assertSee('Cambiar Contraseña');
        $response->assertDontSee('¿Eres Profesional de la Salud Mental?');
        $response->assertDontSee('Contacto Principal de Emergencia');

        // Test updating admin password
        $updateResponse = $this->actingAs($admin)->put('/perfil/password', [
            'current_password' => 'ActualPass123',
            'password' => 'NuevaSuperPass2026',
            'password_confirmation' => 'NuevaSuperPass2026',
        ]);

        $updateResponse->assertRedirect(route('profile.show'));
        $this->assertTrue(Hash::check('NuevaSuperPass2026', $admin->fresh()->password));
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@atulado.com.mx',
            'password' => Hash::make('Atulado_2026'),
            'is_admin' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@atulado.com.mx',
            'password' => 'Atulado_2026',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_create_another_admin_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/usuarios', [
            'first_name' => 'Carlos',
            'last_name' => 'Gonzalez',
            'email' => 'carlos.admin@atulado.com.mx',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Carlos Gonzalez',
            'email' => 'carlos.admin@atulado.com.mx',
            'is_admin' => true,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_create_professional_with_automatic_verification(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/usuarios', [
            'first_name' => 'Patricia',
            'last_name' => 'Jimenez',
            'email' => 'patricia.pro@atulado.com.mx',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'role' => 'profesional',
            'education_level' => 'maestria',
            'license_number' => 'CED-88776655',
            'institution' => 'Facultad de Psicologia UNAM',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        
        $proUser = User::where('email', 'patricia.pro@atulado.com.mx')->first();
        $this->assertNotNull($proUser);
        $this->assertEquals('profesional', $proUser->role);
        $this->assertEquals('CED-88776655', $proUser->license_number);
        $this->assertStringContainsString('Mtro.', $proUser->professional_title);

        $this->assertDatabaseHas('professional_verifications', [
            'user_id' => $proUser->id,
            'status' => 'aprobada',
            'license_number' => 'CED-88776655',
            'education_level' => 'maestria',
            'reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_update_user_and_activate_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'name' => 'Usuario Pendiente',
            'email' => 'pendiente@atulado.com.mx',
            'email_verified_at' => null,
            'role' => 'paciente',
        ]);

        $response = $this->actingAs($admin)->put("/admin/usuarios/{$user->id}", [
            'name' => 'Usuario Activado',
            'email' => 'activado@atulado.com.mx',
            'role' => 'profesional',
            'status' => 'activo',
            'license_number' => 'CED-998877',
            'institution' => 'Hospital Psiquiátrico Fray Bernardino',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user->refresh();
        $this->assertEquals('Usuario Activado', $user->name);
        $this->assertEquals('activado@atulado.com.mx', $user->email);
        $this->assertEquals('profesional', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals('CED-998877', $user->license_number);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->delete("/admin/usuarios/{$admin->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $targetUser = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($admin)->delete("/admin/usuarios/{$targetUser->id}");

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }

    public function test_admin_dashboard_and_users_correctly_differentiate_regular_users_from_professionals(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $regularUser = User::factory()->create([
            'name' => 'Jose Angel Google',
            'email' => 'iscjoseangel@gmail.com',
            'role' => 'usuario',
            'is_admin' => false,
        ]);
        $proUser = User::factory()->create([
            'name' => 'Dra Sandra Psicologa',
            'email' => 'sandra@atulado.com.mx',
            'role' => 'profesional',
            'is_admin' => false,
            'license_number' => 'CED-123456',
        ]);

        // Dashboard test
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('totalAdmins', 1);
        $response->assertViewHas('totalProfessionals', 1);
        $response->assertViewHas('totalRegularUsers', 1);

        // Regular user should have "Usuario" badge in dashboard
        $response->assertSee('iscjoseangel@gmail.com');
        $response->assertSee('Usuario');

        // Users index test
        $indexResponse = $this->actingAs($admin)->get('/admin/usuarios');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Usuarios (1)');
        $indexResponse->assertSee('Profesionales (1)');
        $indexResponse->assertSee('Admins (1)');

        // Filter by usuario
        $usuarioFilterResponse = $this->actingAs($admin)->get('/admin/usuarios?rol=usuario');
        $usuarioFilterResponse->assertSee('iscjoseangel@gmail.com');
        $usuarioFilterResponse->assertDontSee('sandra@atulado.com.mx');

        // Filter by profesional
        $proFilterResponse = $this->actingAs($admin)->get('/admin/usuarios?rol=profesional');
        $proFilterResponse->assertSee('sandra@atulado.com.mx');
        $proFilterResponse->assertDontSee('iscjoseangel@gmail.com');
    }

    public function test_admin_can_update_user_to_regular_usuario_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'name' => 'Ex Profesional',
            'email' => 'expro@atulado.com.mx',
            'role' => 'profesional',
            'license_number' => 'CED-998877',
            'institution' => 'Clinica Central',
        ]);

        $response = $this->actingAs($admin)->put("/admin/usuarios/{$user->id}", [
            'name' => 'Usuario Estandar',
            'email' => 'expro@atulado.com.mx',
            'role' => 'usuario',
            'status' => 'activo',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user->refresh();
        $this->assertEquals('usuario', $user->role);
        $this->assertNull($user->license_number);
        $this->assertNull($user->institution);
    }

    public function test_admin_users_index_displays_registration_date_without_time(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create([
            'created_at' => now()->setDate(2026, 9, 12)->setTime(2, 16, 0),
        ]);

        $response = $this->actingAs($admin)->get('/admin/usuarios');
        $response->assertStatus(200);
        $response->assertSee('12/09/2026');
        $response->assertDontSee('12/09/2026 02:16');
    }

    public function test_google_callback_handles_access_denied_gracefully(): void
    {
        $response = $this->get('/auth/google/callback?error=access_denied');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }
}