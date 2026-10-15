<?php

namespace Tests\Feature;

use App\Mail\ContactoCorporativoMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * «Contacto»: empresas e instituciones que piden una prueba piloto o un plan.
 * Las solicitudes llegan por correo al buzón corporativo, con «Responder a»
 * de quien las envía.
 */
class ContactoTest extends TestCase
{
    use RefreshDatabase;

    private function solicitud(array $cambios = []): array
    {
        return array_merge([
            'interes' => 'piloto',
            'nombre' => 'Ana Uc',
            'puesto' => 'Directora de Recursos Humanos',
            'organizacion' => 'Constructora del Sureste',
            'sector' => 'Construcción y Obras',
            'personas' => '51 a 250',
            'email' => 'Ana@Constructora.mx',
            'telefono' => '+52 999 123 4567',
            'mensaje' => "Queremos empezar con la cuadrilla de Mérida.\nSomos 120 personas.",
            'acepto_aviso' => '1',
            'cargado' => encrypt(now()->subSeconds(30)->timestamp),
        ], $cambios);
    }

    public function test_the_contact_page_shows_the_form_and_the_corporate_email(): void
    {
        $this->get('/contacto')->assertOk()
            ->assertSee('Lleva A Tu Lado a tu')
            ->assertSee('Corporativo@atulado.com.mx')
            ->assertSee('name="interes" value="piloto" checked', false)
            ->assertSee('name="acepto_aviso"', false)
            ->assertSee('name="sitio_web"', false);
    }

    public function test_a_link_can_preselect_the_interest(): void
    {
        $this->get('/contacto?interes=plan')->assertOk()
            ->assertSee('name="interes" value="plan" checked', false);

        $this->get('/contacto?interes=otra-cosa')->assertOk()
            ->assertSee('name="interes" value="piloto" checked', false);
    }

    public function test_home_menu_footer_and_legal_pages_lead_to_contact(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Para empresas e instituciones')
            ->assertSee(route('contacto', ['interes' => 'piloto']) . '#formulario', false)
            ->assertSee(route('contacto', ['interes' => 'plan']) . '#formulario', false)
            ->assertSee('mailto:Corporativo@atulado.com.mx', false);

        $this->get('/crisis')->assertOk()->assertSee('href="' . route('contacto') . '"', false);
        $this->get('/privacidad')->assertOk()->assertSee('href="' . route('contacto') . '"', false);
        $this->get('/terminos')->assertOk()->assertSee('href="' . route('contacto') . '"', false);
    }

    public function test_a_valid_request_is_emailed_to_the_corporate_inbox(): void
    {
        Mail::fake();

        $this->post('/contacto', $this->solicitud())
            ->assertRedirect(route('contacto'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('contacto_enviado')
            ->assertSessionHas('success');

        Mail::assertSent(ContactoCorporativoMail::class, function (ContactoCorporativoMail $mail) {
            $sobre = $mail->envelope();

            return $mail->hasTo('Corporativo@atulado.com.mx')
                && $sobre->hasReplyTo('ana@constructora.mx', 'Ana Uc')
                && $sobre->hasSubject('Contacto corporativo · Prueba piloto de 90 días · Constructora del Sureste')
                && $mail->datos['sector'] === 'Construcción y Obras'
                && $mail->datos['personas'] === '51 a 250';
        });
    }

    public function test_after_sending_the_page_thanks_instead_of_showing_the_form(): void
    {
        Mail::fake();

        $this->followingRedirects()->post('/contacto', $this->solicitud())
            ->assertOk()
            ->assertSee('Recibimos tu solicitud')
            ->assertDontSee('name="nombre"', false);
    }

    public function test_a_request_without_the_required_fields_is_not_sent(): void
    {
        Mail::fake();

        $this->from('/contacto')->post('/contacto', ['interes' => 'otro'])
            ->assertRedirect('/contacto')
            ->assertSessionHasErrors(['nombre', 'organizacion', 'email', 'acepto_aviso']);

        $this->post('/contacto', $this->solicitud(['email' => 'no-es-correo', 'interes' => 'vacaciones', 'acepto_aviso' => null]))
            ->assertSessionHasErrors(['email', 'interes', 'acepto_aviso']);

        Mail::assertNothingSent();
    }

    public function test_validation_messages_are_in_spanish(): void
    {
        $this->post('/contacto', $this->solicitud(['nombre' => '']))
            ->assertSessionHasErrors(['nombre' => 'El campo nombre es obligatorio.']);

        $this->post('/contacto', $this->solicitud(['organizacion' => str_repeat('a', 161)]))
            ->assertSessionHasErrors(['organizacion' => 'El campo organización admite como máximo 160 caracteres.']);

        $this->post('/contacto', $this->solicitud(['sector' => 'Minería espacial']))
            ->assertSessionHasErrors(['sector' => 'Elige una opción válida de sector.']);

        $this->post('/contacto', $this->solicitud(['acepto_aviso' => null]))
            ->assertSessionHasErrors(['acepto_aviso' => 'Para enviar tu solicitud, acepta el aviso de privacidad.']);
    }

    public function test_a_bot_that_fills_the_hidden_field_gets_no_email_sent(): void
    {
        Mail::fake();

        $this->post('/contacto', $this->solicitud(['sitio_web' => 'https://spam.example']))
            ->assertRedirect(route('contacto'))
            ->assertSessionHas('contacto_enviado');

        Mail::assertNothingSent();
    }

    public function test_a_form_sent_instantly_or_without_its_timestamp_gets_no_email_sent(): void
    {
        Mail::fake();

        foreach ([encrypt(now()->timestamp), 'manipulado', null] as $cargado) {
            $this->post('/contacto', $this->solicitud(['cargado' => $cargado]))
                ->assertRedirect(route('contacto'))
                ->assertSessionHas('contacto_enviado');
        }

        Mail::assertNothingSent();
    }

    public function test_text_in_other_alphabets_or_with_links_is_rejected(): void
    {
        Mail::fake();

        foreach ([
            ['nombre' => 'Ayşe Yılmaz'],
            ['organizacion' => 'Şirket Güvenliği'],
            ['nombre' => 'Иван Петров'],
            ['mensaje' => 'Revisa tu factura en https://pago-seguro.example'],
            ['mensaje' => 'Confirma tus datos en www.banco-falso.mx'],
            ['organizacion' => 'Premios estafa.link'],
        ] as $i => $cambio) {
            $this->post('/contacto', $this->solicitud($cambio + ['email' => "caso{$i}@empresa.mx"]))->assertSessionHasErrors(array_keys($cambio));
        }
        Mail::assertNothingSent();

        $this->post('/contacto', $this->solicitud(['nombre' => "José Ñúñez O'Brien", 'organizacion' => 'Grupo Peñón S.A. de C.V.']))
            ->assertSessionHasNoErrors();
        Mail::assertSentCount(1);
    }

    public function test_the_contact_form_is_rate_limited(): void
    {
        Mail::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/contacto', $this->solicitud())->assertSessionHasNoErrors();
        }

        $this->post('/contacto', $this->solicitud())
            ->assertSessionHasErrors(['email' => 'Ya recibimos tus solicitudes. Espera unos minutos antes de enviar otra.']);

        Mail::assertSentCount(5);
    }

    public function test_the_email_renders_in_html_and_text(): void
    {
        $mail = new ContactoCorporativoMail([
            'interes' => 'plan',
            'interes_texto' => 'Contratar un plan',
            'nombre' => 'Luis Pech',
            'puesto' => null,
            'organizacion' => 'Colegio Peninsular',
            'sector' => 'Educación y Colegios',
            'personas' => '251 a 1,000',
            'email' => 'luis@colegio.mx',
            'telefono' => null,
            'mensaje' => "Primera línea\nSegunda línea",
        ]);

        $html = $mail->render();
        $this->assertStringContainsString('Colegio Peninsular', $html);
        $this->assertStringContainsString('Contratar un plan', $html);
        $this->assertStringContainsString('mailto:luis@colegio.mx', $html);
        $this->assertStringContainsString('Primera línea<br />', $html);
        $this->assertStringNotContainsString('Puesto', $html);

        $texto = view('emails.contacto-corporativo-text', ['datos' => $mail->datos])->render();
        $this->assertStringContainsString('Organización: Colegio Peninsular', $texto);
        $this->assertStringContainsString('Personas: 251 a 1,000', $texto);
        $this->assertStringNotContainsString('Puesto:', $texto);
    }
}
