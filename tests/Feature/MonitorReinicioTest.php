<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Botones «Reiniciar servicios» y «Reiniciar servidor» del monitor.
 */
class MonitorReinicioTest extends TestCase
{
    use RefreshDatabase;

    private string $ruta;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ruta = '/' . config('atulado.monitor.ruta') . '/reiniciar'; // en pruebas: /monitor-de-prueba (phpunit.xml)
    }

    public function test_an_admin_restarts_services_or_the_whole_machine_through_the_sudo_script(): void
    {
        Process::fake();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)->postJson($this->ruta, ['tipo' => 'servicios', 'password' => 'password', 'confirmacion' => 'REINICIAR'])
            ->assertOk()->assertJson(['ok' => true, 'tipo' => 'servicios']);
        $this->actingAs($admin)->postJson($this->ruta, ['tipo' => 'maquina', 'password' => 'password', 'confirmacion' => 'REINICIAR'])
            ->assertOk()->assertJson(['ok' => true, 'tipo' => 'maquina']);

        $script = config('atulado.monitor.reinicio');
        Process::assertRan(fn (PendingProcess $p) => $p->command === ['sudo', '-n', $script, 'servicios']);
        Process::assertRan(fn (PendingProcess $p) => $p->command === ['sudo', '-n', $script, 'maquina']);
        Process::assertRanTimes(fn () => true, 2);
    }

    public function test_it_asks_for_the_password_and_the_written_confirmation(): void
    {
        Process::fake();
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)->postJson($this->ruta, ['tipo' => 'maquina', 'password' => 'otra', 'confirmacion' => 'REINICIAR'])
            ->assertStatus(422)->assertJson(['ok' => false, 'mensaje' => 'La contraseña no es correcta.']);
        $this->actingAs($admin)->postJson($this->ruta, ['tipo' => 'maquina', 'password' => 'password', 'confirmacion' => 'reiniciar'])
            ->assertStatus(422);
        $this->actingAs($admin)->postJson($this->ruta, ['tipo' => 'apagar', 'password' => 'password', 'confirmacion' => 'REINICIAR'])
            ->assertStatus(422);

        Process::assertNothingRan();
    }

    public function test_it_reports_when_the_server_does_not_allow_the_restart(): void
    {
        Process::fake(['*' => Process::result(errorOutput: 'sudo: a password is required', exitCode: 1)]);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)->postJson($this->ruta, ['tipo' => 'servicios', 'password' => 'password', 'confirmacion' => 'REINICIAR'])
            ->assertStatus(500)->assertJson(['ok' => false]);
    }

    public function test_nobody_else_can_restart_anything(): void
    {
        Process::fake();
        $usuario = User::factory()->create();
        $clinico = User::factory()->create(['role' => 'clinico', 'is_clinico_atulado' => true]);

        $this->postJson($this->ruta, ['tipo' => 'maquina'])->assertUnauthorized();
        $this->actingAs($usuario)->postJson($this->ruta, ['tipo' => 'maquina', 'password' => 'password', 'confirmacion' => 'REINICIAR'])->assertNotFound();
        $this->actingAs($clinico)->postJson($this->ruta, ['tipo' => 'maquina', 'password' => 'password', 'confirmacion' => 'REINICIAR'])->assertNotFound();

        Process::assertNothingRan();
    }

    public function test_the_monitor_shows_both_restart_buttons(): void
    {
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response('', 404)]);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)->get('/' . config('atulado.monitor.ruta'))->assertOk()
            ->assertSee('Reiniciar servicios')
            ->assertSee('Reiniciar servidor')
            ->assertSee('csrf-token', false);
    }
}
