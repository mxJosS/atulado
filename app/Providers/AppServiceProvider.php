<?php

namespace App\Providers;

use App\Models\Membresia;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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
            // «Entrar como» no es la persona entrando.
            if (\App\Support\Suplantacion::activa()) {
                return;
            }

            Membresia::where('user_id', $evento->user->getAuthIdentifier())
                ->where('estado', 'invitado')
                ->update(['estado' => 'activo', 'activado_en' => now()]);

            // Si venía de un enlace «Invitar por link», queda en esa institución.
            if ($evento->user instanceof \App\Models\User && app()->bound('session.store')) {
                app(\App\Services\EnlaceInstitucionService::class)->unirDesdeSesion($evento->user);
            }
        });

        // Fuera de producción, una consulta N+1 truena en vez de hacer lenta la página.
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!$this->app->isProduction());
        $this->limitesDeIntentos();

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

    /**
     * Límites de intentos. El tope por IP es alto a propósito: en un colegio o
     * una empresa muchas personas salen a internet por la misma IP. Lo que
     * frena la fuerza bruta es el límite por correo o por cuenta, que un
     * atacante no puede repartir entre IPs.
     */
    private function limitesDeIntentos(): void
    {
        $ip = fn (Request $r) => 'ip:' . $r->ip();
        $correo = fn (Request $r) => 'correo:' . mb_strtolower(trim((string) $r->input('email')));
        $demasiados = fn (string $texto) => fn (Request $r, array $cabeceras) => back()
            ->withInput($r->except(['password', 'password_confirmation', 'code']))
            ->withErrors(['email' => $texto, 'code' => $texto])
            ->withHeaders($cabeceras);

        RateLimiter::for('acceso', fn (Request $r) => [
            Limit::perMinute(5)->by($correo($r) . '|' . $r->ip())->response($demasiados('Demasiados intentos. Espera un minuto antes de volver a intentar.')),
            Limit::perHour(30)->by($correo($r))->response($demasiados('Demasiados intentos con este correo. Espera un rato o recupera tu contraseña.')),
            Limit::perMinute(100)->by($ip($r)),
        ]);

        // Sin tope por hora por IP: un colegio registra salón tras salón desde la
        // misma IP. Lo que frena el abuso del alta es User::REGLAS_NOMBRE.
        RateLimiter::for('registro', fn (Request $r) => [
            Limit::perMinute(5)->by($correo($r)),
            Limit::perMinute(100)->by($ip($r)),
        ]);

        RateLimiter::for('recuperar', fn (Request $r) => [
            Limit::perMinute(3)->by($correo($r))->response($demasiados('Has realizado demasiados intentos. Espera unos minutos.')),
            Limit::perMinute(100)->by($ip($r)),
        ]);

        // Códigos de 6 dígitos (verificación de correo y segundo factor): por
        // cuenta, para que un millón de combinaciones no se pueda recorrer.
        RateLimiter::for('codigo', fn (Request $r) => [
            Limit::perMinute(5)->by('codigo:' . ($r->user()?->id ?? $r->session()->get('dos_pasos.user_id') ?? $r->ip()))
                ->response($demasiados('Demasiados intentos. Espera un minuto y vuelve a escribir el código.')),
            Limit::perMinute(100)->by($ip($r)),
        ]);

        RateLimiter::for('reenviar-codigo', fn (Request $r) => [
            Limit::perMinute(2)->by('reenviar:' . ($r->user()?->id ?? $r->session()->get('dos_pasos.user_id') ?? $r->ip()))
                ->response($demasiados('Espera un minuto antes de pedir otro código.')),
        ]);

        // Formulario público de /contacto: cada solicitud es un correo al buzón
        // corporativo. Cuenta también los envíos con errores, por eso deja margen.
        RateLimiter::for('contacto', fn (Request $r) => [
            Limit::perMinutes(10, 5)->by('contacto:' . $correo($r) . '|' . $r->ip())
                ->response($demasiados('Ya recibimos tus solicitudes. Espera unos minutos antes de enviar otra.')),
            Limit::perHour(20)->by('contacto:' . $ip($r))
                ->response($demasiados('Recibimos muchas solicitudes desde tu red. Inténtalo más tarde o escríbenos por correo.')),
        ]);

        // El límite corre antes de resolver el modelo: la ruta todavía trae el id.
        RateLimiter::for('invitacion', fn (Request $r) => [
            Limit::perMinute(10)->by('invitacion:' . (is_object($m = $r->route('membresia')) ? $m->getKey() : $m) . '|' . $r->ip()),
            Limit::perMinute(100)->by($ip($r)),
        ]);
    }
}
