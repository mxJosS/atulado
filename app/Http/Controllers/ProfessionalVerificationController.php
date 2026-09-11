<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfessionalVerificationRequest;
use App\Models\ProfessionalVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfessionalVerificationController extends Controller
{
    /**
     * Bandeja administrativa para auditar y revisar solicitudes de verificación profesional.
     */
    public function index(Request $request)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'Acceso restringido a la Dirección Clínica y Administración.');
        }

        $filter = $request->query('filtro', 'pendiente');

        $query = ProfessionalVerification::with(['user', 'reviewer'])->latest();

        if (in_array($filter, ['pendiente', 'aprobada', 'rechazada'])) {
            $query->where('status', $filter);
        }

        $verifications = $query->paginate(15)->withQueryString();

        $counts = [
            'todos'      => ProfessionalVerification::count(),
            'pendiente'  => ProfessionalVerification::where('status', 'pendiente')->count(),
            'aprobada'   => ProfessionalVerification::where('status', 'aprobada')->count(),
            'rechazada'  => ProfessionalVerification::where('status', 'rechazada')->count(),
        ];

        return view('admin.verifications.index', compact('verifications', 'counts', 'filter'));
    }

    /**
     * Descarga o visualización segura del documento probatorio adjunto.
     */
    public function document(Request $request, ProfessionalVerification $verification)
    {
        $user = $request->user();

        // Solo administradores o el dueño de la solicitud pueden acceder
        if (!$user || (!$user->is_admin && $user->id !== $verification->user_id)) {
            abort(403, 'No tienes autorización para consultar este documento.');
        }

        if (!$verification->document_path || !Storage::disk('public')->exists($verification->document_path)) {
            abort(404, 'El documento probatorio no fue encontrado en el servidor.');
        }

        return Storage::disk('public')->response($verification->document_path);
    }

    /**
     * Procesa el envío de una nueva solicitud de verificación profesional.
     */
    public function store(StoreProfessionalVerificationRequest $request)
    {
        $user = $request->user();

        // Evitar múltiples solicitudes pendientes
        $existing = $user->latestProfessionalVerification;
        if ($existing && $existing->status === 'pendiente') {
            return redirect()->route('profile.show')
                ->with('info', 'Ya cuentas con una solicitud en proceso de revisión por nuestro equipo clínico.');
        }

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('verifications/' . $user->id, 'public');
        }

        ProfessionalVerification::create([
            'user_id'         => $user->id,
            'full_name'       => trim($request->validated('full_name')),
            'license_number'  => strtoupper(trim($request->validated('license_number'))),
            'education_level' => $request->validated('education_level'),
            'document_path'   => $documentPath,
            'status'          => 'pendiente',
        ]);

        return redirect()->route('profile.show')
            ->with('success', '¡Solicitud enviada con éxito! Tu información y cédula profesional están en proceso de verificación por nuestro equipo.');
    }

    /**
     * Aprueba la solicitud y promueve al usuario a 'profesional'.
     */
    public function approve(Request $request, ProfessionalVerification $verification)
    {
        // Solo administradores o personal autorizado pueden aprobar
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'No tienes autorización para validar cédulas profesionales.');
        }

        DB::transaction(function () use ($verification, $request) {
            $verification->update([
                'status'      => 'aprobada',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $targetUser = $verification->user;
            if ($targetUser) {
                $targetUser->update([
                    'role'               => 'profesional',
                    'license_number'     => $verification->license_number,
                    'professional_title' => $verification->computed_professional_title,
                ]);
            }
        });

        return back()->with('success', "La solicitud de {$verification->full_name} ha sido aprobada. El usuario ahora cuenta con la insignia de Profesional Verificado.");
    }

    /**
     * Rechaza la solicitud con un motivo explicativo.
     */
    public function reject(Request $request, ProfessionalVerification $verification)
    {
        if (!$request->user() || !$request->user()->is_admin) {
            abort(403, 'No tienes autorización para rechazar solicitudes.');
        }

        $request->validate([
            'admin_notes' => ['required', 'string', 'max:500'],
        ]);

        $verification->update([
            'status'      => 'rechazada',
            'admin_notes' => $request->input('admin_notes'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('info', "La solicitud de {$verification->full_name} ha sido rechazada.");
    }
}
