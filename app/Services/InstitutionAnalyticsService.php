<?php

namespace App\Services;

use App\Models\AplicacionWho5;
use App\Models\Clasificacion;
use App\Models\EventoCrisis;
use App\Models\Institution;
use App\Models\MoodLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InstitutionAnalyticsService
{
    /**
     * Calcula métricas reales y dinámicas para una institución a partir de
     * sus colaboradores, registros de ánimo (mood_logs), WHO-5 y eventos de crisis.
     */
    public function calculateForInstitution(Institution $institution): array
    {
        $users = User::where('institution_id', $institution->id)->get();
        $userIds = $users->pluck('id');

        $totalUsers = $users->count();

        // 1. Usuarios activos (con al menos un registro en los últimos 30 días)
        $activeUserIds = MoodLog::whereIn('user_id', $userIds)
            ->where('logged_date', '>=', Carbon::now()->subDays(30)->format('Y-m-d'))
            ->distinct()
            ->pluck('user_id');

        $activeCount = $activeUserIds->count();
        $adoptionRate = $totalUsers > 0 ? round(($activeCount / $totalUsers) * 100, 1) : 0.0;

        // 2. Semáforo Global (Último estado o clasificación de cada usuario)
        $globalDist = $this->calculateDistributionForUsers($users);

        // 3. Eventos de crisis abiertos
        $crisisEventsQuery = EventoCrisis::whereIn('user_id', $userIds);
        $totalCrisis = (clone $crisisEventsQuery)->count();
        $openCrisisCount = (clone $crisisEventsQuery)->where('estado', '!=', 'cerrado')->count();

        // Nivel de alerta global
        $alertLevel = 'verde';
        if ($openCrisisCount > 0 || $globalDist['rojo'] > 0) {
            $alertLevel = 'rojo';
        } elseif ($globalDist['naranja'] > 0) {
            $alertLevel = 'naranja';
        } elseif ($globalDist['amarillo'] > 0) {
            $alertLevel = 'amarillo';
        }

        // 4. Estructura de Macro-Grupos y Departamentos
        $groupsStructure = $this->calculateMacroGroupsAndDepartments($institution, $users);

        // 5. IBI (Índice de Bienestar Institucional 0-100)
        $who5Avg = $this->calculateAverageWho5($userIds);
        $adherenceRate = $this->calculateAdherenceRate($userIds, $activeCount, 14);

        if ($totalUsers > 0) {
            $favorableSemaforo = $globalDist['verde'] + ($globalDist['amarillo'] * 0.7);
            $ibi = round(
                ($who5Avg * 0.40) +
                ($favorableSemaforo * 0.30) +
                ($adherenceRate * 0.20) +
                (min(100, $adoptionRate) * 0.10),
                1
            );
        } else {
            $ibi = 0.0;
        }

        // 6. IRC (Concentración del Riesgo en cuadrillas específicas)
        $irc = $this->calculateIrc($groupsStructure, $openCrisisCount + $globalDist['rojo_count']);

        // 7. IRO (Respuesta Operativa ante crisis)
        $iro = $totalCrisis > 0
            ? round((((clone $crisisEventsQuery)->whereNotNull('contactado_en')->count()) / $totalCrisis) * 100, 1)
            : 0.0;

        return [
            'total_users' => $totalUsers,
            'active_count' => $activeCount > 0 ? $activeCount : $totalUsers,
            'adoption_rate' => $totalUsers > 0 ? $adoptionRate : 0.0,
            'alert_level' => $alertLevel,
            'critical_count' => $openCrisisCount,
            'distribution' => $globalDist,
            'groups' => $groupsStructure,
            'ibi' => $ibi,
            'irc' => $irc,
            'iro' => $iro,
            'who5_avg' => round($who5Avg),
            'adherence' => round($adherenceRate),
        ];
    }

    /**
     * Calcula distribución porcentual del semáforo clínico para una colección de usuarios
     */
    public function calculateDistributionForUsers(Collection $users): array
    {
        $total = $users->count();
        if ($total === 0) {
            return [
                'verde' => 0,
                'amarillo' => 0,
                'naranja' => 0,
                'rojo' => 0,
                'verde_count' => 0,
                'amarillo_count' => 0,
                'naranja_count' => 0,
                'rojo_count' => 0,
                'total_evaluados' => 0,
            ];
        }

        $counts = ['VERDE' => 0, 'AMARILLO' => 0, 'NARANJA' => 0, 'ROJO' => 0];

        foreach ($users as $user) {
            $level = $this->resolveUserLevel($user);
            if (isset($counts[$level])) {
                $counts[$level]++;
            } else {
                $counts['VERDE']++;
            }
        }

        $vPct = round(($counts['VERDE'] / $total) * 100);
        $aPct = round(($counts['AMARILLO'] / $total) * 100);
        $nPct = round(($counts['NARANJA'] / $total) * 100);
        $rPct = max(0, 100 - ($vPct + $aPct + $nPct)); // Asegurar suma exacta de 100%

        return [
            'verde' => (int) $vPct,
            'amarillo' => (int) $aPct,
            'naranja' => (int) $nPct,
            'rojo' => (int) $rPct,
            'verde_count' => $counts['VERDE'],
            'amarillo_count' => $counts['AMARILLO'],
            'naranja_count' => $counts['NARANJA'],
            'rojo_count' => $counts['ROJO'],
            'total_evaluados' => $total,
        ];
    }

    /**
     * Resuelve el nivel actual del semáforo para un usuario (Clasificación clínica o formulario de ánimo)
     */
    private function resolveUserLevel(User $user): string
    {
        // 1. Revisar clasificación clínica más reciente
        $clasificacion = Clasificacion::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($clasificacion) {
            if (in_array($clasificacion->nivel, ['ROJO', 'ROJO_AGUDO'])) {
                return 'ROJO';
            }
            return strtoupper($clasificacion->nivel);
        }

        // 2. Si no tiene clasificación clínica formal, evaluar su último formulario de sentimiento (mood_log)
        $lastLog = MoodLog::where('user_id', $user->id)
            ->orderBy('logged_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastLog) {
            if ($lastLog->bandera_lexica || $lastLog->score === 1) {
                return 'ROJO';
            }
            if ($lastLog->score === 2) {
                return 'NARANJA';
            }
            if ($lastLog->score === 3) {
                return 'AMARILLO';
            }
            return 'VERDE';
        }

        return 'VERDE';
    }

    /**
     * Construye y calcula los macro-grupos y cuadrillas a partir de los datos en DB
     */
    private function calculateMacroGroupsAndDepartments(Institution $institution, Collection $users): array
    {
        $rawGroups = $institution->departments_data;

        // Si no tiene estructura en JSON, generar grupos a partir de los macro_group de los usuarios
        if (empty($rawGroups)) {
            $uniqueGroups = $users->pluck('macro_group')->filter()->unique();
            if ($uniqueGroups->isEmpty()) {
                $uniqueGroups = collect(['Operaciones y Frente de Obra', 'Corporativo, Comercial y Dirección']);
            }

            $rawGroups = [];
            foreach ($uniqueGroups as $gName) {
                $deptNames = $users->where('macro_group', $gName)->pluck('department')->filter()->unique();
                if ($deptNames->isEmpty()) {
                    $deptNames = collect(['Área General']);
                }

                $rawGroups[] = [
                    'id' => \Illuminate\Support\Str::slug($gName),
                    'name' => $gName,
                    'badge' => 'Estable',
                    'badge_color' => '#1E8449',
                    'departments' => $deptNames->map(fn($d) => [
                        'name' => $d,
                        'code' => \Illuminate\Support\Str::slug($d),
                        'note' => 'Personal asignado',
                    ])->toArray()
                ];
            }
        }

        $calculatedGroups = [];
        $anyUserHasGroup = $users->contains(fn($u) => !empty($u->macro_group));

        foreach ($rawGroups as $group) {
            $gName = $group['name'] ?? ($group['macro_group'] ?? 'General');
            if ($anyUserHasGroup) {
                $groupUsers = $users->filter(fn($u) => trim(mb_strtolower($u->macro_group ?? '')) === trim(mb_strtolower($gName)));
            } else {
                $groupUsers = $users;
            }

            $gTotal = $groupUsers->count();
            $gDist = $this->calculateDistributionForUsers($groupUsers);

            $gAlerts = EventoCrisis::whereIn('user_id', $groupUsers->pluck('id'))
                ->where('estado', '!=', 'cerrado')
                ->count();
            if ($gDist['rojo_count'] > 0) {
                $gAlerts = max($gAlerts, $gDist['rojo_count']);
            }

            $badge = 'Estable';
            $badgeColor = '#1E8449';
            if ($gAlerts > 0) {
                $badge = "{$gAlerts} " . ($gAlerts === 1 ? 'alerta activa' : 'alertas activas');
                $badgeColor = '#B02418';
            } elseif ($gDist['naranja_count'] > 0) {
                $badge = 'En observación';
                $badgeColor = '#D9660F';
            }

            // Calcular cada departamento dentro del macro-grupo
            $calculatedDepartments = [];
            $deptList = $group['departments'] ?? [];
            $anyUserHasDept = $groupUsers->contains(fn($u) => !empty($u->department));

            foreach ($deptList as $dept) {
                $dName = $dept['name'] ?? 'Área General';
                if ($anyUserHasDept) {
                    $deptUsers = $groupUsers->filter(fn($u) => trim(mb_strtolower($u->department ?? '')) === trim(mb_strtolower($dName)));
                } else {
                    $deptUsers = $groupUsers;
                }
                $dTotal = $deptUsers->count();

                $dDist = $this->calculateDistributionForUsers($deptUsers);
                $dWho5 = $dTotal > 0 ? $this->calculateAverageWho5($deptUsers->pluck('id')) : 0.0;
                $dAdherence = $dTotal > 0 ? $this->calculateAdherenceRate($deptUsers->pluck('id'), $dTotal, 14) : 0.0;

                $dAlerts = EventoCrisis::whereIn('user_id', $deptUsers->pluck('id'))
                    ->where('estado', '!=', 'cerrado')
                    ->count();
                if ($dDist['rojo_count'] > 0) {
                    $dAlerts = max($dAlerts, $dDist['rojo_count']);
                }

                $calculatedDepartments[] = [
                    'name' => $dName,
                    'code' => $dept['code'] ?? \Illuminate\Support\Str::slug($dName),
                    'active' => $dTotal,
                    'dist' => [
                        'verde' => $dDist['verde'],
                        'amarillo' => $dDist['amarillo'],
                        'naranja' => $dDist['naranja'],
                        'rojo' => $dDist['rojo'],
                    ],
                    'who5' => round($dWho5),
                    'adherence' => round($dAdherence) . '%',
                    'alerts' => $dAlerts,
                    'alert_type' => $dAlerts > 0 ? 'rojo' : ($dDist['naranja'] > 0 ? 'naranja' : 'verde'),
                    'note' => $dept['note'] ?? ($dAlerts > 0 ? "Concentra {$dAlerts} alertas prioritarias." : ($dTotal > 0 ? 'Participación normal.' : 'Sin colaboradores asignados aún.')),
                ];
            }

            $calculatedGroups[] = [
                'id' => $group['id'] ?? \Illuminate\Support\Str::slug($gName),
                'name' => $gName,
                'badge' => $badge,
                'badge_color' => $badgeColor,
                'active_count' => $gTotal,
                'who5_avg' => round($this->calculateAverageWho5($groupUsers->pluck('id'))),
                'adherence_avg' => round($this->calculateAdherenceRate($groupUsers->pluck('id'), $gTotal, 14)),
                'alerts_count' => $gAlerts,
                'distribution' => [
                    'verde' => $gDist['verde'],
                    'amarillo' => $gDist['amarillo'],
                    'naranja' => $gDist['naranja'],
                    'rojo' => $gDist['rojo'],
                ],
                'departments' => $calculatedDepartments,
            ];
        }

        return $calculatedGroups;
    }

    /**
     * Promedio de WHO-5 o fallback desde formularios de ánimo
     */
    private function calculateAverageWho5(Collection $userIds): float
    {
        if ($userIds->isEmpty()) {
            return 0.0;
        }

        $who5Avg = AplicacionWho5::whereIn('user_id', $userIds)->avg('escala');
        if ($who5Avg && $who5Avg > 0) {
            return (float) $who5Avg;
        }

        // Fallback desde mood_logs: score de 1 a 5 mapeado a 0-100 (score * 20)
        $moodAvg = MoodLog::whereIn('user_id', $userIds)->avg('score');
        if ($moodAvg && $moodAvg > 0) {
            return (float) ($moodAvg * 20);
        }

        return 0.0;
    }

    /**
     * Tasa de adherencia: días con registro vs esperados en una ventana
     */
    private function calculateAdherenceRate(Collection $userIds, int $totalUsers, int $daysWindow = 14): float
    {
        if ($totalUsers === 0 || $userIds->isEmpty()) {
            return 0.0;
        }

        $totalExpected = $totalUsers * $daysWindow;
        $actualLogs = MoodLog::whereIn('user_id', $userIds)
            ->where('logged_date', '>=', Carbon::now()->subDays($daysWindow)->format('Y-m-d'))
            ->count();

        if ($totalExpected <= 0) {
            return 0.0;
        }

        return min(100.0, round(($actualLogs / $totalExpected) * 100, 1));
    }

    /**
     * IRC: Índice de Riesgo Concentrado
     */
    private function calculateIrc(array $groups, int $totalAlerts): int
    {
        if ($totalAlerts === 0) {
            return 15;
        }

        $maxDepartmentAlerts = 0;
        foreach ($groups as $g) {
            foreach ($g['departments'] as $d) {
                if ($d['alerts'] > $maxDepartmentAlerts) {
                    $maxDepartmentAlerts = $d['alerts'];
                }
            }
        }

        if ($maxDepartmentAlerts === 0) {
            return 20;
        }

        return (int) min(100, round(($maxDepartmentAlerts / $totalAlerts) * 100));
    }
}
