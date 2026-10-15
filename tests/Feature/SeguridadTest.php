<?php

namespace Tests\Feature;

use App\Mail\EmailVerificationCodeMail;
use App\Models\Ajuste;
use App\Models\EventoCrisis;
use App\Models\Institucion;
use App\Models\MoodLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as UsuarioGoogle;
use Mockery;
use Tests\TestCase;

/**
 * Hallazgos de la revisión de seguridad: subida de archivos, límites de
 * intentos, Google, IDOR, segundo factor, cabeceras y cifrado.
 */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    /* ─────────── Registro como relevo de phishing ─────────── */

    public function test_registration_rejects_names_that_carry_links_or_spam(): void
    {
        Mail::fake();
        $datos = fn (string $nombre, string $correo) => [
            'name' => $nombre, 'email' => $correo,
            'password' => 'secreta123', 'password_confirmation' => 'secreta123',
            'acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1',
        ];

        foreach (['Gana $5000 en https://estafa.mx', 'Visita estafa.link', 'Premio 2026'] as $i => $nombre) {
            $this->post('/registro', $datos($nombre, "victima{$i}@ejemplo.com"))->assertSessionHasErrors('name');
        }
        Mail::assertNothingSent();

        $this->post('/registro', $datos("María Ma. José O'Neil-Pérez", 'real@ejemplo.com'))->assertSessionHasNoErrors();
        Mail::assertSent(EmailVerificationCodeMail::class);
    }

    public function test_a_whole_classroom_can_register_from_the_same_school_ip(): void
    {
        Mail::fake();

        foreach (range(1, 60) as $i) {
            auth()->logout();
            $this->withServerVariables(['REMOTE_ADDR' => '200.10.20.30'])->post('/registro', [
                'name' => 'Alumno Prueba', 'email' => "alumno{$i}@escuela.mx",
                'password' => 'secreta123', 'password_confirmation' => 'secreta123',
                'acepto_aviso' => '1', 'acepto_sensibles' => '1', 'mayor_de_edad' => '1',
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(60, User::where('email', 'like', '%@escuela.mx')->count());
    }

    /* ─────────── Subida de archivos ─────────── */

    public function test_a_cover_image_keeps_the_extension_of_its_real_content(): void
    {
        Storage::fake('public');
        $profesional = User::factory()->create(['role' => 'profesional']);
        $tema = \App\Models\TopicArea::create(['name' => 'Sueño', 'slug' => 'sueno']);

        // Una imagen PNG real con nombre «.html»: guardada así, el servidor la
        // entregaría como página del mismo dominio (XSS almacenado).
        $png = UploadedFile::fake()->image('portada.png', 40, 40); // se conserva: al destruirse borra el archivo temporal
        $imagen = new UploadedFile($png->getPathname(), 'portada.html', 'image/png', null, true);

        $this->actingAs($profesional)->post(route('revista.store'), [
            'title' => 'Dormir mejor', 'topic_area_id' => $tema->id, 'author_name' => 'Psic. Ana',
            'author_credentials' => 'Cédula 123', 'publication_type' => 'divulgacion', 'target_audience' => 'general',
            'summary' => 'Resumen breve', 'content' => str_repeat('Contenido de prueba. ', 40), 'cover_image' => $imagen,
            'is_disclaimer_accepted' => '1',
        ])->assertSessionHasNoErrors();

        $archivos = Storage::disk('public')->allFiles('articles');
        $this->assertCount(1, $archivos);
        $this->assertStringEndsWith('.png', $archivos[0]);
    }

    /* ─────────── Límites de intentos y proxies ─────────── */

    public function test_a_forged_x_forwarded_for_does_not_bypass_the_login_limit(): void
    {
        User::factory()->create(['email' => 'luis@utm.mx']);

        for ($i = 0; $i < 5; $i++) {
            $this->withHeader('X-Forwarded-For', "10.0.0.{$i}")
                ->post('/login', ['email' => 'luis@utm.mx', 'password' => 'incorrecta'])
                ->assertSessionHasErrors('email');
        }

        // Sexto intento con otra IP «inventada»: sigue bloqueado.
        $this->withHeader('X-Forwarded-For', '10.0.0.99')
            ->post('/login', ['email' => 'luis@utm.mx', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Demasiados intentos. Espera un minuto antes de volver a intentar.']);
        $this->assertGuest();
    }

    public function test_forgot_password_answers_the_same_whether_the_account_exists_or_not(): void
    {
        User::factory()->create(['email' => 'existe@prueba.mx']);

        $existe = $this->post(route('password.email'), ['email' => 'existe@prueba.mx'])->assertSessionHas('status')->getSession()->get('status');
        $noExiste = $this->post(route('password.email'), ['email' => 'nadie@prueba.mx'])->assertSessionHasNoErrors()->getSession()->get('status');

        $this->assertSame($existe, $noExiste);
    }

    /* ─────────── Google ─────────── */

    private function googleDevuelve(array $datos): void
    {
        Ajuste::guardar(Ajuste::GOOGLE_HABILITADO, '1');
        $usuario = (new UsuarioGoogle())->setRaw($datos)->map([
            'id' => $datos['sub'] ?? '123', 'name' => $datos['name'] ?? 'Ana', 'email' => $datos['email'], 'avatar' => null,
        ]);
        $proveedor = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $proveedor->shouldReceive('enablePKCE')->andReturnSelf();
        $proveedor->shouldReceive('user')->andReturn($usuario);
        Socialite::shouldReceive('driver')->with('google')->andReturn($proveedor);
    }

    public function test_google_login_requires_the_state_to_match(): void
    {
        Ajuste::guardar(Ajuste::GOOGLE_HABILITADO, '1');
        $proveedor = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $proveedor->shouldReceive('enablePKCE')->andReturnSelf();
        $proveedor->shouldReceive('user')->andThrow(new InvalidStateException());
        Socialite::shouldReceive('driver')->with('google')->andReturn($proveedor);

        $this->get(route('auth.google.callback', ['state' => 'ajeno', 'code' => 'x']))
            ->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_google_rejects_unverified_emails_and_staff_accounts(): void
    {
        User::factory()->create(['email' => 'admin@atulado.com.mx', 'is_admin' => true, 'role' => 'admin']);

        $this->googleDevuelve(['email' => 'nuevo@gmail.com', 'email_verified' => false]);
        $this->get(route('auth.google.callback'))->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => 'nuevo@gmail.com']);

        $this->googleDevuelve(['email' => 'admin@atulado.com.mx', 'email_verified' => true]);
        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_a_new_google_account_must_give_consent_before_using_the_app(): void
    {
        $this->googleDevuelve(['email' => 'nuevo@gmail.com', 'email_verified' => true]);

        $this->get(route('auth.google.callback'))->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertRedirect(route('consentimiento.show'));
    }

    /* ─────────── IDOR al cerrar casos ─────────── */

    public function test_a_clinician_cannot_close_a_case_of_an_institution_not_assigned(): void
    {
        $propia = Institucion::factory()->create();
        $ajena = Institucion::factory()->create();
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);
        $clinico->institucionesAsignadas()->attach($propia);
        $persona = User::factory()->create();
        $caso = EventoCrisis::create([
            'user_id' => $persona->id, 'institucion_id' => $ajena->id, 'nivel' => 'ROJO', 'disparado_en' => now()->subHour(),
            'contactado_en' => now()->subMinutes(30), 'estado' => 'en_atencion',
        ]);

        $this->actingAs($clinico)->postJson(route('apoyo.cerrar', $caso), ['notas_cierre' => 'Cierre sin permiso sobre la institución.'])
            ->assertNotFound();
        $this->assertNotSame('cerrado', $caso->fresh()->estado);
    }

    /* ─────────── Segundo factor ─────────── */

    public function test_a_clinician_needs_the_emailed_code_and_five_mistakes_cancel_the_attempt(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'clinica@atulado.mx', 'role' => 'clinico', 'is_clinico_atulado' => true, 'password' => Hash::make('secreto123')]);

        $this->post('/login', ['email' => 'clinica@atulado.mx', 'password' => 'secreto123'])->assertRedirect(route('acceso.codigo'));
        $this->assertGuest();
        Mail::assertSent(EmailVerificationCodeMail::class, fn ($m) => $m->proposito === 'acceso');

        for ($i = 1; $i <= 4; $i++) {
            $this->post(route('acceso.codigo.verificar'), ['code' => '000000'])->assertSessionHasErrors('code');
        }
        $this->post(route('acceso.codigo.verificar'), ['code' => '000000'])->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNull(User::where('email', 'clinica@atulado.mx')->value('dos_pasos_codigo'));

        // El código viejo ya no sirve aunque fuera el correcto.
        $this->get(route('acceso.codigo'))->assertRedirect(route('login'));
    }

    public function test_the_access_code_is_stored_hashed_and_expires(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['email' => 'admin@atulado.mx', 'is_admin' => true, 'role' => 'admin', 'password' => Hash::make('secreto123')]);
        $this->post('/login', ['email' => 'admin@atulado.mx', 'password' => 'secreto123']);

        $codigo = null;
        Mail::assertSent(EmailVerificationCodeMail::class, function ($m) use (&$codigo) {
            $codigo = $m->code;

            return true;
        });
        $this->assertNotSame($codigo, $admin->fresh()->dos_pasos_codigo);

        $this->travel(11)->minutes();
        $this->post(route('acceso.codigo.verificar'), ['code' => $codigo])->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /* ─────────── Cabeceras y cifrado ─────────── */

    public function test_security_headers_are_sent(): void
    {
        $this->get('/')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_the_migration_encrypts_existing_plain_text_once(): void
    {
        $user = User::factory()->create();
        DB::table('mood_logs')->insert([
            'user_id' => $user->id, 'score' => 3, 'primary_emotion' => 'Calma', 'logged_date' => today(),
            'journal_entry' => 'Texto de antes del cifrado', 'terminos_detectados' => json_encode(['uno']),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('contactos_emergencia')->insert([
            'user_id' => $user->id, 'nombre' => 'Mamá', 'telefono' => '9991234567', 'relacion' => 'Madre',
            'es_principal' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $migracion = require database_path('migrations/2026_09_26_000007_cifrar_datos_sensibles.php');
        $migracion->up();
        $migracion->up(); // correrla dos veces no cifra doble

        $this->assertStringNotContainsString('antes del cifrado', DB::table('mood_logs')->value('journal_entry'));
        $log = MoodLog::first();
        $this->assertSame('Texto de antes del cifrado', $log->journal_entry);
        $this->assertSame(['uno'], $log->terminos_detectados);
        $this->assertSame('9991234567', \App\Models\ContactoEmergencia::first()->telefono);
    }

    public function test_the_daily_journal_is_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/mood/checkin', [
            'score' => 3, 'primary_emotion' => 'Calma', 'journal_entry' => 'Hoy hablé con mi hermana.',
        ]);

        $crudo = DB::table('mood_logs')->where('user_id', $user->id)->value('journal_entry');
        $this->assertNotNull($crudo);
        $this->assertStringNotContainsString('hermana', $crudo);
        $this->assertSame('Hoy hablé con mi hermana.', MoodLog::where('user_id', $user->id)->first()->journal_entry);
    }
}
