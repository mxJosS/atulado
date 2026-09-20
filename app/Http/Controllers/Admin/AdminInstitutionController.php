<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AplicacionWho5;
use App\Models\EventoCrisis;
use App\Models\Institution;
use App\Models\MoodLog;
use App\Models\User;
use App\Services\InstitutionAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminInstitutionController extends Controller
{
    /**
     * Operación de plataforma / Listado de Instituciones B2B
     */
    public function index(InstitutionAnalyticsService $analyticsService)
    {
        $allDbInstitutions = Institution::orderBy('created_at', 'desc')->get();

        $institutions = $allDbInstitutions->map(function ($inst) use ($analyticsService) {
            $userCount = $inst->users()->count();
            if ($userCount > 0) {
                $calc = $analyticsService->calculateForInstitution($inst);
                $usersCount = $calc['total_users'];
                $activeCount = $calc['active_count'];
                $adoptionRate = $calc['adoption_rate'];
                $alertLevel = $calc['alert_level'];
                $criticalCount = $calc['critical_count'];
            } else {
                $usersCount = $inst->users_count ?: 0;
                $activeCount = $inst->active_count ?: 0;
                $adoptionRate = $inst->adoption_rate ?: 0.0;
                $alertLevel = $inst->alert_level ?: 'verde';
                $criticalCount = $inst->critical_count ?: 0;
            }

            return [
                'id' => $inst->slug,
                'name' => $inst->short_name ?: $inst->name,
                'full_name' => $inst->name,
                'category' => $inst->category,
                'city' => $inst->city ?: 'Mérida, Yuc.',
                'contact' => $inst->contact_name,
                'phone' => $inst->contact_phone,
                'email' => $inst->contact_email,
                'users_count' => $usersCount,
                'active_count' => $activeCount,
                'adoption_rate' => $adoptionRate,
                'alert_level' => $alertLevel,
                'critical_count' => $criticalCount,
                'plan' => $inst->plan,
                'renewal_date' => $inst->renewal_date ? $inst->renewal_date->format('d/m/Y') : 'Por definir',
            ];
        })->toArray();

        // Métricas reales calculadas de la plataforma (limpias cuando inicia en 0)
        $totalInst = count($institutions);
        $totalRegistered = (int) collect($institutions)->sum('users_count');
        $totalActive = (int) collect($institutions)->sum('active_count');
        $globalAdoption = $totalRegistered > 0 ? round(($totalActive / $totalRegistered) * 100, 1) : 0.0;

        $b2bUserIds = User::whereNotNull('institution_id')->pluck('id');
        $adherenceRate = 0.0;
        if ($b2bUserIds->isNotEmpty() && $totalActive > 0) {
            $recentLogs = MoodLog::whereIn('user_id', $b2bUserIds)
                ->where('logged_date', '>=', now()->subDays(14)->format('Y-m-d'))
                ->count();
            $expected = $totalActive * 14;
            $adherenceRate = $expected > 0 ? min(100.0, round(($recentLogs / $expected) * 100, 1)) : 0.0;
        }

        $dau = $b2bUserIds->isNotEmpty()
            ? MoodLog::whereIn('user_id', $b2bUserIds)->where('logged_date', now()->format('Y-m-d'))->distinct('user_id')->count('user_id')
            : 0;
        $mau = $b2bUserIds->isNotEmpty()
            ? MoodLog::whereIn('user_id', $b2bUserIds)->where('logged_date', '>=', now()->subDays(30)->format('Y-m-d'))->distinct('user_id')->count('user_id')
            : 0;
        $arraigo = $mau > 0 ? round($dau / $mau, 2) : 0.0;

        $platformMetrics = [
            'total_institutions' => $totalInst,
            'total_registered' => $totalRegistered,
            'total_active' => $totalActive,
            'adoption_rate' => $globalAdoption,
            'adherence_rate' => $adherenceRate,
            'arraigo' => $arraigo,
        ];

        return view('admin.institutions.index', compact('institutions', 'platformMetrics'));
    }

    /**
     * Registrar una nueva institución con sus macro-grupos y profesional asignado
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'rfc' => 'nullable|string|max:20',
            'category' => 'required|string|max:150',
            'city' => 'nullable|string|max:150',
            'contact_name' => 'required|string|max:255',
            'contact_position' => 'nullable|string|max:150',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'professional_name' => 'required|string|max:255',
            'professional_license' => 'nullable|string|max:50',
            'plan' => 'required|string|max:100',
            'renewal_date' => 'nullable|date',
            'users_count' => 'nullable|integer|min:0',
            'macro_groups' => 'nullable|string',
        ]);

        // Generar slug único
        $baseSlug = Str::slug($validated['short_name'] ?: $validated['name']);
        if (empty($baseSlug)) {
            $baseSlug = 'institucion';
        }
        $slug = $baseSlug;
        $counter = 1;
        while (Institution::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Estructura de macro-grupos inicial
        $rawGroups = $request->input('macro_groups', 'Operaciones y Campo, Corporativo y Administración');
        $groupNames = array_filter(array_map('trim', explode(',', $rawGroups)));
        if (empty($groupNames)) {
            $groupNames = ['Operaciones y Campo', 'Corporativo y Administración'];
        }

        $departmentsData = [];
        foreach ($groupNames as $gName) {
            $gSlug = Str::slug($gName);
            $departmentsData[] = [
                'id' => $gSlug,
                'name' => $gName,
                'badge' => 'Estable',
                'badge_color' => '#1E8449',
                'active_count' => 0,
                'who5_avg' => 0,
                'adherence_avg' => 0,
                'alerts_count' => 0,
                'distribution' => ['verde' => 0, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                'departments' => [
                    [
                        'name' => 'Área General · ' . $gName,
                        'code' => $gSlug . '-gral',
                        'active' => 0,
                        'dist' => ['verde' => 0, 'amarillo' => 0, 'naranja' => 0, 'rojo' => 0],
                        'who5' => 0,
                        'adherence' => '0%',
                        'alerts' => 0,
                        'alert_type' => 'verde',
                        'note' => 'Grupo inicial configurado.',
                    ]
                ]
            ];
        }

        $usersCount = $validated['users_count'] ?? 0;

        Institution::create([
            'slug' => $slug,
            'name' => $validated['name'],
            'short_name' => $validated['short_name'] ?: $validated['name'],
            'rfc' => $validated['rfc'] ?? null,
            'category' => $validated['category'],
            'city' => $validated['city'] ?? 'Mérida, Yucatán',
            'contact_name' => $validated['contact_name'],
            'contact_position' => $validated['contact_position'] ?? 'Recursos Humanos',
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'professional_name' => $validated['professional_name'],
            'professional_license' => $validated['professional_license'] ?? null,
            'professional_contract_status' => 'vigente',
            'plan' => $validated['plan'],
            'users_count' => $usersCount,
            'active_count' => 0,
            'adoption_rate' => 0.0,
            'alert_level' => 'verde',
            'critical_count' => 0,
            'renewal_date' => $validated['renewal_date'] ?? null,
            'departments_data' => $departmentsData,
        ]);

        return redirect()->route('admin.institutions.index')
            ->with('success', '¡La institución "' . $validated['name'] . '" ha sido dada de alta exitosamente con sus macro-grupos operativos!');
    }

    /**
     * Detalle institucional con semáforo por grupos y departamentos
     */
    public function show(?string $slug = null, ?InstitutionAnalyticsService $analyticsService = null)
    {
        $analyticsService = $analyticsService ?: app(InstitutionAnalyticsService::class);

        $dbInst = null;
        if ($slug) {
            $dbInst = Institution::where('slug', $slug)->first();
        }

        if (!$dbInst) {
            $dbInst = Institution::first();
        }

        $allInstitutions = Institution::select('slug', 'name', 'short_name')->orderBy('name')->get();

        if (!$dbInst) {
            return view('admin.institutions.show', [
                'inst' => null,
                'groups' => [],
                'distribution' => [
                    'verde' => 0,
                    'amarillo' => 0,
                    'naranja' => 0,
                    'rojo' => 0,
                    'total_evaluados' => 0,
                ],
                'crisisQueue' => collect(),
                'allInstitutions' => $allInstitutions,
                'slug' => '',
            ]);
        }

        $calc = $analyticsService->calculateForInstitution($dbInst);

        $userIds = $dbInst->users()->pluck('id');
        $crisisQueue = EventoCrisis::whereIn('user_id', $userIds)
            ->with('user')
            ->where('estado', '!=', 'cerrado')
            ->orderBy('disparado_en', 'desc')
            ->take(5)
            ->get();

        $inst = [
            'id' => $dbInst->slug,
            'name' => $dbInst->name,
            'short' => $dbInst->short_name ?: $dbInst->name,
            'giro' => $dbInst->category,
            'contacto' => $dbInst->contact_name . ($dbInst->contact_position ? ' — ' . $dbInst->contact_position : ''),
            'email' => $dbInst->contact_email,
            'telefono' => $dbInst->contact_phone ?: 'No registrado',
            'profesional' => $dbInst->professional_name,
            'cedula' => $dbInst->professional_license ?: 'En trámite',
            'padron_total' => $calc['total_users'] > 0 ? $calc['total_users'] : ($dbInst->users_count ?: 0),
            'padron_activo' => $calc['active_count'] > 0 ? $calc['active_count'] : 0,
            'adoption_rate' => $calc['adoption_rate'] > 0 ? $calc['adoption_rate'] : 0.0,
            'ibi' => $calc['total_users'] > 0 ? $calc['ibi'] : 0.0,
            'ibi_delta' => '+0.0',
            'adherencia' => $calc['adherence'] > 0 ? $calc['adherence'] : 0,
            'sedes' => [$dbInst->city ?: 'Sede Central'],
        ];

        $groups = $calc['groups'];
        $distribution = $calc['distribution'];
        $slug = $dbInst->slug;

        return view('admin.institutions.show', compact('inst', 'groups', 'distribution', 'crisisQueue', 'allInstitutions', 'slug'));
    }

    /**
     * Analítica e Índices NOM-035 / IBI
     */
    public function analytics(?InstitutionAnalyticsService $analyticsService = null)
    {
        $analyticsService = $analyticsService ?: app(InstitutionAnalyticsService::class);

        $b2bUserIds = User::whereNotNull('institution_id')->pluck('id');
        $totalUsers = $b2bUserIds->count();
        $activeUsers30d = $b2bUserIds->isNotEmpty()
            ? MoodLog::whereIn('user_id', $b2bUserIds)->where('logged_date', '>=', now()->subDays(30)->format('Y-m-d'))->distinct('user_id')->count('user_id')
            : 0;
        $totalMoodLogs = $b2bUserIds->isNotEmpty() ? MoodLog::whereIn('user_id', $b2bUserIds)->count() : 0;
        $totalWho5 = $b2bUserIds->isNotEmpty() ? AplicacionWho5::whereIn('user_id', $b2bUserIds)->count() : 0;
        $totalCrisis = $b2bUserIds->isNotEmpty() ? EventoCrisis::whereIn('user_id', $b2bUserIds)->count() : 0;
        $openCrisis = $b2bUserIds->isNotEmpty() ? EventoCrisis::whereIn('user_id', $b2bUserIds)->where('estado', '!=', 'cerrado')->count() : 0;

        $institutions = Institution::all();
        $ibiSum = 0;
        $ircSum = 0;
        $iroSum = 0;
        $instCount = $institutions->count();

        foreach ($institutions as $inst) {
            $calc = $analyticsService->calculateForInstitution($inst);
            $ibiSum += $calc['ibi'];
            $ircSum += $calc['irc'];
            $iroSum += $calc['iro'];
        }

        $globalIbi = ($instCount > 0 && $totalUsers > 0) ? round($ibiSum / $instCount, 1) : 0.0;
        $globalIrc = ($instCount > 0 && $totalCrisis > 0) ? (int) round($ircSum / $instCount) : 0;
        $globalIro = ($instCount > 0 && $totalCrisis > 0) ? (int) round($iroSum / $instCount) : 0;

        $moduleStats = [
            'mood_logs' => $totalMoodLogs,
            'who5_tests' => $totalWho5,
            'crisis_calls' => $totalCrisis,
            'open_crisis' => $openCrisis,
            'active_users' => $activeUsers30d,
            'total_users' => $totalUsers,
        ];

        return view('admin.institutions.analytics', compact('globalIbi', 'globalIrc', 'globalIro', 'moduleStats'));
    }

    /**
     * Altas y Estructura Organizacional
     */
    public function structure(Request $request)
    {
        $institutions = Institution::withCount('users')->orderBy('name')->get();
        $slug = $request->query('inst');
        $selectedInst = null;
        if ($slug) {
            $selectedInst = $institutions->firstWhere('slug', $slug);
        }
        if (!$selectedInst) {
            $selectedInst = $institutions->first();
        }

        return view('admin.institutions.structure', compact('institutions', 'selectedInst'));
    }

    /**
     * Vista Cliente / Demo Panel Institucional
     */
    public function clientView(Request $request, ?InstitutionAnalyticsService $analyticsService = null)
    {
        $analyticsService = $analyticsService ?: app(InstitutionAnalyticsService::class);

        $slug = $request->query('inst');
        $inst = null;
        if ($slug) {
            $inst = Institution::where('slug', $slug)->first();
        }
        if (!$inst) {
            $inst = Institution::first();
        }

        $calc = $inst ? $analyticsService->calculateForInstitution($inst) : null;
        $allInstitutions = Institution::select('slug', 'name', 'short_name')->orderBy('name')->get();
        $slug = $inst ? $inst->slug : '';

        return view('admin.institutions.client-view', compact('inst', 'calc', 'allInstitutions', 'slug'));
    }
}
