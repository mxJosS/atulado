<?php

namespace App\Providers;

use App\Models\Membresia;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        // Una cuenta del padrón queda «activa» la primera vez que la persona entra,
        // por cualquier vía: contraseña, Google, invitación o «recordarme».
        Event::listen(Login::class, function (Login $evento): void {
            Membresia::where('user_id', $evento->user->getAuthIdentifier())
                ->where('estado', 'invitado')
                ->update(['estado' => 'activo', 'activado_en' => now()]);
        });

        // Custom Spanish & Branded Password Reset Email for A Tu Lado
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new \App\Mail\ResetPasswordMail($url, $notifiable))
                ->to($notifiable->getEmailForPasswordReset());
        });
    }
}
