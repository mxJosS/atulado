<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Services\InvitacionService;
use Illuminate\Http\Request;

class InvitacionController extends Controller
{
    public function __construct(private InvitacionService $invitaciones)
    {
    }

    /**
     * alcance = seleccion (personas[]) · todos (quien no ha activado) · nuevos (a quien nunca se le envió)
     */
    public function enviar(Request $request, Institucion $institucion)
    {
        // El botón «Enviar» de una fila manda sólo a esa persona, aunque haya casillas marcadas.
        if ($request->filled('solo')) {
            $request->merge(['alcance' => 'seleccion', 'personas' => [(int) $request->input('solo')]]);
        }

        $datos = $request->validate([
            'alcance' => ['required', 'in:seleccion,todos,nuevos'],
            'personas' => ['required_if:alcance,seleccion', 'array'],
            'personas.*' => ['integer'],
        ], [
            'personas.required_if' => 'Selecciona al menos una persona.',
        ]);

        $consulta = $institucion->membresias()->with(['user:id,name,email,password', 'institucion:id,nombre_corto'])
            ->whereIn('estado', ['invitado', 'suspendido']);

        match ($datos['alcance']) {
            'seleccion' => $consulta->whereIn('id', $datos['personas']),
            'nuevos' => $consulta->whereNull('invitado_en'),
            'todos' => null,
        };

        $enviadas = $this->invitaciones->enviar($consulta->get());

        $volver = redirect()->to(route('admin.instituciones.show', $institucion) . '#tab-invitaciones');

        if ($enviadas === 0) {
            return $volver->with('info', 'No había a quién enviar: todas las personas elegidas ya activaron su cuenta o están dadas de baja.');
        }

        return $volver->with('success', $enviadas === 1
            ? 'Se envió 1 invitación.'
            : "Se están enviando {$enviadas} invitaciones. Llegan en unos minutos.");
    }
}
