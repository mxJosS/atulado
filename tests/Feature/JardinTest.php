<?php

namespace Tests\Feature;

use App\Models\Jardin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El juego del árbol se guarda en la cuenta (antes sólo en el navegador), con
 * nivel máximo 50, y las cuentas principales tienen botones de +1 / −1.
 */
class JardinTest extends TestCase
{
    use RefreshDatabase;

    private function estado(array $cambios = []): array
    {
        $plantas = collect(Jardin::PLANTAS)->mapWithKeys(fn ($p) => [$p => ['nivel' => 1, 'xp' => 0]])->all();

        return array_replace_recursive([
            'plantas' => $plantas,
            'soles' => 125,
            'tareas_fecha' => now()->toDateString(),
            'tareas_hechas' => 2,
            'inventario' => [
                'escudo' => false,
                'plantas' => ['tree'],
                'planta_activa' => 'tree',
                'macetas' => ['terracotta'],
                'maceta_activa' => 'terracotta',
            ],
        ], $cambios);
    }

    public function test_the_garden_is_saved_in_the_account_and_comes_back_on_every_page(): void
    {
        $ana = User::factory()->create();

        $this->actingAs($ana)->postJson(route('jardin.guardar'), $this->estado(['plantas' => ['tree' => ['nivel' => 7, 'xp' => 30]]]))
            ->assertNoContent();

        $this->assertSame(7, $ana->fresh()->jardin->nivelArbol());
        $this->get(route('dashboard'))->assertOk()
            ->assertSee('window.ATULADO_JARDIN = ', false)
            ->assertSee('"nivel":7', false)
            ->assertSee('Nvl 7');
    }

    public function test_levels_above_50_and_unknown_things_are_rejected(): void
    {
        $ana = User::factory()->create();

        $this->actingAs($ana)->postJson(route('jardin.guardar'), $this->estado(['plantas' => ['tree' => ['nivel' => 51]]]))
            ->assertStatus(422)->assertJsonValidationErrors('plantas.tree.nivel');
        $this->actingAs($ana)->postJson(route('jardin.guardar'), $this->estado(['inventario' => ['planta_activa' => 'dragon']]))
            ->assertStatus(422);
        $this->assertDatabaseCount('jardines', 0);
    }

    public function test_each_person_only_writes_their_own_garden(): void
    {
        $ana = User::factory()->create();
        $beto = User::factory()->create();

        $this->actingAs($ana)->postJson(route('jardin.guardar'), $this->estado() + ['user_id' => $beto->id])->assertNoContent();

        $this->assertNotNull($ana->fresh()->jardin);
        $this->assertNull($beto->fresh()->jardin);
    }

    public function test_guests_cannot_save(): void
    {
        $this->postJson(route('jardin.guardar'), $this->estado())->assertUnauthorized();
    }

    public function test_the_level_buttons_are_only_for_main_accounts_also_while_entering_as_someone(): void
    {
        config(['atulado.superadmin_emails' => ['principal@atulado.com.mx']]);
        $principal = User::factory()->create(['email' => 'principal@atulado.com.mx', 'is_admin' => true, 'role' => 'admin']);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $persona = User::factory()->create();

        $this->actingAs($principal)->get(route('dashboard'))->assertOk()->assertSee('ajustarNivelPlanta(1)', false);
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertDontSee('ajustarNivelPlanta(1)', false);
        $this->actingAs($persona)->get(route('dashboard'))->assertOk()->assertDontSee('ajustarNivelPlanta(1)', false);

        $this->actingAs($principal)->post(route('admin.users.entrar', $persona));
        $this->get(route('dashboard'))->assertOk()->assertSee('ajustarNivelPlanta(1)', false);
    }
}
