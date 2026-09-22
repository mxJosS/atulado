<?php

namespace App\Services;

use App\Models\AplicacionPuchol;
use App\Models\Clasificacion;
use App\Models\EventoCrisis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Puchol en el motor: test breve del estado de ánimo de 22 ítems.
 *
 * Para no agobiar, no se aplica de golpe. Cada ciclo (30 días) se reparte en
 * cuatro bloques cortos que aparecen tras el registro diario, uno por día como
 * máximo y sólo en días sin otras preguntas. Si la persona cierra el bloque
 * sin contestar se vuelve a ofrecer otro día, hasta MAX_OFRECIMIENTOS veces.
 * También puede responderlos cuando quiera desde su plan de seguridad.
 *
 * Resultados: sólo informativos para el plano clínico, salvo impulsos
 * suicidas, donde cualquier valor mayor a 0 abre un caso Rojo al momento.
 */
class PucholService
{
    public const ESCALA = [0 => 'Nada en absoluto', 1 => 'Algo', 2 => 'Moderadamente', 3 => 'Mucho', 4 => 'Muchísimo'];

    public const ITEMS = [
        'a1' => 'Angustiado(a)',
        'a2' => 'Nervioso(a)',
        'a3' => 'Preocupado(a)',
        'a4' => 'Asustado(a) o aprensivo(a)',
        'a5' => 'Tenso(a) o con los nervios de punta',
        'f1' => 'Palpitaciones, pulso acelerado o taquicardia',
        'f2' => 'Sudores, escalofríos o sofocos',
        'f3' => 'Temblores o estremecimientos',
        'f4' => 'Falta de aliento o dificultad para respirar',
        'f5' => 'Sensación de ahogo',
        'f6' => 'Dolor o tensión en el pecho',
        'f7' => 'Estómago revuelto o náuseas',
        'f8' => 'Sensación de mareo o de que todo da vueltas',
        'f9' => 'Sensación de que usted es irreal o de que el mundo es irreal',
        'f10' => 'Sensación de insensibilidad o de hormigueos',
        'd1' => 'Triste o decaído(a)',
        'd2' => 'Desanimado(a) o desesperanzado(a)',
        'd3' => 'Autoestima baja',
        'd4' => 'Sensación de no valer o de ser inadecuado(a)',
        'd5' => 'Pérdida de placer o de satisfacción con la vida',
        's1' => '¿Tiene algún pensamiento de suicidarse?',
        's2' => '¿Quisiera poner fin a su vida?',
    ];

    /** Bloques cortos. Impulsos suicidas va al final de depresión, no como bloque aparte. */
    public const BLOQUES = [
        'A' => ['a1', 'a2', 'a3', 'a4', 'a5'],
        'B' => ['f1', 'f2', 'f3', 'f4', 'f5'],
        'C' => ['f6', 'f7', 'f8', 'f9', 'f10'],
        'D' => ['d1', 'd2', 'd3', 'd4', 'd5', 's1', 's2'],
    ];

    /** Qué pregunta cada bloque (texto neutro para la persona usuaria). */
    public const ENCABEZADOS = [
        'A' => 'Últimamente, ¿qué tanto te has sentido…?',
        'B' => 'Últimamente, ¿qué tanto has notado en tu cuerpo…?',
        'C' => 'Últimamente, ¿qué tanto has notado en tu cuerpo…?',
        'D' => 'Últimamente, ¿qué tanto te has sentido…?',
    ];

    /** sección => [ítems, máximo] */
    public const SECCIONES = [
        'ansiedad' => [['a1', 'a2', 'a3', 'a4', 'a5'], 20],
        'fisica' => [['f1', 'f2', 'f3', 'f4', 'f5', 'f6', 'f7', 'f8', 'f9', 'f10'], 40],
        'depresion' => [['d1', 'd2', 'd3', 'd4', 'd5'], 20],
        'suicidas' => [['s1', 's2'], 8],
    ];

    public const NOMBRE_SECCION = [
        'ansiedad' => 'Sentimientos de ansiedad',
        'fisica' => 'Ansiedad física',
        'depresion' => 'Depresión',
        'suicidas' => 'Impulsos suicidas',
    ];

    /** Claves de calificación del instrumento: [desde, hasta, significado] */
    public const CLAVES = [
        'ansiedad' => [
            [0, 1, 'Pocos síntomas de ansiedad o ninguno'],
            [2, 4, 'Ansiedad marginal'],
            [5, 8, 'Ansiedad leve'],
            [9, 12, 'Ansiedad moderada'],
            [13, 16, 'Ansiedad grave'],
            [17, 20, 'Ansiedad extrema'],
        ],
        'fisica' => [
            [0, 2, 'Pocos síntomas físicos de ansiedad o ninguno'],
            [3, 6, 'Algunos síntomas físicos de ansiedad'],
            [7, 10, 'Síntomas físicos de ansiedad leves'],
            [11, 20, 'Síntomas físicos de ansiedad moderados'],
            [21, 30, 'Síntomas físicos de ansiedad fuertes'],
            [31, 40, 'Síntomas físicos de ansiedad extremos'],
        ],
    ];

    public function __construct(private ClinicalEngineService $motor)
    {
    }

    public static function interpretar(string $seccion, ?int $total): ?string
    {
        if ($total === null) {
            return null;
        }
        if ($seccion === 'suicidas') {
            return $total > 0 ? 'Positivo: activa el protocolo de crisis' : 'Sin impulsos referidos';
        }
        foreach (self::CLAVES[$seccion] ?? [] as [$desde, $hasta, $texto]) {
            if ($total >= $desde && $total <= $hasta) {
                return $texto;
            }
        }

        return null; // depresión: el instrumento no trae clave
    }

    private function diasCiclo(): int
    {
        return (int) config('clinical.puchol.dias_ciclo', 30);
    }

    public function cicloEnCurso(User $user): ?AplicacionPuchol
    {
        return AplicacionPuchol::where('user_id', $user->id)->where('estado', 'en_curso')
            ->orderByDesc('fecha')->orderByDesc('id')->first();
    }

    private function tocaNuevoCiclo(User $user): bool
    {
        $ultimo = AplicacionPuchol::where('user_id', $user->id)->orderByDesc('fecha')->orderByDesc('id')->value('fecha');

        return $ultimo === null || Carbon::parse($ultimo)->lte(Carbon::today()->subDays($this->diasCiclo()));
    }

    private function conCasoAbierto(User $user): bool
    {
        return EventoCrisis::where('user_id', $user->id)->abiertos()->exists();
    }

    /** @return list<string> */
    private static function pendientes(?AplicacionPuchol $ciclo): array
    {
        return array_values(array_diff(array_keys(self::BLOQUES), $ciclo?->bloquesRespondidos() ?? []));
    }

    /**
     * ¿Qué bloque ofrecer hoy tras el registro diario? Quien llama ya comprobó
     * que hoy no hubo otras preguntas. Devuelve la letra del bloque o null.
     */
    public function ofrecerHoy(User $user): ?string
    {
        if ($this->conCasoAbierto($user)) {
            return null;
        }

        $hoy = Carbon::today();
        $ciclo = $this->cicloEnCurso($user);

        if (!$ciclo) {
            if (!$this->tocaNuevoCiclo($user)) {
                return null;
            }
            $ciclo = AplicacionPuchol::create([
                'user_id' => $user->id, 'fecha' => $hoy, 'estado' => 'en_curso',
                'origen' => 'periodico', 'disponible_desde' => $hoy,
            ]);
        }

        if ($ciclo->disponible_desde && $ciclo->disponible_desde->gt($hoy)) {
            return null;
        }
        // Un bloque al día como máximo, y no se insiste el mismo día.
        if ($ciclo->ultima_respuesta?->isSameDay($hoy) || $ciclo->ofrecido_en?->isSameDay($hoy)) {
            return null;
        }

        if ($ciclo->ofrecido_pendiente !== null) {
            $ciclo->intentos++;
            if ($ciclo->intentos >= (int) config('clinical.puchol.max_ofrecimientos', 3)) {
                $ciclo->forceFill(['estado' => 'abandonado', 'ofrecido_pendiente' => null])->save();

                return null;
            }
        }

        $letra = self::pendientes($ciclo)[0] ?? null;
        if ($letra === null) {
            return null;
        }

        $ciclo->forceFill(['ofrecido_pendiente' => $letra, 'ofrecido_en' => $hoy])->save();

        return $letra;
    }

    /**
     * Bloques que la persona puede responder desde su plan de seguridad.
     *
     * @return list<string>
     */
    public function bloquesDisponibles(User $user): array
    {
        if ($this->conCasoAbierto($user)) {
            return [];
        }

        $ciclo = $this->cicloEnCurso($user);
        if ($ciclo) {
            return self::pendientes($ciclo);
        }

        return $this->tocaNuevoCiclo($user) ? array_keys(self::BLOQUES) : [];
    }

    /**
     * El MDI salió con estrés o sueño alto: el ciclo se adelanta y empieza
     * mañana (nunca el mismo día que el MDI).
     */
    public function adelantar(User $user): void
    {
        if ($this->cicloEnCurso($user)) {
            return;
        }

        $ultimo = AplicacionPuchol::where('user_id', $user->id)->orderByDesc('fecha')->value('fecha');
        if ($ultimo && Carbon::parse($ultimo)->gt(Carbon::today()->subDays((int) config('clinical.puchol.dias_sin_repetir_adelanto', 7)))) {
            return; // acaba de hacer uno
        }

        AplicacionPuchol::create([
            'user_id' => $user->id, 'fecha' => Carbon::today(), 'estado' => 'en_curso',
            'origen' => 'adelantado', 'disponible_desde' => Carbon::tomorrow(),
        ]);
    }

    /**
     * Guarda un bloque. Devuelve 'apoyo' si hay impulsos suicidas (caso Rojo abierto).
     *
     * @param  array<string,int|string>  $respuestas
     *
     * @throws ValidationException si el bloque no está disponible
     */
    public function responder(User $user, string $letra, array $respuestas): ?string
    {
        if (!in_array($letra, $this->bloquesDisponibles($user), true)) {
            throw ValidationException::withMessages(['bloque' => 'Estas preguntas ya no están pendientes.']);
        }

        return DB::transaction(function () use ($user, $letra, $respuestas) {
            $ciclo = $this->cicloEnCurso($user) ?? AplicacionPuchol::create([
                'user_id' => $user->id, 'fecha' => Carbon::today(), 'estado' => 'en_curso',
                'origen' => 'periodico', 'disponible_desde' => Carbon::today(),
            ]);

            $todas = $ciclo->respuestas ?? [];
            foreach (self::BLOQUES[$letra] as $item) {
                $todas[$item] = max(0, min(4, (int) $respuestas[$item]));
            }

            $bloques = array_values(array_unique([...$ciclo->bloquesRespondidos(), $letra]));
            $cambios = [
                'respuestas' => $todas,
                'bloques' => $bloques,
                'ultima_respuesta' => Carbon::today(),
                'intentos' => 0,
                'ofrecido_pendiente' => $ciclo->ofrecido_pendiente === $letra ? null : $ciclo->ofrecido_pendiente,
            ];

            foreach (self::SECCIONES as $seccion => [$items]) {
                if (count(array_intersect_key($todas, array_flip($items))) === count($items)) {
                    $cambios[$seccion] = array_sum(array_intersect_key($todas, array_flip($items)));
                }
            }

            if (count($bloques) === count(self::BLOQUES)) {
                $cambios['estado'] = 'completado';
                $cambios['completado_en'] = now();
            }

            $ciclo->forceFill($cambios)->save();

            // Impulsos suicidas: cualquier valor mayor a 0 abre un caso Rojo al momento.
            if (($cambios['suicidas'] ?? 0) > 0) {
                Clasificacion::create([
                    'user_id' => $user->id, 'fecha' => Carbon::today(), 'nivel' => 'ROJO', 'origen' => 'puchol',
                    'banderas' => ['impulsos_suicidas' => $cambios['suicidas'], 's1' => $todas['s1'], 's2' => $todas['s2']],
                ]);
                $this->motor->registrarEventoCrisis($user, ['nivel' => 'ROJO', 'origen' => 'puchol']);

                return 'apoyo';
            }

            return null;
        });
    }

    /**
     * Herramientas que el plan de seguridad sugiere según el último resultado.
     * Nunca se muestra el puntaje: sólo la sugerencia.
     *
     * @return list<array{clave:string, titulo:string, texto:string, ruta:string}>
     */
    public function sugerencias(User $user): array
    {
        $ultimo = AplicacionPuchol::where('user_id', $user->id)
            ->where(fn ($q) => $q->whereNotNull('ansiedad')->orWhereNotNull('fisica'))
            ->orderByDesc('fecha')->orderByDesc('id')->first();

        $sugerencias = [];
        // Desde «ansiedad leve» (5) y «síntomas físicos leves» (7) según las claves del instrumento.
        if (($ultimo?->fisica ?? 0) >= 7 || ($ultimo?->ansiedad ?? 0) >= 5) {
            $sugerencias['respiracion'] = ['clave' => 'respiracion', 'titulo' => 'Respiración 4-7-8', 'texto' => 'Baja el ritmo del cuerpo en un par de minutos.', 'ruta' => 'tools.respiracion'];
        }
        if (($ultimo?->fisica ?? 0) >= 7) {
            $sugerencias['grounding'] = ['clave' => 'grounding', 'titulo' => 'Anclaje 5-4-3-2-1', 'texto' => 'Vuelve al presente con tus cinco sentidos.', 'ruta' => 'tools.grounding'];
        }
        if (($ultimo?->ansiedad ?? 0) >= 5) {
            $sugerencias['stop'] = ['clave' => 'stop', 'titulo' => 'Técnica STOP', 'texto' => 'Una pausa breve antes de reaccionar.', 'ruta' => 'tools.stop'];
        }

        return array_values($sugerencias);
    }
}
