<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationCodeMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailVerificationCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_generates_6_digit_code_and_sends_email(): void
    {
        Mail::fake();

        $response = $this->post('/registro', [
            'name' => 'Valeria Morales',
            'email' => 'valeria@ejemplo.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'avatar_color' => 'sage',
        ]);

        $response->assertRedirect('/verificar-codigo');
        $this->assertAuthenticated();

        $user = User::where('email', 'valeria@ejemplo.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->verification_code);
        $this->assertEquals(6, strlen($user->verification_code));
        $this->assertTrue(Carbon::now()->lt($user->verification_code_expires_at));

        Mail::assertSent(EmailVerificationCodeMail::class, function ($mail) use ($user) {
            return $mail->hasTo('valeria@ejemplo.com') &&
                   $mail->code === $user->verification_code &&
                   $mail->name === 'Valeria Morales';
        });
    }

    public function test_registration_with_mint_avatar_color_succeeds(): void
    {
        Mail::fake();

        $response = $this->post('/registro', [
            'name' => 'Angel Espinosa',
            'email' => 'jangel2003.kimba4@gmail.com',
            'password' => 'secreto123',
            'password_confirmation' => 'secreto123',
            'avatar_color' => 'mint',
        ]);

        $response->assertRedirect('/verificar-codigo');
        $this->assertAuthenticated();

        $user = User::where('email', 'jangel2003.kimba4@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('mint', $user->avatar_color);
        $this->assertNotNull($user->verification_code);
    }

    public function test_unverified_user_is_redirected_to_verify_screen_from_dashboard(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_code' => '123456',
            'verification_code_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/verificar-codigo');
    }

    public function test_incorrect_code_fails_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_code' => '654321',
            'verification_code_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $response = $this->actingAs($user)
            ->post('/verificar-codigo', [
                'code' => '111111',
            ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_code_fails_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_code' => '654321',
            'verification_code_expires_at' => Carbon::now()->subMinutes(1),
        ]);

        $response = $this->actingAs($user)
            ->post('/verificar-codigo', [
                'code' => '654321',
            ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_valid_code_successfully_verifies_email_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->unverified()->create([
            'verification_code' => '789456',
            'verification_code_expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $response = $this->actingAs($user)
            ->post('/verificar-codigo', [
                'code' => '789456',
            ]);

        $response->assertRedirect('/dashboard');
        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser->email_verified_at);
        $this->assertNull($freshUser->verification_code);
        $this->assertNull($freshUser->verification_code_expires_at);

        // Now dashboard is accessible
        $this->actingAs($freshUser)
            ->get('/dashboard')
            ->assertStatus(200);
    }

    public function test_user_can_resend_verification_code(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create([
            'verification_code' => '111111',
            'verification_code_expires_at' => Carbon::now()->subMinutes(5),
        ]);

        $response = $this->actingAs($user)
            ->post('/verificar-codigo/reenviar');

        $response->assertSessionHas('status');

        $freshUser = $user->fresh();
        $this->assertNotEquals('111111', $freshUser->verification_code);
        $this->assertEquals(6, strlen($freshUser->verification_code));
        $this->assertTrue(Carbon::now()->lt($freshUser->verification_code_expires_at));

        Mail::assertSent(EmailVerificationCodeMail::class, function ($mail) use ($freshUser) {
            return $mail->hasTo($freshUser->email) &&
                   $mail->code === $freshUser->verification_code;
        });
    }

    public function test_unverified_user_logging_in_is_redirected_to_verify_screen(): void
    {
        Mail::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'login_unverified@ejemplo.com',
            'password' => Hash::make('password123'),
            'verification_code' => null,
            'verification_code_expires_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'login_unverified@ejemplo.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/verificar-codigo');
        $this->assertAuthenticatedAs($user);

        $freshUser = $user->fresh();
        $this->assertNotNull($freshUser->verification_code);

        Mail::assertSent(EmailVerificationCodeMail::class);
    }

    public function test_email_verification_mail_renders_content_correctly(): void
    {
        $mail = new EmailVerificationCodeMail('839201', 'Carlos Sanchez');
        $mail->assertHasSubject('Tu código de verificación de A Tu Lado: 839201');

        $rendered = $mail->render();
        $this->assertStringContainsString('839201', $rendered);
        $this->assertStringContainsString('Carlos Sanchez', $rendered);
        $this->assertStringContainsString('A Tu Lado', $rendered);
        $this->assertStringContainsString('15 minutos', $rendered);
    }
}
