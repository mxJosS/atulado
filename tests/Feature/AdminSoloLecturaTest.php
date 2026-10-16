<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * «Sólo lectura» para administradores: sólo la cuenta principal la marca.
 * Por ahora se guarda; el bloqueo de acciones llega después.
 */
class AdminSoloLecturaTest extends TestCase
{
    use RefreshDatabase;

    private function datos(User $user, array $extra = []): array
    {
        return array_merge(['name' => $user->name, 'email' => $user->email, 'role' => 'admin', 'status' => 'activo'], $extra);
    }

    public function test_only_the_main_account_can_mark_an_admin_as_read_only(): void
    {
        config(['atulado.superadmin_emails' => ['principal@atulado.com.mx']]);
        $principal = User::factory()->create(['email' => 'principal@atulado.com.mx', 'is_admin' => true, 'role' => 'admin']);
        $otroAdmin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($otroAdmin)->put(route('admin.users.update', $admin), $this->datos($admin, ['admin_solo_lectura' => '1']));
        $this->assertFalse($admin->fresh()->admin_solo_lectura);

        $this->actingAs($principal)->put(route('admin.users.update', $admin), $this->datos($admin, ['admin_solo_lectura' => '1']))
            ->assertRedirect(route('admin.users.index'));
        $this->assertTrue($admin->fresh()->admin_solo_lectura);
        $this->assertDatabaseHas('bitacora_admin', ['accion' => 'usuario_permisos']);

        // Al dejar de ser administrador, la marca se quita.
        $this->actingAs($principal)->put(route('admin.users.update', $admin), $this->datos($admin, ['role' => 'usuario', 'admin_solo_lectura' => '1']));
        $this->assertFalse($admin->fresh()->admin_solo_lectura);
    }
}
