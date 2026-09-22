<?php

namespace App\Http\Controllers;

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

        return response($entrega->contenido)->withHeaders([
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
