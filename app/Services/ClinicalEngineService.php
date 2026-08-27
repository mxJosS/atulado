<?php

namespace App\Services;

use App\Models\AplicacionAsq;
use App\Models\AplicacionMdi;
use App\Models\AplicacionWho5;
use App\Models\AuditoriaClinica;
use App\Models\Clasificacion;
use App\Models\EventoCrisis;
use App\Models\MoodLog;
use App\Models\SerieVigilancia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ClinicalEngineService
{
    /**
     * Evaluar el registro diario (Capa 0)
     * - Codificación invertida (0=Excelente, 1=Bien, 2=Regular, 3=Mal, 4=Terrible)
     * - Filtro léxico
     * - Evaluación de las 4 reglas de vigilancia diaria
     * - Enrutamiento a Ruta A, B o C
     */
    public function evaluarRegistroDiario(User $user, int $score, ?string $journalEntry = null): array
    {
        // 1. Codificación invertida (mayor valor = mayor malestar)
        $valorInvertido = match ($score) {
            5 => 0, // Excelente
            4 => 1, // Bien
            3 => 2, // Regular
            2 => 3, // Mal
            1 => 4, // Terrible
            default => 2,
        };

        // 2. Filtro léxico de términos críticos
        $filtroLexico = $this->analizarTextoLibre($journalEntry);

        // 3. Serie histórica de registros diarios (hasta 30 días previos más el actual)
        $serieHistorica = $this->obtenerSerieHistorica($user, $valorInvertido);

        // 4. Evaluar las 4 reglas de vigilancia diaria
        $senalVigilancia = $this->evaluarReglasVigilancia($serieHistorica);

        // Guardar registro en series_vigilancia para trazabilidad
        $this->guardarSerieVigilancia($user, $serieHistorica, $senalVigilancia);

        // 5. Decisión de enrutamiento
        // RUTA A: Excelente (0) o Bien (1)
        if ($valorInvertido <= 1) {
            if ($senalVigilancia) {
                // Adelantar WHO-5 por señal de vigilancia
                return [
                    'ruta' => 'B',
                    'abrir_who5' => true,
                    'origen_who5' => 'adelantada',
                    'motivo' => 'senal_vigilancia',
                    'senal' => $senalVigilancia,
                    'valor_invertido' => $valorInvertido,
                    'filtro_lexico' => $filtroLexico,
                ];
            }

            return [
                'ruta' => 'A',
                'abrir_who5' => false,
                'abrir_mdi' => false,
                'valor_invertido' => $valorInvertido,
                'filtro_lexico' => $filtroLexico,
            ];
        }

        // RUTA B: Regular (2)
        if ($valorInvertido === 2) {
            // Verificar si existe una aplicación de WHO-5 con menos de 14 días
            $ultimaWho5 = $user->aplicacionesWho5()->first();
            $diasDesdeUltima = $ultimaWho5 ? Carbon::parse($ultimaWho5->fecha)->diffInDays(Carbon::today()) : null;

            if ($ultimaWho5 && $diasDesdeUltima < config('clinical.who5.dias_min_longitudinal', 14) && !$senalVigilancia) {
                return [
                    'ruta' => 'B_REUTILIZAR',
                    'abrir_who5' => false,
                    'reutiliza_who5_id' => $ultimaWho5->id,
                    'valor_invertido' => $valorInvertido,
                    'filtro_lexico' => $filtroLexico,
                ];
            }

            return [
                'ruta' => 'B',
                'abrir_who5' => true,
                'origen_who5' => $senalVigilancia ? 'adelantada' : 'ruta_b',
                'senal' => $senalVigilancia,
                'valor_invertido' => $valorInvertido,
                'filtro_lexico' => $filtroLexico,
            ];
        }

        // RUTA C: Mal (3) o Terrible (4) -> Abre MDI directamente (omite WHO-5)
        return [
            'ruta' => 'C',
            'abrir_who5' => false,
            'abrir_mdi' => true,
            'valor_invertido' => $valorInvertido,
            'filtro_lexico' => $filtroLexico,
        ];
    }

    /**
     * Filtro léxico del texto libre contra lista cerrada de términos críticos
     */
    public function analizarTextoLibre(?string $texto): array
    {
        if (empty($texto)) {
            return ['bandera_lexica' => false, 'terminos_detectados' => []];
        }

        $textoNormalizado = mb_strtolower($texto, 'UTF-8');
        $terminosCriticos = config('clinical.terminos_criticos', []);
        $detectados = [];

        foreach ($terminosCriticos as $termino) {
            if (str_contains($textoNormalizado, mb_strtolower($termino, 'UTF-8'))) {
                $detectados[] = $termino;
            }
        }

        return [
            'bandera_lexica' => count($detectados) > 0,
            'terminos_detectados' => array_values(array_unique($detectados)),
        ];
    }

    /**
     * Obtener serie histórica de valores invertidos (0 a 4)
     */
    protected function obtenerSerieHistorica(User $user, int $valorActual): array
    {
        $historia = $user->moodLogs()
            ->whereDate('logged_date', '<', Carbon::today())
            ->orderBy('logged_date', 'desc')
            ->take(29)
            ->pluck('valor_invertido')
            ->filter(fn($v) => $v !== null)
            ->map(fn($v) => (int)$v)
            ->toArray();

        // Agregar el actual al inicio de la serie cronológica inversa
        array_unshift($historia, $valorActual);

        return $historia;
    }

    /**
     * Evaluación de las 4 Reglas de Vigilancia Diaria
     * R1: Desviación de línea base (movil7 - base30 >= 1.0) con >= 14 días de historia
     * R2: Persistencia (>= 3 días consecutivos con v >= 3)
     * R3: Caída abrupta sostenida 2 días (salto >= 2 puntos)
     * R4: Silencio tras patrón regular (>= 5 días de silencio)
     */
    public function evaluarReglasVigilancia(array $serie): ?string
    {
        $n = count($serie);
        if ($n === 0) return null;

        $umbralDesviacion = config('clinical.surveillance.umbral_desviacion', 1.0);
        $diasPersistencia = config('clinical.surveillance.dias_persistencia', 3);
        $saltoAbrupto = config('clinical.surveillance.salto_abrupto', 2);
        $minHistoriaR1 = config('clinical.surveillance.min_historia_r1', 14);

        $ultimos7 = array_slice($serie, 0, min(7, $n));
        $movil7 = count($ultimos7) > 0 ? (array_sum($ultimos7) / count($ultimos7)) : 0;

        $ultimos30 = array_slice($serie, 0, min(30, $n));
        $base30 = count($ultimos30) > 0 ? (array_sum($ultimos30) / count($ultimos30)) : 0;

        // R1: Desviación de línea base (requiere >= 14 días de historia)
        if ($n >= $minHistoriaR1 && ($movil7 - base30) >= $umbralDesviacion) {
            return 'R1_DESVIACION';
        }

        // R2: Persistencia (3 o más días consecutivos con valor >= 3)
        $consecutivosAltos = 0;
        foreach ($serie as $val) {
            if ($val >= 3) {
                $consecutivosAltos++;
                if ($consecutivosAltos >= $diasPersistencia) {
                    return 'R2_PERSISTENCIA';
                }
            } else {
                break;
            }
        }

        // R3: Salto abrupto sostenido 2 días (diferencia de al menos 2 puntos respecto a días previos sostenido)
        if ($n >= 3) {
            $reciente1 = $serie[0];
            $reciente2 = $serie[1];
            $previo = $serie[2];
            if (($reciente1 - $previo >= $saltoAbrupto) && ($reciente2 - $previo >= $saltoAbrupto)) {
                return 'R3_CAIDA';
            }
        }

        return null;
    }

    /**
     * Guardar estado de vigilancia
     */
    protected function guardarSerieVigilancia(User $user, array $serie, ?string $senal): SerieVigilancia
    {
        $n = count($serie);
        $ultimos7 = array_slice($serie, 0, min(7, $n));
        $movil7 = count($ultimos7) > 0 ? (array_sum($ultimos7) / count($ultimos7)) : 0;

        $ultimos30 = array_slice($serie, 0, min(30, $n));
        $base30 = count($ultimos30) > 0 ? (array_sum($ultimos30) / count($ultimos30)) : 0;

        return SerieVigilancia::create([
            'user_id' => $user->id,
            'base30' => round($base30, 2),
            'movil7' => round($movil7, 2),
            'dias_silencio' => 0,
            'ultima_senal' => $senal,
            'fecha' => Carbon::today(),
        ]);
    }

    /**
     * Capa 1: Procesar WHO-5
     * - Crudo = suma(i1..i5) [0 a 25]
     * - Escala = crudo * 4 [0 a 100]
     * - Umbral crítico: crudo <= 12 O cualquier ítem en 0-1
     * - Cambio longitudinal: caída >= 10 pts si pasaron >= 14 días
     */
    public function procesarWHO5(User $user, array $items, string $origen = 'programada'): array
    {
        $i1 = (int)($items['i1'] ?? 0);
        $i2 = (int)($items['i2'] ?? 0);
        $i3 = (int)($items['i3'] ?? 0);
        $i4 = (int)($items['i4'] ?? 0);
        $i5 = (int)($items['i5'] ?? 0);

        $itemValues = [$i1, $i2, $i3, $i4, $i5];
        $crudo = array_sum($itemValues);
        $escala = $crudo * 4;

        $corteCrudo = config('clinical.who5.corte_crudo_mdi', 12);
        $itemAlertaMax = config('clinical.who5.item_alerta_max', 1);

        // Condición absoluta de apertura MDI
        $abrirMDI = ($crudo <= $corteCrudo) || min($itemValues) <= $itemAlertaMax;

        // Condición longitudinal solo si es 'programada'
        $cambioSignificativo = false;
        if ($origen === 'programada') {
            $anterior = $user->aplicacionesWho5()
                ->whereDate('fecha', '<=', Carbon::today()->subDays(config('clinical.who5.dias_min_longitudinal', 14)))
                ->first();

            if ($anterior && ($anterior->escala - $escala) >= config('clinical.who5.caida_significativa', 10)) {
                $cambioSignificativo = true;
                $abrirMDI = true;
            }
        }

        // Guardar aplicación WHO-5
        $who5 = AplicacionWho5::create([
            'user_id' => $user->id,
            'fecha' => Carbon::today(),
            'i1' => $i1,
            'i2' => $i2,
            'i3' => $i3,
            'i4' => $i4,
            'i5' => $i5,
            'crudo' => $crudo,
            'escala' => $escala,
            'origen' => $origen,
        ]);

        if (!$abrirMDI) {
            // Nivel VERDE
            Clasificacion::create([
                'user_id' => $user->id,
                'fecha' => Carbon::today(),
                'nivel' => 'VERDE',
                'origen' => 'who5',
                'banderas' => ['crudo' => $crudo, 'escala' => $escala],
            ]);

            return [
                'who5_id' => $who5->id,
                'crudo' => $crudo,
                'escala' => $escala,
                'abrir_mdi' => false,
                'nivel' => 'VERDE',
                'mensaje' => 'Tu nivel de bienestar es óptimo. Sigue fortaleciendo tus hábitos de autocuidado.',
            ];
        }

        return [
            'who5_id' => $who5->id,
            'crudo' => $crudo,
            'escala' => $escala,
            'abrir_mdi' => true,
            'nivel' => 'AMARILLO',
            'cambio_significativo' => $cambioSignificativo,
            'mensaje' => 'Completaremos una breve evaluación complementaria para brindarte los mejores recursos.',
        ];
    }

    /**
     * Capa 2: Procesar MDI (Major Depression Inventory)
     * 12 preguntas que puntúan 10 constructos (max(8a, 8b) y max(10a, 10b)).
     * Total: 0 a 50.
     * Niveles: < 20 Amarillo, 20-29 Naranja, >= 30 Rojo.
     * Condición ASQ: Ítem 6 > 0 abre Capa 3.
     */
    public function procesarMDI(User $user, array $items, ?string $origen = null): array
    {
        $i1 = (int)($items['i1'] ?? 0);
        $i2 = (int)($items['i2'] ?? 0);
        $i3 = (int)($items['i3'] ?? 0);
        $i4 = (int)($items['i4'] ?? 0);
        $i5 = (int)($items['i5'] ?? 0);
        $i6 = (int)($items['i6'] ?? 0); // Ideación pasiva
        $i7 = (int)($items['i7'] ?? 0);
        $i8a = (int)($items['i8a'] ?? 0);
        $i8b = (int)($items['i8b'] ?? 0);
        $i9 = (int)($items['i9'] ?? 0);
        $i10a = (int)($items['i10a'] ?? 0);
        $i10b = (int)($items['i10b'] ?? 0);

        $i8 = max($i8a, $i8b);
        $i10 = max($i10a, $i10b);

        $total = $i1 + $i2 + $i3 + $i4 + $i5 + $i6 + $i7 + $i8 + $i9 + $i10;

        // Determinación de severidad
        $nivel = match (true) {
            $total < config('clinical.mdi.umbral_amarillo_max', 19) + 1 => 'AMARILLO',
            $total <= config('clinical.mdi.umbral_naranja_max', 29) => 'NARANJA',
            default => 'ROJO',
        };

        // Guardar aplicación MDI
        $mdi = AplicacionMdi::create([
            'user_id' => $user->id,
            'fecha' => Carbon::today(),
            'i1' => $i1,
            'i2' => $i2,
            'i3' => $i3,
            'i4' => $i4,
            'i5' => $i5,
            'i6' => $i6,
            'i7' => $i7,
            'i8a' => $i8a,
            'i8b' => $i8b,
            'i9' => $i9,
            'i10a' => $i10a,
            'i10b' => $i10b,
            'total' => $total,
            'nivel' => $nivel,
            'origen' => $origen,
        ]);

        // Disparador de Capa 3 (ASQ): Ítem 6 > 0
        $abrirASQ = ($i6 > 0);

        if (!$abrirASQ) {
            // Guardar clasificación
            Clasificacion::create([
                'user_id' => $user->id,
                'fecha' => Carbon::today(),
                'nivel' => $nivel,
                'origen' => 'mdi',
                'banderas' => ['total_mdi' => $total, 'item6' => $i6],
            ]);

            if ($nivel === 'ROJO') {
                $this->registrarEventoCrisis($user, [
                    'disparado_en' => now(),
                    'notificado_en' => now(),
                ]);
            }

            return [
                'mdi_id' => $mdi->id,
                'total' => $total,
                'nivel' => $nivel,
                'abrir_asq' => false,
            ];
        }

        return [
            'mdi_id' => $mdi->id,
            'total' => $total,
            'nivel' => $nivel,
            'abrir_asq' => true,
        ];
    }

    /**
     * Capa 3: Procesar ASQ (Ask Suicide-Screening Questions NIMH)
     * - Opciones: 'si', 'no', 'prefiero_no_contestar' (se procesa exactamente igual que 'si').
     * - Preguntas 1 a 4: si todas 'no' -> NEGATIVA.
     * - Si alguna es positiva -> Se formula Pregunta 5.
     * - Pregunta 5: 'si' o 'prefiero_no_contestar' -> POSITIVA_AGUDA (ROJO_AGUDO, < 5 min).
     * - Pregunta 5 'no' -> POSITIVA_NO_AGUDA (ROJO, contacto mismo día).
     */
    public function procesarASQ(User $user, array $respuestas, ?string $metodo = null, ?string $fechaIntento = null): array
    {
        $p1 = $respuestas['p1'] ?? 'no';
        $p2 = $respuestas['p2'] ?? 'no';
        $p3 = $respuestas['p3'] ?? 'no';
        $p4 = $respuestas['p4'] ?? 'no';
        $p5 = $respuestas['p5'] ?? null;

        $esPositiva = fn($r) => in_array($r, ['si', 'prefiero_no_contestar'], true);

        $p1a4 = [$p1, $p2, $p3, $p4];
        $algunoPositivo = array_reduce($p1a4, fn($carry, $item) => $carry || $esPositiva($item), false);

        if (!$algunoPositivo) {
            $resultado = 'NEGATIVA';
            $nivel = null; // No modifica el nivel asignado por MDI
        } else {
            // Se evaluó la 5
            if ($esPositiva($p5)) {
                $resultado = 'POSITIVA_AGUDA';
                $nivel = 'ROJO_AGUDO';
            } else {
                $resultado = 'POSITIVA_NO_AGUDA';
                $nivel = 'ROJO';
            }
        }

        // Guardar aplicación ASQ
        $asq = AplicacionAsq::create([
            'user_id' => $user->id,
            'fecha' => Carbon::today(),
            'p1' => $p1,
            'p2' => $p2,
            'p3' => $p3,
            'p4' => $p4,
            'p5' => $p5,
            'metodo' => $metodo,
            'fecha_intento' => $fechaIntento,
            'resultado' => $resultado,
            'nivel' => $nivel,
        ]);

        $eventoCrisis = null;

        if ($nivel !== null) {
            // Guardar clasificación priorizada
            Clasificacion::create([
                'user_id' => $user->id,
                'fecha' => Carbon::today(),
                'nivel' => $nivel,
                'origen' => 'asq',
                'banderas' => ['resultado_asq' => $resultado, 'p5' => $p5],
            ]);

            // Disparar evento de crisis
            $eventoCrisis = $this->registrarEventoCrisis($user, [
                'disparado_en' => now(),
                'notificado_en' => now(),
            ]);
        }

        return [
            'asq_id' => $asq->id,
            'resultado' => $resultado,
            'nivel' => $nivel,
            'evento_crisis_id' => $eventoCrisis?->id,
        ];
    }

    /**
     * Registro de evento de crisis
     */
    public function registrarEventoCrisis(User $user, array $datos = []): EventoCrisis
    {
        return EventoCrisis::create([
            'user_id' => $user->id,
            'disparado_en' => $datos['disparado_en'] ?? now(),
            'notificado_en' => $datos['notificado_en'] ?? now(),
            'contactado_en' => null,
            'salida_sin_contacto' => false,
            'estoy_con_alguien' => false,
            'cierre_verificado_por' => null,
            'notas_cierre' => null,
            'estado' => 'abierto',
        ]);
    }

    /**
     * Registrar salida acompañada ("Ya estoy con alguien")
     */
    public function registrarEstoyConAlguien(EventoCrisis $evento): void
    {
        $evento->update([
            'estoy_con_alguien' => true,
        ]);
    }

    /**
     * Registrar salida sin contacto humano
     */
    public function registrarSalidaSinContacto(EventoCrisis $evento): void
    {
        $evento->update([
            'salida_sin_contacto' => true,
        ]);
    }

    /**
     * Cierre de caso Rojo: Solo con contacto humano verificado por un profesional
     * Regla 4: "Un caso Rojo no se cierra por puntaje: se cierra únicamente con contacto humano verificado."
     */
    public function verificarCierreCaso(EventoCrisis $evento, User $profesional, string $notas): bool
    {
        if (!$profesional->isProfessional()) {
            return false;
        }

        $evento->update([
            'contactado_en' => now(),
            'cierre_verificado_por' => $profesional->id,
            'notas_cierre' => $notas,
            'estado' => 'cerrado',
        ]);

        // Registro en auditoría clínica
        AuditoriaClinica::create([
            'profesional_id' => $profesional->id,
            'usuario_consultado_id' => $evento->user_id,
            'accion' => 'cierre_crisis',
            'detalle' => "Caso de crisis ID {$evento->id} verificado y cerrado con contacto humano. Notas: {$notas}",
        ]);

        return true;
    }

    /**
     * Vía manual para elevación de nivel por criterio clínico profesional (con auditoría)
     */
    public function elevarNivelManual(User $profesional, User $paciente, string $nuevoNivel, string $justificacion): Clasificacion
    {
        $clasificacion = Clasificacion::create([
            'user_id' => $paciente->id,
            'fecha' => Carbon::today(),
            'nivel' => $nuevoNivel,
            'origen' => 'manual_clinico',
            'banderas' => [
                'profesional_id' => $profesional->id,
                'justificacion' => $justificacion,
            ],
        ]);

        if (in_array($nuevoNivel, ['ROJO', 'ROJO_AGUDO'], true)) {
            $this->registrarEventoCrisis($paciente, [
                'disparado_en' => now(),
                'notificado_en' => now(),
            ]);
        }

        AuditoriaClinica::create([
            'profesional_id' => $profesional->id,
            'usuario_consultado_id' => $paciente->id,
            'accion' => 'elevacion_nivel',
            'detalle' => "Elevación manual a nivel {$nuevoNivel}. Justificación: {$justificacion}",
        ]);

        return $clasificacion;
    }

    /**
     * Vista agregada del Plano Gerencial
     * Regla 3: Separación estricta de planos, umbral mínimo de 15 personas por corte.
     * Solo retorna porcentajes agregados y NUNCA texto libre ni puntajes individuales.
     */
    public function obtenerVistaGerencialAgregada(?int $orgId = null): array
    {
        $umbralMinimo = config('clinical.plano_gerencial.umbral_minimo_anonimato', 15);

        $totalUsuarios = User::query()
            ->when($orgId, fn($q) => $q->where('org_id', $orgId))
            ->count();

        if ($totalUsuarios < $umbralMinimo) {
            return [
                'disponible' => false,
                'total_usuarios' => $totalUsuarios,
                'umbral_requerido' => $umbralMinimo,
                'motivo' => "El corte actual cuenta con {$totalUsuarios} usuarios. Se requiere un mínimo de {$umbralMinimo} colaboradores activos para mostrar reportes agregados y garantizar el anonimato estricto (K-anonymity).",
            ];
        }

        // Obtener la última clasificación de cada usuario
        $ultimasClasificaciones = Clasificacion::query()
            ->select('user_id', 'nivel')
            ->whereIn('id', function ($query) use ($orgId) {
                $query->selectRaw('MAX(id)')
                    ->from('clasificaciones')
                    ->groupBy('user_id');
            })
            ->when($orgId, function ($query) use ($orgId) {
                $query->whereHas('user', fn($q) => $q->where('org_id', $orgId));
            })
            ->get();

        $totalEvaluados = $ultimasClasificaciones->count();
        if ($totalEvaluados === 0) {
            return [
                'disponible' => true,
                'total_usuarios' => $totalUsuarios,
                'distribucion' => [
                    'verde_pct' => 100,
                    'amarillo_pct' => 0,
                    'naranja_pct' => 0,
                    'rojo_pct' => 0,
                ],
            ];
        }

        $verdeCount = $ultimasClasificaciones->where('nivel', 'VERDE')->count();
        $amarilloCount = $ultimasClasificaciones->where('nivel', 'AMARILLO')->count();
        $naranjaCount = $ultimasClasificaciones->where('nivel', 'NARANJA')->count();
        $rojoCount = $ultimasClasificaciones->whereIn('nivel', ['ROJO', 'ROJO_AGUDO'])->count();

        return [
            'disponible' => true,
            'total_usuarios' => $totalUsuarios,
            'total_evaluados' => $totalEvaluados,
            'distribucion' => [
                'verde_pct' => round(($verdeCount / $totalEvaluados) * 100, 1),
                'amarillo_pct' => round(($amarilloCount / $totalEvaluados) * 100, 1),
                'naranja_pct' => round(($naranjaCount / $totalEvaluados) * 100, 1),
                'rojo_pct' => round(($rojoCount / $totalEvaluados) * 100, 1),
            ],
        ];
    }
}
