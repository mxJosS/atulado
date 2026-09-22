<?php

namespace Tests\Feature;

use App\Mail\InvitacionPadronMail;
use App\Models\Institucion;
use App\Models\Membresia;
use App\Models\User;
use App\Services\InvitacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvitacionesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Institucion $institucion;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $this->institucion = Institucion::factory()->create(['nombre_corto' => 'Constructora Maya']);
    }

    private function persona(string $estado = 'invitado', array $extra = []): Membresia
    {
        return Membresia::factory()->create(array_merge(['institucion_id' => $this->institucion->id, 'estado' => $estado], $extra));
    }

    private function enviar(array $datos)
    {
        return $this->actingAs($this->admin)->post(route('admin.instituciones.invitaciones.enviar', $this->institucion), $datos);
    }

    public function test_send_to_everyone_skips_active_and_removed_people(): void
    {
        $pendientes = collect([$this->persona(), $this->persona(), $this->persona('invitado', ['invitado_en' => now()->subDays(3)])]);
        $this->persona('activo');
        $this->persona('baja');

        $this->enviar(['alcance' => 'todos'])
            ->assertRedirect(route('admin.instituciones.show', $this->institucion) . '#tab-invitaciones')
            ->assertSessionHas('success');

        Mail::assertQueuedCount(3);
        $pendientes->each(fn ($m) => $this->assertNotNull($m->fresh()->invitado_en));
    }

    public function test_send_only_to_people_never_invited(): void
    {
        $nueva = $this->persona();
        $this->persona('invitado', ['invitado_en' => now()->subDay()]);

        $this->enviar(['alcance' => 'nuevos']);

        Mail::assertQueuedCount(1);
        Mail::assertQueued(InvitacionPadronMail::class, fn ($mail) => $mail->hasTo($nueva->user->email));
    }

    public function test_send_to_selection_and_to_a_single_person(): void
    {
        [$a, $b, $c] = [$this->persona(), $this->persona(), $this->persona()];

        $this->enviar(['alcance' => 'seleccion', 'personas' => [$a->id, $b->id]]);
        Mail::assertQueuedCount(2);

        // El botón de una fila ignora las casillas marcadas.
        $this->enviar(['alcance' => 'seleccion', 'personas' => [$a->id, $b->id], 'solo' => $c->id]);
        Mail::assertQueuedCount(3);
        Mail::assertQueued(InvitacionPadronMail::class, fn ($mail) => $mail->hasTo($c->user->email));
    }

    public function test_people_of_another_institution_are_never_invited(): void
    {
        $ajena = Membresia::factory()->create(['estado' => 'invitado']);

        $this->enviar(['alcance' => 'seleccion', 'personas' => [$ajena->id]])->assertSessionHas('info');
        Mail::assertNothingQueued();
    }

    public function test_the_invitation_email_has_a_personal_link_and_no_clinical_data(): void
    {
        $m = $this->persona();
        $html = (new InvitacionPadronMail($m, app(InvitacionService::class)->url($m)))->render();

        $this->assertStringContainsString('Constructora Maya', $html);
        $this->assertStringContainsString('/invitacion/' . $m->id, $html);
        $this->assertStringContainsString('confidencial', $html);
    }

    public function test_accepting_sets_the_password_activates_and_the_link_works_once(): void
    {
        $m = $this->persona();
        $url = app(InvitacionService::class)->url($m);

        $this->get($url)->assertOk()->assertSee('Activa tu cuenta')->assertSee($m->user->email);

        $this->post($url, ['password' => 'Clave12345', 'password_confirmation' => 'Clave12345'])
            ->assertSessionHasErrors('acepto');

        $this->post($url, ['password' => 'Clave12345', 'password_confirmation' => 'Clave12345', 'acepto' => '1'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($m->user->fresh());
        $this->assertSame('activo', $m->fresh()->estado);
        $this->assertNotNull($m->fresh()->activado_en);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Clave12345', $m->user->fresh()->password));

        auth()->logout();
        $this->get($url)->assertOk()->assertSee('ya no es válida');
    }

    public function test_tampered_or_expired_links_are_rejected(): void
    {
        $m = $this->persona();
        $url = app(InvitacionService::class)->url($m);

        $this->get($url . 'x')->assertForbidden();

        $this->travel(InvitacionService::DIAS_VIGENCIA + 1)->days();
        $this->get($url)->assertForbidden();
    }
}
