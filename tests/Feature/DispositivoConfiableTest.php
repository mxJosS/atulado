<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationCodeMail;
use App\Models\DispositivoConfiable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Administración y equipo clínico reciben el código por correo sólo al entrar
 * desde otra IP, otro navegador u otro dispositivo.
 */
class DispositivoConfiableTest extends TestCase
{
    use RefreshDatabase;

    private const CHROME_WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36';
    private const CHROME_WINDOWS_NUEVO = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36';
    private const FIREFOX_WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:140.0) Gecko/20100101 Firefox/140.0';

    private User $clinica;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clinica = User::factory()->create([
            'email' => 'clinica@atulado.mx', 'role' => 'clinico', 'is_clinico_atulado' => true, 'password' => Hash::make('secreto123'),
        ]);
    }

    /** Entra con código desde esa IP y navegador; devuelve el token de la cookie del dispositivo. */
    private function entrarConCodigo(string $ip, string $navegador): string
    {
        Mail::fake();
        $this->intentar($ip, $navegador)->assertRedirect(route('acceso.codigo'));

        $codigo = null;
        Mail::assertSent(EmailVerificationCodeMail::class, function ($m) use (&$codigo) {
            $codigo = $m->code;

            return $m->proposito === 'acceso' && in_array($m->dispositivo, ['Chrome · Windows', 'Firefox · Windows'], true);
        });

        $respuesta = $this->post(route('acceso.codigo.verificar'), ['code' => $codigo]);
        $this->assertAuthenticatedAs($this->clinica);
        $this->post('/logout');

        return $respuesta->getCookie(DispositivoConfiable::COOKIE)->getValue();
    }

    private function intentar(string $ip, string $navegador, ?string $token = null)
    {
        $this->defaultCookies = []; // withCookie() se queda puesta entre peticiones
        $peticion = $this->withServerVariables(['REMOTE_ADDR' => $ip])->withHeader('User-Agent', $navegador);
        if ($token !== null) {
            $peticion = $peticion->withCookie(DispositivoConfiable::COOKIE, $token);
        }

        return $peticion->post('/login', ['email' => 'clinica@atulado.mx', 'password' => 'secreto123']);
    }

    public function test_from_a_confirmed_device_it_enters_without_a_code(): void
    {
        $token = $this->entrarConCodigo('10.0.0.5', self::CHROME_WINDOWS);
        $this->assertDatabaseHas('dispositivos_confiables', ['user_id' => $this->clinica->id, 'ip' => '10.0.0.5', 'navegador' => 'Chrome · Windows']);

        Mail::fake();
        $this->intentar('10.0.0.5', self::CHROME_WINDOWS, $token)->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->clinica);
        Mail::assertNothingSent();
    }

    public function test_a_browser_update_is_still_the_same_browser(): void
    {
        $token = $this->entrarConCodigo('10.0.0.5', self::CHROME_WINDOWS);

        Mail::fake();
        $this->intentar('10.0.0.5', self::CHROME_WINDOWS_NUEVO, $token)->assertRedirect(route('dashboard'));
        Mail::assertNothingSent();
    }

    public function test_another_ip_another_browser_or_another_device_asks_for_the_code(): void
    {
        $token = $this->entrarConCodigo('10.0.0.5', self::CHROME_WINDOWS);

        foreach ([['10.0.0.9', self::CHROME_WINDOWS, $token], ['10.0.0.5', self::FIREFOX_WINDOWS, $token], ['10.0.0.5', self::CHROME_WINDOWS, null]] as [$ip, $navegador, $cookie]) {
            Mail::fake();
            $this->intentar($ip, $navegador, $cookie)->assertRedirect(route('acceso.codigo'));
            $this->assertGuest();
            Mail::assertSent(EmailVerificationCodeMail::class);
            $this->post(route('acceso.codigo.cancelar'));
        }
    }

    public function test_after_90_days_without_use_it_asks_again(): void
    {
        $token = $this->entrarConCodigo('10.0.0.5', self::CHROME_WINDOWS);

        $this->travel(DispositivoConfiable::DIAS_VIGENCIA + 1)->days();
        $this->intentar('10.0.0.5', self::CHROME_WINDOWS, $token)->assertRedirect(route('acceso.codigo'));
        $this->assertGuest();
    }

    public function test_changing_the_password_forgets_every_device(): void
    {
        $token = $this->entrarConCodigo('10.0.0.5', self::CHROME_WINDOWS);

        $this->actingAs($this->clinica)->put(route('profile.password'), [
            'current_password' => 'secreto123', 'password' => 'nueva-clave-99', 'password_confirmation' => 'nueva-clave-99',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('dispositivos_confiables', ['user_id' => $this->clinica->id]);
        $this->post('/logout');

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])->withHeader('User-Agent', self::CHROME_WINDOWS)
            ->withCookie(DispositivoConfiable::COOKIE, $token)
            ->post('/login', ['email' => 'clinica@atulado.mx', 'password' => 'nueva-clave-99'])
            ->assertRedirect(route('acceso.codigo'));
    }

    public function test_the_device_cookie_of_one_account_does_not_work_for_another(): void
    {
        $token = $this->entrarConCodigo('10.0.0.5', self::CHROME_WINDOWS);
        User::factory()->create(['email' => 'admin@atulado.mx', 'is_admin' => true, 'role' => 'admin', 'password' => Hash::make('secreto123')]);

        Mail::fake();
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.5'])->withHeader('User-Agent', self::CHROME_WINDOWS)
            ->withCookie(DispositivoConfiable::COOKIE, $token)
            ->post('/login', ['email' => 'admin@atulado.mx', 'password' => 'secreto123'])
            ->assertRedirect(route('acceso.codigo'));
        Mail::assertSent(EmailVerificationCodeMail::class);
    }

    public function test_accounts_without_second_factor_are_not_affected(): void
    {
        User::factory()->create(['email' => 'ana@correo.mx', 'password' => Hash::make('secreto123')]);

        Mail::fake();
        $this->post('/login', ['email' => 'ana@correo.mx', 'password' => 'secreto123'])->assertRedirect(route('dashboard'));
        Mail::assertNothingSent();
        $this->assertDatabaseCount('dispositivos_confiables', 0);
    }
}
