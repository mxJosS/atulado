<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('¿Olvidaste tu contraseña?');
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'test@atulado.com.mx',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'sample-token', 'email' => 'test@atulado.com.mx']));

        $response->assertStatus(200);
        $response->assertSee('Crea tu nueva contraseña');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'paciente@atulado.com.mx',
            'password' => Hash::make('antigua123'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'nuevaClave123',
            'password_confirmation' => 'nuevaClave123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('nuevaClave123', $user->fresh()->password));
    }

    public function test_custom_password_reset_mailable_renders_spanish_branded_template(): void
    {
        $user = User::factory()->create([
            'name' => 'Mariana Lopez',
            'email' => 'mariana@atulado.com.mx',
        ]);

        $url = 'https://atulado.com.mx/restablecer-contrasena/token-12345?email=mariana%40atulado.com.mx';
        $mailable = new ResetPasswordMail($url, $user);

        $this->assertEquals('Restablecer tu contraseña — A tu lado', $mailable->envelope()->subject);
        $this->assertEquals('emails.reset-password', $mailable->content()->view);
        $this->assertEquals('emails.reset-password-text', $mailable->content()->text);

        // Render HTML view
        $html = view($mailable->content()->view, $mailable->content()->with)->render();
        $this->assertStringContainsString('Restablecer tu contraseña', $html);
        $this->assertStringContainsString('Mariana Lopez', $html);
        $this->assertStringContainsString('A Tu Lado', $html);
        $this->assertStringContainsString('token-12345', $html);
        $this->assertStringContainsString('🌲', $html);

        // Render Text view
        $text = view($mailable->content()->text, $mailable->content()->with)->render();
        $this->assertStringContainsString('Restablecer tu contraseña', $text);
        $this->assertStringContainsString('Mariana Lopez', $text);
        $this->assertStringContainsString('token-12345', $text);
        $this->assertStringContainsString('hola@atulado.com.mx', $text);
    }

    public function test_user_send_password_reset_notification_dispatches_branded_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'name' => 'Carlos Ruiz',
            'email' => 'carlos@atulado.com.mx',
        ]);

        $user->sendPasswordResetNotification('token-carlos-xyz');

        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use ($user) {
            $mail = $notification->toMail($user);
            $this->assertInstanceOf(ResetPasswordMail::class, $mail);
            $this->assertEquals('Restablecer tu contraseña — A tu lado', $mail->envelope()->subject);
            return true;
        });
    }

    public function test_fallback_vendor_notification_template_has_brand_styling(): void
    {
        $markdown = app(\Illuminate\Mail\Markdown::class);
        $rendered = $markdown->render('vendor.notifications.email', [
            'level' => 'info',
            'greeting' => '¡Hola!',
            'introLines' => ['Línea de prueba institucional.'],
            'actionText' => 'Acción de prueba',
            'actionUrl' => 'https://atulado.com.mx/prueba',
            'displayableActionUrl' => 'https://atulado.com.mx/prueba',
            'outroLines' => ['Línea final institucional.'],
            'salutation' => null,
        ])->toHtml();

        $this->assertStringContainsString('🌲', $rendered);
        $this->assertStringContainsString('a tu', $rendered);
        $this->assertStringContainsString('lado', $rendered);
        $this->assertStringContainsString('Acción de prueba', $rendered);
        $this->assertStringNotContainsString('Laravel Logo', $rendered);
    }
}
