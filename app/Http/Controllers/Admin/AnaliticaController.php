<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Services\AnaliticaService;
use Illuminate\Http\Request;

/**
 * «Analítica e índices»: sólo administración. Una institución o todas las
 * vigentes, en 30 días, 90 días o 12 meses.
 */
class AnaliticaController extends Controller
{
    public function index(Request $request, AnaliticaService $analitica)
    {
        $opciones = Institucion::vigentes()->orderBy('nombre_corto')->get(['id', 'slug', 'nombre_corto', 'razon_social']);

        $institucion = $opciones->firstWhere('slug', (string) $request->query('institucion'));
        $corte = $institucion ? collect([$institucion]) : $opciones;

        $datos = $analitica->calcular($corte, (string) $request->query('periodo', '90'));

        return view('admin.analitica.index', $datos + [
            'institucion' => $institucion,
            'opciones' => $opciones,
            'periodos' => AnaliticaService::PERIODOS,
        ]);
    }
}
