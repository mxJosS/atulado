<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Services\InstitutionAnalyticsService;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    /**
     * Centro de Reportes: Catálogo de emisión e Historial
     */
    public function index()
    {
        $institutionsList = Institution::orderBy('name')->get();
        $institutions = $institutionsList->pluck('name', 'slug')->toArray();
        $institutionsData = $institutionsList->keyBy('slug')->map(function ($i) {
            return [
                'nombre' => $i->name,
                'corto' => $i->short_name ?: $i->name,
                'rfc' => $i->rfc ?: 'RFC NO REGISTRADO',
                'sector' => $i->category ?: 'General',
                'ciudad' => $i->city ?: '',
                'padron' => $i->users()->count(),
                'activos' => $i->users()->count(),
                'contacto' => $i->contact_name . ($i->contact_position ? ' — ' . $i->contact_position : ''),
                'profesional' => $i->professional_name ?: 'Sin profesional asignado',
                'cedula' => $i->professional_license ?: 'En trámite',
                'vigencia' => $i->renewal_date ? $i->renewal_date->format('d/m/Y') : '14/02/2027',
                'alta' => $i->created_at ? $i->created_at->format('d/m/Y') : now()->format('d/m/Y'),
            ];
        })->toArray();

        return view('admin.reports.index', compact('institutions', 'institutionsData'));
    }

    /**
     * Visor Paginado de Reporte Ejecutivo / Clínico / NOM-035 (Listo para imprimir o PDF)
     */
    public function viewer(Request $request, ?InstitutionAnalyticsService $analyticsService = null)
    {
        $analyticsService = $analyticsService ?: app(InstitutionAnalyticsService::class);
        $reportType = $request->query('r', 'ejecutivo');
        $institutionId = $request->query('inst');
        $period = $request->query('periodo', now()->subDays(90)->format('d/m/Y') . ' – ' . now()->format('d/m/Y'));
        $dest = $request->query('dest', 'contacto');
        $folio = $request->query('folio', 'RE-' . now()->year . '-' . rand(1000, 9999));

        $institution = null;
        if ($institutionId) {
            $institution = Institution::where('slug', $institutionId)->first();
        }
        if (!$institution) {
            $institution = Institution::first();
        }

        $institutionId = $institution ? $institution->slug : '';
        $analytics = $institution ? $analyticsService->calculateForInstitution($institution) : null;

        return view('admin.reports.viewer', compact(
            'reportType',
            'institutionId',
            'period',
            'dest',
            'folio',
            'institution',
            'analytics'
        ));
    }
}
