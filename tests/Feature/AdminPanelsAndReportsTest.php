<?php

namespace Tests\Feature;

use App\Models\EventoCrisis;
use App\Models\Institution;
use App\Models\MoodLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelsAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_or_regular_user_cannot_access_institutions_and_reports(): void
    {
        // Guest
        $this->get('/admin/instituciones')->assertRedirect('/login');
        $this->get('/admin/reportes')->assertRedirect('/login');
        $this->get('/admin/reportes/visor')->assertRedirect('/login');

        // Regular User
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/instituciones')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/admin/reportes')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/admin/reportes/visor')->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_access_institutions_index(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // When empty
        $response = $this->actingAs($admin)->get('/admin/instituciones');
        $response->assertStatus(200);
        $response->assertSee('Estado de la plataforma');
        $response->assertSee('No hay instituciones registradas aún');

        // When institution created
        Institution::create([
            'slug' => 'empresa-prueba',
            'name' => 'Empresa de Prueba S.A.',
            'category' => 'Tecnología',
            'contact_name' => 'Contacto',
            'contact_email' => 'test@empresa.com',
            'professional_name' => 'Psic. Prueba',
            'plan' => 'Pyme',
            'users_count' => 10,
        ]);

        $response2 = $this->actingAs($admin)->get('/admin/instituciones');
        $response2->assertStatus(200);
        $response2->assertSee('Empresa de Prueba S.A.');
    }

    public function test_admin_can_access_institution_detail_with_grouped_semaforo(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $inst = Institution::create([
            'slug' => 'empresa-demo',
            'name' => 'Empresa Demo S.A.',
            'category' => 'Manufactura',
            'contact_name' => 'Contacto Demo',
            'contact_email' => 'demo@empresa.com',
            'professional_name' => 'Psic. Especialista',
            'plan' => 'Corporativo',
            'users_count' => 50,
            'departments_data' => [
                [
                    'id' => 'operaciones',
                    'name' => 'Operaciones y Frente de Obra',
                    'departments' => [
                        ['name' => 'Cuadrilla Nocturna', 'code' => 'cuadrilla_nocturna']
                    ]
                ],
                [
                    'id' => 'corporativo',
                    'name' => 'Corporativo y Dirección',
                    'departments' => [
                        ['name' => 'Administración y Finanzas', 'code' => 'admin_finanzas']
                    ]
                ]
            ]
        ]);

        $response = $this->actingAs($admin)->get('/admin/instituciones/empresa-demo');
        $response->assertStatus(200);
        $response->assertSee('Empresa Demo S.A.');
        $response->assertSee('Semáforo Clínico por Departamento');
        $response->assertSee('Operaciones y Frente de Obra');
        $response->assertSee('Corporativo y Dirección');
        $response->assertSee('Cuadrilla Nocturna');
        $response->assertSee('Administración y Finanzas');
        $response->assertSee('Por Grupos');
        $response->assertSee('Lista Plana');
    }

    public function test_admin_can_access_analytics_and_structure_modules(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // When empty
        $this->actingAs($admin)->get('/admin/analitica')
            ->assertStatus(200)
            ->assertSee('Analítica e Índices de Bienestar')
            ->assertSee('Índice de Bienestar (IBI)')
            ->assertSee('Riesgo Concentrado (IRC)');

        $this->actingAs($admin)->get('/admin/altas-estructura')
            ->assertStatus(200)
            ->assertSee('Altas y Estructura Organizacional')
            ->assertSee('No hay instituciones registradas aún');

        // When institution created
        Institution::create([
            'slug' => 'empresa-estructura',
            'name' => 'Empresa de Estructura S.A.',
            'category' => 'Construcción',
            'contact_name' => 'Ing. Estructura',
            'contact_email' => 'est@empresa.com',
            'professional_name' => 'Psic. Estructura',
            'plan' => 'Corporativo',
            'users_count' => 10,
        ]);

        $this->actingAs($admin)->get('/admin/altas-estructura')
            ->assertStatus(200)
            ->assertSee('Altas y Estructura Organizacional')
            ->assertSee('Cargar Padrón CSV')
            ->assertSee('Áreas y Macro-Grupos Asignados');
    }

    public function test_admin_can_access_client_view_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // When empty
        $response = $this->actingAs($admin)->get('/admin/vista-cliente');
        $response->assertStatus(200);
        $response->assertSee('No hay instituciones registradas aún');

        // When institution created
        Institution::create([
            'slug' => 'organizacion-salud',
            'name' => 'Organización de Salud Integral',
            'category' => 'Salud',
            'contact_name' => 'Contacto Salud',
            'contact_email' => 'salud@org.com',
            'professional_name' => 'Psic. Valeria Ramos',
            'professional_license' => '1234567',
            'plan' => 'Institucional',
            'users_count' => 100,
        ]);

        $response2 = $this->actingAs($admin)->get('/admin/vista-cliente');
        $response2->assertStatus(200);
        $response2->assertSee('Estado general de la organización');
        $response2->assertSee('Lo que este panel muestra y lo que nunca mostrará');
        $response2->assertSee('Distribución del acompañamiento');
        $response2->assertSee('Cumplimiento NOM-035-STPS-2018');
        $response2->assertSee('Psic. Valeria Ramos');
    }

    public function test_admin_can_access_reports_hub_and_view_all_catalogs(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/reportes');
        $response->assertStatus(200);
        $response->assertSee('Centro de Reportes');
        $response->assertSee('Catálogo y emisión');
        $response->assertSee('Qué contiene cada reporte');
        $response->assertSee('Emisiones y entregas');
        $response->assertSee('Reglas de seguridad y diseño');
    }

    public function test_admin_can_open_report_viewer_for_different_report_types(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // When no institutions: shows empty state card
        $emptyResp = $this->actingAs($admin)->get('/admin/reportes/visor?r=ejecutivo');
        $emptyResp->assertStatus(200);
        $emptyResp->assertSee('No hay instituciones registradas');

        // When institution exists
        $inst = Institution::create([
            'slug' => 'empresa-reporte',
            'name' => 'Empresa de Reporte Oficial S.A.',
            'category' => 'Corporativo',
            'contact_name' => 'Director',
            'contact_email' => 'dir@empresa.com',
            'professional_name' => 'Psic. Reporte',
            'plan' => 'Corporativo',
            'users_count' => 20,
        ]);

        // 1. Reporte Ejecutivo
        $resp1 = $this->actingAs($admin)->get('/admin/reportes/visor?r=ejecutivo&inst=empresa-reporte');
        $resp1->assertStatus(200);
        $resp1->assertSee('Descargar PDF');
        $resp1->assertSee('Volver a reportes');
        $resp1->assertSee('Empresa de Reporte Oficial S.A.');

        // 2. NOM-035
        $resp2 = $this->actingAs($admin)->get('/admin/reportes/visor?r=nom035&inst=empresa-reporte');
        $resp2->assertStatus(200);
        $resp2->assertSee('Descargar PDF');

        // 3. Clínico Confidencial
        $resp3 = $this->actingAs($admin)->get('/admin/reportes/visor?r=clinico&inst=empresa-reporte&dest=profesional');
        $resp3->assertStatus(200);
        $resp3->assertSee('Descargar PDF');
    }

    public function test_admin_can_create_a_new_institution_with_macro_groups(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $postData = [
            'name' => 'Hospital Central del Mayab S.A.',
            'short_name' => 'Hospital Mayab',
            'category' => 'Salud y Hospitales',
            'city' => 'Mérida, Yucatán',
            'contact_name' => 'Dra. Laura Morales',
            'contact_position' => 'Directora de Recursos Humanos',
            'contact_email' => 'lmorales@hospital-mayab.mx',
            'contact_phone' => '999 555 1234',
            'professional_name' => 'Psic. Carlos Peniche',
            'professional_license' => '8821940',
            'plan' => 'Institucional Premium',
            'users_count' => 350,
            'renewal_date' => '2027-05-15',
            'macro_groups' => 'Urgencias y Triage, Consulta Externa y Pisos, Administración',
        ];

        $response = $this->actingAs($admin)->post('/admin/instituciones', $postData);

        $response->assertRedirect('/admin/instituciones');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('institutions', [
            'slug' => 'hospital-mayab',
            'name' => 'Hospital Central del Mayab S.A.',
            'category' => 'Salud y Hospitales',
            'professional_name' => 'Psic. Carlos Peniche',
        ]);

        // Verificar que el semáforo y detalle muestra los nuevos macro-grupos
        $detailResponse = $this->actingAs($admin)->get('/admin/instituciones/hospital-mayab');
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Hospital Mayab');
        $detailResponse->assertSee('Urgencias y Triage');
        $detailResponse->assertSee('Consulta Externa y Pisos');
        $detailResponse->assertSee('Psic. Carlos Peniche');
    }

    public function test_sentiment_checkins_dynamically_alter_institution_semaforo(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Crear una institución
        $inst = Institution::create([
            'slug' => 'clinica-sur',
            'name' => 'Clínica del Sur',
            'category' => 'Salud',
            'contact_name' => 'Dr. González',
            'contact_email' => 'dr@clinica.com',
            'professional_name' => 'Psic. Elena',
            'plan' => 'Pyme',
            'users_count' => 2,
            'departments_data' => [
                [
                    'id' => 'medico',
                    'name' => 'Cuerpo Médico',
                    'departments' => [
                        ['name' => 'Guardia Nocturna', 'code' => 'guardia']
                    ]
                ]
            ]
        ]);

        // Crear dos usuarios asignados a la institución
        $user1 = User::factory()->create([
            'institution_id' => $inst->id,
            'macro_group' => 'Cuerpo Médico',
            'department' => 'Guardia Nocturna',
            'employee_number' => 'COL-MED-01'
        ]);

        $user2 = User::factory()->create([
            'institution_id' => $inst->id,
            'macro_group' => 'Cuerpo Médico',
            'department' => 'Guardia Nocturna',
            'employee_number' => 'COL-MED-02'
        ]);

        // Usuario 1 envía registro saludable (score 5)
        MoodLog::create([
            'user_id' => $user1->id,
            'score' => 5,
            'valor_invertido' => 0,
            'primary_emotion' => 'Calma',
            'logged_date' => Carbon::today()->format('Y-m-d')
        ]);

        // Usuario 2 envía registro crítico con bandera léxica (score 1)
        MoodLog::create([
            'user_id' => $user2->id,
            'score' => 1,
            'valor_invertido' => 4,
            'primary_emotion' => 'Desesperanza',
            'bandera_lexica' => true,
            'logged_date' => Carbon::today()->format('Y-m-d')
        ]);

        $response = $this->actingAs($admin)->get('/admin/instituciones/clinica-sur');
        $response->assertStatus(200);

        // La distribución debe reflejar 50% Verde y 50% Rojo calculados en tiempo real desde DB
        $response->assertSee('50% Verde');
        $response->assertSee('50% Rojo');
        $response->assertDontSee('COL-MED-01'); // Anonimato garantizado en la vista agregada B2B
    }

    public function test_crisis_events_dynamically_populate_clinical_queue(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $inst = Institution::create([
            'slug' => 'puerto-progreso',
            'name' => 'Logística Puerto Progreso',
            'category' => 'Transporte y Aduanas',
            'contact_name' => 'Lic. Mendez',
            'contact_email' => 'mendez@puerto.com',
            'professional_name' => 'Psic. Rodrigo Ancona',
            'plan' => 'Corporativo',
            'users_count' => 1,
            'departments_data' => null
        ]);

        $user = User::factory()->create([
            'institution_id' => $inst->id,
            'employee_number' => 'COL-PRG-99',
            'macro_group' => 'Operaciones',
            'department' => 'Aduana'
        ]);

        // Registrar un evento de crisis activo
        EventoCrisis::create([
            'user_id' => $user->id,
            'disparado_en' => Carbon::now()->subMinutes(15),
            'estado' => 'abierto',
            'notas_cierre' => 'Protocolo de contención nocturno activado por botón SOS.'
        ]);

        $response = $this->actingAs($admin)->get('/admin/instituciones/puerto-progreso');
        $response->assertStatus(200);

        // La cola clínica debe mostrar la alerta con el código del colaborador y notas
        $response->assertSee('Cola de Atención Clínica');
        $response->assertSee('COL-PRG-99');
        $response->assertSee('Protocolo de contención nocturno activado');
    }

    public function test_newly_created_institution_appears_in_reports_and_can_emit_viewer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $inst = Institution::create([
            'slug' => 'universidad-maya',
            'name' => 'Universidad Maya del Sureste',
            'short_name' => 'Universidad Maya',
            'category' => 'Educación Superior',
            'contact_name' => 'Rectoría',
            'contact_email' => 'rectoria@umaya.edu.mx',
            'professional_name' => 'Dra. Gabriela Solís',
            'professional_license' => '994120',
            'plan' => 'Comunidad',
            'users_count' => 500,
            'renewal_date' => '2027-08-20'
        ]);

        // Debe aparecer en el catálogo de reportes
        $reportsResp = $this->actingAs($admin)->get('/admin/reportes');
        $reportsResp->assertStatus(200);
        $reportsResp->assertSee('Universidad Maya del Sureste');

        // Debe poder abrir el visor con su contexto inyectado
        $viewerResp = $this->actingAs($admin)->get('/admin/reportes/visor?inst=universidad-maya&r=nom035');
        $viewerResp->assertStatus(200);
        $viewerResp->assertSee('universidad-maya');
        $viewerResp->assertSee('Universidad Maya del Sureste');
        $viewerResp->assertSee('Dra. Gabriela Solís');
    }
}

