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
            'safe_environment_steps' => ['nullable', 'string', 'max:2000'],
            'reasons_for_living' => ['nullable', 'string', 'max:2000'],
        ]);

        // Filter empty elements
        $cleanWarningSigns = array_values(array_filter($validated['warning_signs'] ?? [], fn($v) => !empty(trim($v))));
        $cleanInternalCoping = array_values(array_filter($validated['internal_coping'] ?? [], fn($v) => !empty(trim($v))));
        $cleanDistractions = array_values(array_filter($validated['distraction_activities'] ?? [], fn($v) => !empty(trim($v))));
        
        $cleanTrusted = array_values(array_filter($validated['trusted_contacts'] ?? [], fn($c) => !empty(trim($c['name'] ?? ''))));
        $cleanPro = array_values(array_filter($validated['professional_contacts'] ?? [], fn($c) => !empty(trim($c['name'] ?? ''))));

        $safetyPlan = SafetyPlan::updateOrCreate(
            ['user_id' => $user->id],
            [
                'warning_signs' => $cleanWarningSigns,
                'internal_coping' => $cleanInternalCoping,
                'distraction_activities' => $cleanDistractions,
                'trusted_contacts' => $cleanTrusted,
                'professional_contacts' => $cleanPro,
                'safe_environment_steps' => $validated['safe_environment_steps'] ?? null,
                'reasons_for_living' => $validated['reasons_for_living'] ?? null,
            ]
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
