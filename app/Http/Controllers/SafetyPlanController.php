<?php

namespace App\Http\Controllers;

use App\Models\CrisisLine;
use App\Models\SafetyPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SafetyPlanController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $safetyPlan = $user->safetyPlan ?? new SafetyPlan(['user_id' => $user->id]);
        $crisisLines = CrisisLine::where('is_featured', true)->take(4)->get();

        return view('dashboard.safety-plan', compact('safetyPlan', 'crisisLines'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'warning_signs' => ['nullable', 'array'],
            'warning_signs.*' => ['nullable', 'string', 'max:255'],
            'internal_coping' => ['nullable', 'array'],
            'internal_coping.*' => ['nullable', 'string', 'max:255'],
            'social_distractions' => ['nullable', 'array'],
            'social_distractions.*' => ['nullable', 'string', 'max:255'],
            'safe_places' => ['nullable', 'array'],
            'safe_places.*' => ['nullable', 'string', 'max:255'],
            'distraction_activities' => ['nullable', 'array'],
            'distraction_activities.*' => ['nullable', 'string', 'max:255'],
            'trusted_contacts' => ['nullable', 'array'],
            'trusted_contacts.*.name' => ['nullable', 'string', 'max:100'],
            'trusted_contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'trusted_contacts.*.relationship' => ['nullable', 'string', 'max:100'],
            'professional_contacts' => ['nullable', 'array'],
            'professional_contacts.*.name' => ['nullable', 'string', 'max:100'],
            'professional_contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'professional_contacts.*.note' => ['nullable', 'string', 'max:150'],
            'reasons_to_live' => ['nullable', 'array'],
            'reasons_to_live.*' => ['nullable', 'string', 'max:255'],
            'safe_environment_steps' => ['nullable', 'string', 'max:2000'],
            'reasons_for_living' => ['nullable', 'string', 'max:2000'],
        ]);

        // Filter empty elements
        $cleanWarningSigns = array_values(array_filter($validated['warning_signs'] ?? [], fn($v) => !empty(trim($v))));
        $cleanInternalCoping = array_values(array_filter($validated['internal_coping'] ?? [], fn($v) => !empty(trim($v))));
        $cleanSocialDistractions = array_values(array_filter($validated['social_distractions'] ?? [], fn($v) => !empty(trim($v))));
        $cleanSafePlaces = array_values(array_filter($validated['safe_places'] ?? [], fn($v) => !empty(trim($v))));
        $cleanReasonsToLive = array_values(array_filter($validated['reasons_to_live'] ?? [], fn($v) => !empty(trim($v))));

        $cleanDistractions = !empty($cleanSocialDistractions) || !empty($cleanSafePlaces)
            ? array_merge($cleanSocialDistractions, $cleanSafePlaces)
            : array_values(array_filter($validated['distraction_activities'] ?? [], fn($v) => !empty(trim($v))));

        $reasonsForLivingText = !empty($cleanReasonsToLive)
            ? implode("\n", $cleanReasonsToLive)
            : ($validated['reasons_for_living'] ?? null);

        $cleanTrusted = array_values(array_filter($validated['trusted_contacts'] ?? [], fn($c) => !empty(trim($c['name'] ?? ''))));
        $cleanPro = array_values(array_filter($validated['professional_contacts'] ?? [], fn($c) => !empty(trim($c['name'] ?? ''))));

        $dataToSave = [
            'warning_signs' => $cleanWarningSigns,
            'internal_coping' => $cleanInternalCoping,
            'distraction_activities' => $cleanDistractions,
            'trusted_contacts' => $cleanTrusted,
            'professional_contacts' => $cleanPro,
            'safe_environment_steps' => $validated['safe_environment_steps'] ?? null,
            'reasons_for_living' => $reasonsForLivingText,
        ];

        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('safety_plans', 'social_distractions')) {
                $dataToSave['social_distractions'] = $cleanSocialDistractions;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('safety_plans', 'safe_places')) {
                $dataToSave['safe_places'] = $cleanSafePlaces;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('safety_plans', 'reasons_to_live')) {
                $dataToSave['reasons_to_live'] = $cleanReasonsToLive;
            }
        } catch (\Throwable $e) {
            // Si la conexión no permite schema inspection, continuar con campos base
        }

        $safetyPlan = SafetyPlan::updateOrCreate(
            ['user_id' => $user->id],
            $dataToSave
        );

        return back()->with('success', '¡Tu Plan de Seguridad Personal ha sido actualizado y guardado!');
    }

    public function printView()
    {
        $user = Auth::user();
        $safetyPlan = $user->safetyPlan;

        if (!$safetyPlan) {
            return redirect()->route('safety-plan.show')->with('info', 'Primero completa algunos datos de tu plan para imprimirlo.');
        }

        $crisisLines = CrisisLine::where('is_featured', true)->get();

        return view('dashboard.safety-plan-print', compact('user', 'safetyPlan', 'crisisLines'));
    }
}
