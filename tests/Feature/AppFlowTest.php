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

        $topicArea = \App\Models\TopicArea::create([
            'name' => 'Ansiedad & Pánico',
            'slug' => 'ansiedad-panico',
            'description' => 'Área de ansiedad y pánico',
            'icon' => 'fa-wind',
            'color' => 'sky',
        ]);

        Article::create([
            'topic_area_id' => $topicArea->id,
            'title' => 'Salud Mental Hoy',
            'slug' => 'salud-mental-hoy',
            'author_name' => 'Dr. Carlos Mendoza',
            'author_credentials' => 'Psiquiatra y Terapeuta DBT',
            'visual_theme' => 'sky',
            'publication_type' => 'revision',
            'target_audience' => 'general',
            'summary' => 'Extracto de prueba',
            'content' => 'Contenido completo de prueba con más de cien caracteres para superar las validaciones de publicación.',
            'references' => 'APA Reference 2026',
            'discussion_prompt' => '¿Cómo manejas tus momentos de crisis?',
            'reading_time_min' => 4,
            'allow_comments' => true,
            'is_disclaimer_accepted' => true,
            'status' => 'published',
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

        // Test Print View
        $this->actingAs($user)->get('/plan-de-seguridad/imprimir')
            ->assertStatus(200)
            ->assertSee('Plan de Seguridad Personal')
            ->assertSee('LÍNEAS DE CRISIS Y AYUDA INMEDIATA');
    }

    public function test_home_has_marquee_and_login_has_google_button(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('100% CONFIDENCIAL')
            ->assertSee('BASADO EN DBT');

        $this->get('/login')
            ->assertStatus(200)
            ->assertSee('Acceder con Google')
            ->assertSee('demo@atulado.com.mx');
    }

    public function test_user_can_publish_scientific_article(): void
    {
        $user = User::factory()->create([
            'role' => 'profesional',
            'professional_title' => 'Dra. en Neurociencias',
        ]);

        $topicArea = \App\Models\TopicArea::create([
            'name' => 'Terapia DBT & Conductual',
            'slug' => 'terapia-dbt-conductual',
            'icon' => 'fa-brain',
            'color' => 'lav',
        ]);

        $this->actingAs($user)->get('/revista/crear')
            ->assertStatus(200)
            ->assertSee('Publicar Artículo o Investigación');

        $response = $this->actingAs($user)->post('/revista', [
            'topic_area_id' => $topicArea->id,
            'title' => 'Neurobiología del Apego y Regulación DBT',
            'author_name' => 'Dra. Laura Sánchez',
            'author_credentials' => 'Especialista en DBT y Neurociencias',
            'visual_theme' => 'lav',
            'publication_type' => 'revision',
            'target_audience' => 'profesionales',
            'summary' => 'Análisis de los circuitos de regulación en momentos de activación simpática.',
            'content' => 'La terapia dialéctico conductual combina la aceptación radical con el cambio conductual activo en personas con desregulación emocional severa, permitiendo cultivar una vida que valga la pena ser vivida.',
            'references' => 'Linehan, M. M. (1993). Cognitive-Behavioral Treatment of Borderline Personality Disorder.',
            'discussion_prompt' => '¿Cómo influye la relación terapéutica en el apego seguro?',
            'reading_time_min' => 5,
            'allow_comments' => 1,
            'is_disclaimer_accepted' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('articles', [
            'topic_area_id' => $topicArea->id,
            'title' => 'Neurobiología del Apego y Regulación DBT',
            'author_name' => 'Dra. Laura Sánchez',
            'author_credentials' => 'Especialista en DBT y Neurociencias',
            'publication_type' => 'revision',
            'target_audience' => 'profesionales',
            'is_disclaimer_accepted' => 1,
        ]);
    }

    public function test_user_can_publish_article_with_cover_image_file(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create(['role' => 'profesional']);
        $topicArea = \App\Models\TopicArea::create([
            'name' => 'Mindfulness',
            'slug' => 'mindfulness',
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('portada.jpg', 800, 600);

        $response = $this->actingAs($user)->post('/revista', [
            'topic_area_id' => $topicArea->id,
            'title' => 'Mindfulness y Autocompasión',
            'author_name' => 'Dr. Fernando Rios',
            'author_credentials' => 'Psicólogo Clínico',
            'visual_theme' => 'salvia',
            'publication_type' => 'divulgacion',
            'target_audience' => 'general',
            'summary' => 'Resumen sobre mindfulness y bienestar emocional.',
            'content' => 'Contenido completo para la investigación con más de cien caracteres para superar las validaciones requeridas.',
            'cover_image' => $file,
            'is_disclaimer_accepted' => 1,
        ]);

        $response->assertRedirect();
        $article = Article::where('title', 'Mindfulness y Autocompasión')->first();
        $this->assertNotNull($article);
        $this->assertNotNull($article->cover_image_path);
        $this->assertStringStartsWith('/storage/articles/', $article->cover_image_path);
    }
}
