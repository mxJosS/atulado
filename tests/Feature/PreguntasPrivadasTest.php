<?php

namespace Tests\Feature;

use App\Models\AplicacionWho5;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * La persona usuaria responde preguntas, pero nunca ve (ni en pantalla ni con
 * las herramientas del navegador) qué instrumento es, sus puntajes, niveles
 * o las reglas del motor.
 */
class PreguntasPrivadasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Lo que no debe aparecer en nada que llegue al navegador de la persona usuaria.
     * Palabras completas: "MDI" dentro de un token aleatorio no cuenta.
     */
    private const PROHIBIDO_EN_PAGINA = [
        'WHO-?5', 'MDI', 'ASQ', 'Puchol', 'Burns', 'NIMH', 'OMS', 'Major Depression', 'Suicide-Screening',
        'Capa [0-4]', 'ROJO_AGUDO', 'assessment', 'SENSITIVE_KEYWORDS',
        // términos que sólo están en la lista del filtro léxico (config/clinical.php)
        'pastillas para dormir todas', 'cortarme las venas', 'ojala no despertara',
    ];

    /** Llaves que delatarían la lógica del motor en una respuesta JSON. */
    private const LLAVES_PROHIBIDAS = [
        'resultado', 'nivel', 'crudo', 'escala', 'total', 'ruta', 'senal', 'abrir_who5', 'abrir_mdi', 'abrir_asq',
        'origen_who5', 'valor_invertido', 'filtro_lexico', 'bandera_lexica', 'terminos_detectados', 'evaluacion', 'log',
        'evento_crisis_id', 'who5_id', 'mdi_id', 'asq_id',
    ];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function sinLlavesDelMotor(TestResponse $respuesta): TestResponse
    {
        $json = $respuesta->json();
        array_walk_recursive($json, fn () => null);
        $llaves = array_keys(\Illuminate\Support\Arr::dot($json));

        foreach ($llaves as $llave) {
            foreach (self::LLAVES_PROHIBIDAS as $prohibida) {
                $this->assertFalse(
                    in_array($prohibida, explode('.', $llave), true),
                    "La respuesta expone «{$prohibida}»: " . json_encode($json)
                );
            }
        }

        return $respuesta;
    }

    private function registrar(int $carita, ?string $texto = null): TestResponse
    {
        return $this->actingAs($this->user)->postJson('/mood/checkin', [
            'score' => $carita,
            'primary_emotion' => 'Calma',
            'journal_entry' => $texto,
        ]);
    }

    public function test_pages_never_name_the_instruments_or_ship_the_lexicon(): void
    {
        $paginas = [
            $this->actingAs($this->user)->get('/dashboard')->assertOk()->getContent(),
            file_get_contents(public_path('js/main.js')),
            $this->actingAs($this->user)->get('/plan-de-seguridad')->assertOk()->getContent(),
        ];

        foreach ($paginas as $i => $contenido) {
            $contenido = str_replace(csrf_token(), '', $contenido);
            foreach (self::PROHIBIDO_EN_PAGINA as $prohibido) {
                $this->assertDoesNotMatchRegularExpression(
                    '/(?<![A-Za-z0-9])' . $prohibido . '(?![A-Za-z0-9])/i',
                    $contenido,
                    "Aparece «{$prohibido}» en " . ['el tablero', 'main.js', 'el plan de seguridad'][$i]
                );
            }
        }

        $this->actingAs($this->user)->get('/dashboard')
            ->assertSee('Unas preguntas para conocerte mejor')
            ->assertSee('Un poco más sobre cómo te has sentido')
            ->assertSee('Queremos asegurarnos de que estés bien');
    }

    public function test_daily_checkin_only_says_which_block_comes_next(): void
    {
        // Revisión mensual al día: no le toca ningún bloque.
        \App\Models\AplicacionPuchol::create(['user_id' => $this->user->id, 'fecha' => today()->subDays(2), 'estado' => 'completado', 'bloques' => ['A', 'B', 'C', 'D']]);
        // Con un WHO-5 reciente, un buen día no abre nada.
        AplicacionWho5::create(['user_id' => $this->user->id, 'fecha' => today()->subDays(3), 'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4, 'crudo' => 20, 'escala' => 80, 'origen' => 'programada']);

        $this->sinLlavesDelMotor($this->registrar(5, 'no quiero vivir'))
            ->assertOk()->assertJsonMissingPath('siguiente');

        // «Regular» reutiliza un WHO-5 de menos de 14 días; sin él, abre el bloque 1.
        $this->sinLlavesDelMotor($this->registrar(3))->assertJsonMissingPath('siguiente');
        AplicacionWho5::query()->delete();
        $this->sinLlavesDelMotor($this->registrar(3))->assertJsonPath('siguiente', 'bloque_1');
        $this->sinLlavesDelMotor($this->registrar(1))->assertJsonPath('siguiente', 'bloque_2');
    }

    public function test_block_origin_comes_from_the_server_not_the_browser(): void
    {
        $this->registrar(3); // el motor decide el origen del bloque 1

        $this->actingAs($this->user)->postJson('/preguntas/1', [
            'i1' => 4, 'i2' => 4, 'i3' => 4, 'i4' => 4, 'i5' => 4,
            'origen' => 'programada', // se ignora
        ])->assertOk();

        $this->assertSame('ruta_b', AplicacionWho5::sole()->origen);
    }

    public function test_blocks_answer_only_the_next_step(): void
    {
        $bajo = ['i1' => 1, 'i2' => 1, 'i3' => 0, 'i4' => 0, 'i5' => 1];
        $alto = ['i1' => 5, 'i2' => 5, 'i3' => 5, 'i4' => 5, 'i5' => 5];
        $mdi = fn (int $base, int $i6) => ['i1' => $base, 'i2' => $base, 'i3' => $base, 'i4' => $base, 'i5' => $base, 'i6' => $i6,
            'i7' => $base, 'i8a' => $base, 'i8b' => 0, 'i9' => $base, 'i10a' => $base, 'i10b' => 0];
        $asq = fn (string $r, ?string $p5 = null) => array_filter(['p1' => $r, 'p2' => 'no', 'p3' => 'no', 'p4' => 'no', 'p5' => $p5]);

        $casos = [
            ['/preguntas/1', $bajo, 'bloque_2'],
            ['/preguntas/1', $alto, null],
            ['/preguntas/2', $mdi(1, 2), 'bloque_3'],
            ['/preguntas/2', $mdi(4, 0), 'apoyo'],
            ['/preguntas/2', $mdi(1, 0), null],
            ['/preguntas/3', $asq('si', 'si'), 'apoyo'],
            ['/preguntas/3', $asq('no'), null],
        ];

        foreach ($casos as [$url, $datos, $siguiente]) {
            $respuesta = $this->sinLlavesDelMotor($this->actingAs($this->user)->postJson($url, $datos))->assertOk();

            $siguiente === null
                ? $respuesta->assertJsonMissingPath('siguiente')->assertJsonStructure(['success', 'mensaje'])
                : $respuesta->assertJsonPath('siguiente', $siguiente);
        }
    }

    public function test_support_banner_check_returns_only_yes_or_no(): void
    {
        $this->actingAs($this->user)->postJson('/apoyo/texto', ['texto' => 'hoy fue un buen día'])
            ->assertExactJson(['apoyo' => false]);

        $this->actingAs($this->user)->postJson('/apoyo/texto', ['texto' => 'Ya no quiero vivir así'])
            ->assertExactJson(['apoyo' => true]);
    }

    public function test_support_banner_check_requires_login(): void
    {
        $this->postJson('/apoyo/texto', ['texto' => 'x'])->assertUnauthorized();
    }

    public function test_old_urls_no_longer_exist(): void
    {
        foreach (['/assessment/who5', '/assessment/mdi', '/assessment/asq'] as $url) {
            $this->actingAs($this->user)->postJson($url, [])->assertNotFound();
        }
    }
}
