<?php

namespace App\Http\Controllers;

use App\Models\Jardin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Guarda el estado del juego del árbol de la persona con sesión. El juego es
 * suyo: sólo puede escribir su propio jardín, y el servidor sólo cuida que el
 * estado tenga forma válida (niveles 1–50, especies y macetas conocidas).
 */
class JardinController extends Controller
{
    public function guardar(Request $request)
    {
        $max = Jardin::NIVEL_MAXIMO;
        $reglas = [
            'plantas' => ['required', 'array'],
            'soles' => ['required', 'integer', 'min:0', 'max:10000000'],
            'tareas_fecha' => ['nullable', 'date_format:Y-m-d'],
            'tareas_hechas' => ['required', 'integer', 'min:0', 'max:50'],
            'inventario' => ['required', 'array'],
            'inventario.escudo' => ['required', 'boolean'],
            'inventario.plantas' => ['required', 'array', 'min:1'],
            'inventario.plantas.*' => [Rule::in(Jardin::PLANTAS)],
            'inventario.planta_activa' => ['required', Rule::in(Jardin::PLANTAS)],
            'inventario.macetas' => ['required', 'array', 'min:1'],
            'inventario.macetas.*' => [Rule::in(Jardin::MACETAS)],
            'inventario.maceta_activa' => ['required', Rule::in(Jardin::MACETAS)],
        ];
        foreach (Jardin::PLANTAS as $planta) {
            $reglas["plantas.{$planta}"] = ['required', 'array'];
            $reglas["plantas.{$planta}.nivel"] = ['required', 'integer', 'min:1', "max:{$max}"];
            $reglas["plantas.{$planta}.xp"] = ['required', 'integer', 'min:0', 'max:1000000'];
        }

        $datos = $request->validate($reglas);

        $estado = [
            'plantas' => collect(Jardin::PLANTAS)->mapWithKeys(fn ($p) => [$p => [
                'nivel' => (int) $datos['plantas'][$p]['nivel'],
                'xp' => (int) $datos['plantas'][$p]['xp'],
            ]])->all(),
            'soles' => (int) $datos['soles'],
            'tareas_fecha' => $datos['tareas_fecha'] ?? null,
            'tareas_hechas' => (int) $datos['tareas_hechas'],
            'inventario' => [
                'escudo' => (bool) $datos['inventario']['escudo'],
                'plantas' => array_values(array_unique($datos['inventario']['plantas'])),
                'planta_activa' => $datos['inventario']['planta_activa'],
                'macetas' => array_values(array_unique($datos['inventario']['macetas'])),
                'maceta_activa' => $datos['inventario']['maceta_activa'],
            ],
        ];

        Jardin::updateOrCreate(['user_id' => $request->user()->id], ['estado' => $estado]);

        return response()->noContent();
    }
}
