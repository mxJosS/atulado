<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ExpedienteController;
use App\Models\EntregaResumen;

/**
 * Abre un resumen clínico entregado por correo. La firma y la caducidad las
 * comprueba el middleware 'signed'; aquí se cuentan las aperturas.
 */
class ResumenEntregaController extends Controller
{
    public function ver(EntregaResumen $entrega)
    {
        abort_unless($entrega->vigente(), 410, 'Este resumen ya venció.');

        $entrega->forceFill([
            'abierto_en' => $entrega->abierto_en ?? now(),
            'aperturas' => $entrega->aperturas + 1,
        ])->save();

        // El destinatario también puede guardarlo como PDF; la barra no se imprime.
        $barra = view('admin.instituciones.resumen-visor-barra', [
            'entrega' => $entrega,
            'institucion' => $entrega->membresia?->institucion?->nombre_corto ?? 'A Tu Lado',
            'periodo' => null,
        ])->render();

        return response(ExpedienteController::conBarra($entrega->contenido, $barra))->withHeaders([
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
