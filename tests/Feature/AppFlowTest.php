<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\CrisisLine;
use App\Models\MoodLog;
use App\Models\Resource;
use App\Models\SafetyPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_load_successfully(): void
    {
        // Seed a sample resource and article
        Resource::create([
            'title' => 'Respiración 4-7-8',
            'slug' => 'respiracion-478',
            'category' => 'tip',
            'summary' => 'Ejercicio de respiración',
            'content' => 'Contenido de prueba',
            'is_featured' => true,
        ]);

        Article::create([
            'title' => 'Salud Mental Hoy',
            'slug' => 'salud-mental-hoy',
            'category' => 'Ansiedad',
            'excerpt' => 'Extracto de prueba',
            'content' => 'Contenido completo',
            'published_at' => Carbon::now(),
        ]);

        CrisisLine::create([
            'country' => 'México',
            'country_code' => 'MX',
            'phone_number' => '800 290 0024',
            'service_name' => 'Línea de la Vida',
            'description' => 'Servicio gratuito',
            'is_featured' => true,
        ]);

        // Home
        $this->get('/')->assertStatus(200)->assertSee('a tu lado');

        // Recursos index & show
        $this->get('/recursos')->assertStatus(200)->assertSee('Respiración 4-7-8');
        $this->get('/recursos/respiracion-478')->assertStatus(200)->assertSee('Respiración 4-7-8');

        // Revista index & show
        $this->get('/revista')->assertStatus(200)->assertSee('Salud Mental Hoy');
        $this->get('/revista/salud-mental-hoy')->assertStatus(200)->assertSee('Salud Mental Hoy');

        // Interactive tools
        $this->get('/sientes')->assertStatus(200)->assertSee('¿Cómo te sientes');
        $this->get('/herramientas/respiracion')->assertStatus(200)->assertSee('Respira conmigo');
        $this->get('/herramientas/grounding')->assertStatus(200)->assertSee('5-4-3-2-1');
        $this->get('/herramientas/stop')->assertStatus(200)->assertSee('Técnica STOP');
        $this->get('/crisis')->assertStatus(200)->assertSee('Línea de la Vida');
    }

    public function test_user_can_register_and_log_in(): void
    {
        $response = $this->post('/registro', [
            'name' => 'Test User',
            'email' => 'test@atulado.com.mx',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'avatar_color' => 'sage',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'test@atulado.com.mx']);
    }

    public function test_dashboard_is_protected_for_guests(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_record_mood_checkin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/mood/checkin', [
            'score' => 5,
            'primary_emotion' => 'Esperanza',
            'journal_entry' => 'Hoy me sentí muy bien.',
            'gratitude_note' => 'El día soleado.',
            'energy_level' => 4,
            'sleep_hours' => 8,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('mood_logs', [
            'user_id' => $user->id,
            'score' => 5,
            'primary_emotion' => 'Esperanza',
        ]);
    }

    public function test_user_can_save_safety_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/plan-de-seguridad', [
            'warning_signs' => ['Pensamientos negativos', 'Insomnio'],
            'internal_coping' => ['Respiración 4-7-8'],
            'distraction_activities' => ['Pasear en el parque'],
            'trusted_contacts' => [
                ['name' => 'Hermano', 'phone' => '55 1234 5678', 'relationship' => 'Hermano']
            ],
            'reasons_for_living' => 'Mi familia y mis proyectos',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('safety_plans', [
            'user_id' => $user->id,
            'reasons_for_living' => 'Mi familia y mis proyectos',
        ]);
    }
}
