<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraAdmin;
use App\Models\Institucion;
use App\Services\EnlaceInstitucionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * «Invitar por link» de una institución: generarlo, cambiarlo por uno nuevo,
 * activarlo o desactivarlo y elegir a qué área entra quien lo usa.
 *
 * Un profesional clínico con el permiso en esa institución (se da en Usuarios)
 * genera el link si aún no existe y lo comparte; lo demás es de la administración.
 */
class EnlaceController extends Controller
{
    public function __construct(private EnlaceInstitucionService $enlaces)
    {
    }

    private function volver(Institucion $institucion)
    {
        return redirect()->to(route('admin.instituciones.show', $institucion) . '#tab-enlace');
    }

    /** Genera el enlace o lo reemplaza (el anterior deja de funcionar). */
    public function generar(Request $request, Institucion $institucion)
    {
        $user = $request->user();
        abort_unless($user->puedeInvitarPorLink($institucion), 403);

        // Cambiarlo por uno nuevo corta el link que ya se compartió: lo decide la administración.
        if ($institucion->enlace_token !== null && !$user->is_admin) {
            return $this->volver($institucion)->with('error', 'Sólo la administración puede cambiar el link por uno nuevo.');
        }

        if ($institucion->estaArchivada()) {
            return $this->volver($institucion)->with('error', 'La institución está archivada: restáurala para invitar por link.');
        }

        $nuevo = $institucion->enlace_token === null;
        $this->enlaces->generar($institucion);
        BitacoraAdmin::registrar($nuevo ? 'enlace_generado' : 'enlace_regenerado', $institucion,
            ($nuevo ? 'Enlace de invitación creado' : 'Enlace de invitación reemplazado; el anterior dejó de funcionar') . " para «{$institucion->nombre_corto}».");

        return $this->volver($institucion)->with('success', $nuevo
            ? 'Link de invitación creado. Ya puedes compartirlo.'
            : 'Link nuevo creado. El anterior ya no funciona.');
    }

    /** Activa o desactiva el enlace y fija el área de entrada. */
    public function actualizar(Request $request, Institucion $institucion)
    {
        abort_if($institucion->enlace_token === null, 404);

        $datos = $request->validate([
            'activo' => ['required', 'boolean'],
            'departamento_id' => ['nullable', Rule::exists('departamentos', 'id')->where('institucion_id', $institucion->id)],
        ], [
            'departamento_id.exists' => 'El área elegida no pertenece a esta institución.',
        ]);

        $institucion->forceFill([
            'enlace_activo' => (bool) $datos['activo'],
            'enlace_departamento_id' => $datos['departamento_id'] ?? null,
        ])->save();

        BitacoraAdmin::registrar('enlace_actualizado', $institucion,
            'Enlace de «' . $institucion->nombre_corto . '» ' . ($institucion->enlace_activo ? 'activo' : 'desactivado')
            . ($institucion->enlace_departamento_id ? ', área ' . $institucion->enlaceDepartamento?->nombre : ', sin área') . '.');

        return $this->volver($institucion)->with('success', $institucion->enlace_activo
            ? 'Link de invitación actualizado.'
            : 'Link desactivado: quien lo abra verá que ya no funciona.');
    }
}
