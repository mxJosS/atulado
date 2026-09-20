<?php

namespace Tests\Feature;

use App\Models\ProfessionalVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfessionalVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Usuario Normal',
            'email' => 'normal@atulado.com.mx',
            'role' => 'usuario',
            'is_admin' => false,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Director Clínico',
            'email' => 'admin@atulado.com.mx',
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    /**
     * Test 1: Un usuario regular puede enviar sus papeles y crear una solicitud pendiente
     */
    public function test_regular_user_can_submit_professional_verification_request(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user);

        $file = UploadedFile::fake()->create('cedula_profesional.pdf', 500, 'application/pdf');

        $response = $this->post(route('profile.verification.store'), [
            'full_name' => 'Dr. Andrés Gómez',
            'license_number' => '12345678',
            'education_level' => 'licenciatura',
            'document' => $file,
        ]);

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('professional_verifications', [
            'user_id' => $this->user->id,
            'full_name' => 'Dr. Andrés Gómez',
            'license_number' => '12345678',
            'education_level' => 'licenciatura',
            'status' => 'pendiente',
        ]);

        // Verificar que el archivo se guardó
        $verification = ProfessionalVerification::where('user_id', $this->user->id)->first();
        $this->assertNotNull($verification->document_path);
        Storage::disk('public')->assertExists($verification->document_path);
    }

    /**
     * Test 2: Validación de campos obligatorios
     */
    public function test_validation_fails_with_missing_required_fields(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('profile.verification.store'), [
            'full_name' => '',
            'license_number' => '',
            'education_level' => 'invalido',
        ]);

        $response->assertSessionHasErrors(['full_name', 'license_number', 'education_level']);
    }

    /**
     * Test 3: Un administrador puede aprobar la solicitud y promover al usuario a profesional
     */
    public function test_admin_can_approve_verification_and_promote_user(): void
    {
        $verification = ProfessionalVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Dra. María Elena',
            'license_number' => 'MED-998877',
            'education_level' => 'especialidad',
            'status' => 'pendiente',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.verification.approve', $verification));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $verification->refresh();
        $this->assertEquals('aprobada', $verification->status);
        $this->assertEquals($this->admin->id, $verification->reviewed_by);
        $this->assertNotNull($verification->reviewed_at);

        // El usuario promovido ahora es profesional
        $this->user->refresh();
        $this->assertEquals('profesional', $this->user->role);
        $this->assertEquals('MED-998877', $this->user->license_number);
        $this->assertTrue($this->user->isProfessional());
    }

    /**
     * Test 4: Un usuario no-admin no puede aprobar solicitudes
     */
    public function test_non_admin_cannot_approve_verifications(): void
    {
        $verification = ProfessionalVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Usuario Candidato',
            'license_number' => '112233',
            'education_level' => 'licenciatura',
            'status' => 'pendiente',
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('admin.verification.approve', $verification));
        $response->assertRedirect(route('dashboard'));

        $jsonResponse = $this->postJson(route('admin.verification.approve', $verification));
        $jsonResponse->assertStatus(403);
    }

    /**
     * Test 5: Un administrador puede ver la bandeja de solicitudes
     */
    public function test_admin_can_view_verifications_index(): void
    {
        ProfessionalVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Psic. Roberto Gómez',
            'license_number' => 'PSI-554433',
            'education_level' => 'maestria',
            'status' => 'pendiente',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.verifications.index'));
        $response->assertStatus(200);
        $response->assertSee('Psic. Roberto Gómez');
        $response->assertSee('PSI-554433');
    }

    /**
     * Test 6: Un usuario común no puede ver la bandeja administrativa
     */
    public function test_non_admin_cannot_view_verifications_index(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('admin.verifications.index'));
        $response->assertRedirect(route('dashboard'));

        $jsonResponse = $this->getJson(route('admin.verifications.index'));
        $jsonResponse->assertStatus(403);
    }

    /**
     * Test 7: Un administrador puede rechazar una solicitud con motivo
     */
    public function test_admin_can_reject_verification_with_notes(): void
    {
        $verification = ProfessionalVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Dr. Falso',
            'license_number' => '00000000',
            'education_level' => 'licenciatura',
            'status' => 'pendiente',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.verification.reject', $verification), [
            'admin_notes' => 'La cédula no corresponde a la profesión indicada.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('info');

        $verification->refresh();
        $this->assertEquals('rechazada', $verification->status);
        $this->assertEquals('La cédula no corresponde a la profesión indicada.', $verification->admin_notes);
        $this->assertEquals($this->admin->id, $verification->reviewed_by);

        // El usuario regular no fue promovido
        $this->user->refresh();
        $this->assertEquals('usuario', $this->user->role);
        $this->assertFalse($this->user->isProfessional());
    }
}
