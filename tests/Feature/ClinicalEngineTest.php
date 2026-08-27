<?php

namespace Tests\Feature;

use App\Models\AplicacionWho5;
use App\Models\EventoCrisis;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\ClinicalEngineService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalEngineTest extends TestCase
{
    use RefreshDatabase;

    protected ClinicalEngineService $engine;
    protected User $user;
    protected User $profesional;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = app(ClinicalEngineService::class);
        $this->user = User::factory()->create([
            'email' => 'paciente@atulado.com.mx',
            'role' => 'usuario',
        ]);
        $this->profesional = User::factory()->create([
            'email' => 'profesional@atulado.com.mx',
            'role' => 'profesional',
        ]);
    }

    /**
     * Verificación 1 y 2: WHO-5 evalúa crudo <= 12 y cualquier ítem individual <= 1 abre MDI
     */
    public function test_who5_crudo_and_single_item_threshold(): void
    {
        // Caso A: Crudo = 10 (<= 12) -> Debe abrir MDI
        $resA = $this->engine->procesarWHO5($this->user, [
            'i1' => 2, 'i2' => 2, 'i3' => 2, 'i4' => 2, 'i5' => 2
        ]);
        $this->assertTrue($resA['abrir_mdi']);
        $this->assertEquals(10, $resA['crudo']);
        $this->assertEquals(40, $resA['escala']);

        // Caso B: Crudo = 17 (> 12), pero con un ítem en 0 -> DEBE abrir MDI (regla crítica 2)
        $resB = $this->engine->procesarWHO5($this->user, [
            'i1' => 5, 'i2' => 5, 'i3' => 4, 'i4' => 3, 'i5' => 0
        ]);
        $this->assertTrue($resB['abrir_mdi'], 'Un ítem con valor 0 debe abrir MDI aun con crudo alto');
        $this->assertEquals(17, $resB['crudo']);

        // Caso C: Crudo = 20 (> 12), todos los ítems >= 2 -> No abre MDI (VERDE)
        $resC = $this->engine->procesarWHO5($this->user, [
            'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4
        ]);
        $this->assertFalse($resC['abrir_mdi']);
        $this->assertEquals('VERDE', $resC['nivel']);
    }

    /**
     * Verificación 3 y 5: MDI calcula max en subítems (8a/8b, 10a/10b) y clasifica severidad
     */
    public function test_mdi_subitems_max_and_severity_levels(): void
    {
        // Test con 8a=4, 8b=1 -> max es 4; 10a=0, 10b=3 -> max es 3
        $items = [
            'i1' => 2, 'i2' => 2, 'i3' => 2, 'i4' => 2, 'i5' => 2,
            'i6' => 0, 'i7' => 2,
            'i8a' => 4, 'i8b' => 1,
            'i9' => 2,
            'i10a' => 0, 'i10b' => 3,
        ];
        // Total = 2+2+2+2+2+0+2+4+2+3 = 21 (NARANJA: 20-29)
        $res = $this->engine->procesarMDI($this->user, $items);
        $this->assertEquals(21, $res['total']);
        $this->assertEquals('NARANJA', $res['nivel']);
        $this->assertFalse($res['abrir_asq']);

        // Test con ítem 6 > 0 -> Debe abrir ASQ
        $items['i6'] = 2; // total = 23
        $resConAsq = $this->engine->procesarMDI($this->user, $items);
        $this->assertTrue($resConAsq['abrir_asq']);
    }

    /**
     * Verificación 17 y 18: ASQ maneja 3 opciones y 'prefiero_no_contestar' se procesa como afirmativa
     */
    public function test_asq_three_options_and_conservative_triage(): void
    {
        // Caso A: Todas negativas (no)
        $resNeg = $this->engine->procesarASQ($this->user, [
            'p1' => 'no', 'p2' => 'no', 'p3' => 'no', 'p4' => 'no',
        ]);
        $this->assertEquals('NEGATIVA', $resNeg['resultado']);
        $this->assertNull($resNeg['nivel']);

        // Caso B: P1 positiva y P5 negativa -> POSITIVA_NO_AGUDA (ROJO)
        $resNoAguda = $this->engine->procesarASQ($this->user, [
            'p1' => 'si', 'p2' => 'no', 'p3' => 'no', 'p4' => 'no', 'p5' => 'no'
        ]);
        $this->assertEquals('POSITIVA_NO_AGUDA', $resNoAguda['resultado']);
        $this->assertEquals('ROJO', $resNoAguda['nivel']);

        // Caso C: P2 'prefiero_no_contestar' y P5 'prefiero_no_contestar' -> POSITIVA_AGUDA (ROJO_AGUDO)
        $resAguda = $this->engine->procesarASQ($this->user, [
            'p1' => 'no', 'p2' => 'prefiero_no_contestar', 'p3' => 'no', 'p4' => 'no', 'p5' => 'prefiero_no_contestar'
        ]);
        $this->assertEquals('POSITIVA_AGUDA', $resAguda['resultado']);
        $this->assertEquals('ROJO_AGUDO', $resAguda['nivel']);
        $this->assertNotNull($resAguda['evento_crisis_id']);
    }

    /**
     * Verificación 4 y 20: Un caso Rojo solo se cierra con contacto humano verificado por un profesional
     */
    public function test_crisis_case_only_closed_by_verified_professional_contact(): void
    {
        $evento = $this->engine->registrarEventoCrisis($this->user);
        $this->assertEquals('abierto', $evento->estado);

        // Intento de cierre por un usuario común (no profesional) -> Falla
        $intentoUsuarioComun = $this->engine->verificarCierreCaso($evento, $this->user, 'Traté de cerrar');
        $this->assertFalse($intentoUsuarioComun);
        $this->assertEquals('abierto', $evento->fresh()->estado);

        // Cierre por un profesional autorizado -> Éxito
        $cierreExitoso = $this->engine->verificarCierreCaso($evento, $this->profesional, 'Contacto telefónico realizado con éxito');
        $this->assertTrue($cierreExitoso);
        $this->assertEquals('cerrado', $evento->fresh()->estado);
        $this->assertNotNull($evento->fresh()->contactado_en);
    }

    /**
     * Verificación 8 y 9: Plano gerencial aplica umbral mínimo de 15 personas para reporte agregado
     */
    public function test_managerial_view_enforces_15_user_anonymity_threshold(): void
    {
        // Caso A: Menos de 15 usuarios
        $reporteIncompleto = $this->engine->obtenerVistaGerencialAgregada();
        $this->assertFalse($reporteIncompleto['disponible']);
        $this->assertStringContainsString('mínimo de 15', $reporteIncompleto['motivo']);

        // Caso B: Crear 15 usuarios para completar el corte
        User::factory()->count(14)->create();
        $reporteValido = $this->engine->obtenerVistaGerencialAgregada();
        $this->assertTrue($reporteValido['disponible']);
        $this->assertArrayHasKey('distribucion', $reporteValido);
    }

    /**
     * Verificación de endpoints AJAX de evaluación clínica
     */
    public function test_clinical_assessment_http_endpoints(): void
    {
        $this->actingAs($this->user);

        // Submit WHO-5
        $resWho5 = $this->postJson('/assessment/who5', [
            'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4,
            'origen' => 'programada',
        ]);
        $resWho5->assertStatus(200)->assertJson(['success' => true]);

        // Submit MDI
        $resMdi = $this->postJson('/assessment/mdi', [
            'i1' => 1, 'i2' => 1, 'i3' => 1, 'i4' => 1, 'i5' => 1,
            'i6' => 0, 'i7' => 1, 'i8a' => 1, 'i8b' => 0, 'i9' => 1,
            'i10a' => 1, 'i10b' => 0,
        ]);
        $resMdi->assertStatus(200)->assertJson(['success' => true]);

        // Submit ASQ
        $resAsq = $this->postJson('/assessment/asq', [
            'p1' => 'no', 'p2' => 'no', 'p3' => 'no', 'p4' => 'no',
        ]);
        $resAsq->assertStatus(200)->assertJson(['success' => true]);
    }
}
