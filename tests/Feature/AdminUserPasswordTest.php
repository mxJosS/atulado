<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Editar a una persona en «Directorio de usuarios» no toca su contraseña,
 * salvo que se marque «Cambiar la contraseña».
 */
class AdminUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function datos(User $user, array $extra = []): array
    {
        return array_merge([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'usuario',
            'status' => 'activo',
        ], $extra);
    }

    public function test_changing_role_or_institution_keeps_the_current_password_even_if_one_arrives(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $persona = User::factory()->create(['password' => Hash::make('mi-clave-propia')]);
        $institucion = Institucion::factory()->create();

        // El navegador rellenaba el campo con la contraseña guardada del administrador.
        $this->actingAs($admin)->put(route('admin.users.update', $persona), $this->datos($persona, [
            'institucion_id' => $institucion->id,
            'password' => 'clave-del-administrador',
        ]))->assertRedirect(route('admin.users.index'));

        $this->actingAs($admin)->put(route('admin.users.update', $persona), $this->datos($persona, [
            'role' => 'admin',
            'password' => 'clave-del-administrador',
            'cambiar_password' => '0',
        ]))->assertRedirect(route('admin.users.index'));

        $persona->refresh();
        $this->assertTrue($persona->is_admin);
        $this->assertTrue(Hash::check('mi-clave-propia', $persona->password));
        $this->assertDatabaseHas('membresias', ['user_id' => $persona->id, 'institucion_id' => $institucion->id]);
    }

    public function test_the_password_changes_only_when_asked(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);
        $persona = User::factory()->create(['password' => Hash::make('mi-clave-propia')]);

        $this->actingAs($admin)->put(route('admin.users.update', $persona), $this->datos($persona, [
            'cambiar_password' => '1',
        ]))->assertSessionHasErrors('password');

        $this->actingAs($admin)->put(route('admin.users.update', $persona), $this->datos($persona, [
            'cambiar_password' => '1',
            'password' => 'nueva-clave',
        ]))->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('nueva-clave', $persona->fresh()->password));
    }

    public function test_the_edit_form_keeps_the_password_field_disabled_until_asked(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()
            ->assertSee('name="cambiar_password"', false)
            ->assertSee('id="editUserPassword" placeholder="Nueva contraseña" class="form-control" style="border-radius: 9px;" autocomplete="new-password" disabled', false);
    }
}
